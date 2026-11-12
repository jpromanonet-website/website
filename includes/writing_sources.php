<?php
declare(strict_types=1);

/**
 * Auto writing sources (WordPress) — HomeSpot + Soup IT.
 * Same idea as Medium: live fetch + cache, outside the CMS.
 */

/**
 * @return list<array{title:string,url:string,category:string,pubDate:string,timestamp:int,source:string,imageSrc:string}>
 */
function fetch_auto_writing_posts(int $cacheTtl = 3600): array
{
    return writing_merge_source_posts(
        writing_fetch_wp_source([
            'key' => 'homespot',
            'label' => 'HomeSpot',
            'api' => 'https://homespot.com.ar/wp-json/wp/v2/posts',
            'fallbackImage' => 'homespot_logo.png',
        ], $cacheTtl),
        writing_fetch_wp_source([
            'key' => 'soupit',
            'label' => 'Soup IT',
            'api' => 'https://soupit.ar/blog/wp-json/wp/v2/posts',
            'fallbackImage' => 'soupit.png',
        ], $cacheTtl)
    );
}

/**
 * Publishers that are loaded automatically and must not stay as CMS cards.
 *
 * @return list<string>
 */
function writing_auto_publisher_labels(): array
{
    return ['HomeSpot', 'Soup IT', 'SoupIT', 'Homespot'];
}

function writing_is_auto_publisher_article(string $category, string $url): bool
{
    $cat = strtolower(trim($category));
    foreach (writing_auto_publisher_labels() as $label) {
        if ($cat === strtolower($label)) {
            return true;
        }
    }
    if ($url !== '' && preg_match('#https?://(?:www\.)?homespot\.com\.ar#i', $url)) {
        return true;
    }
    if ($url !== '' && preg_match('#https?://(?:www\.)?soupit\.ar(?:/blog)?#i', $url)) {
        return true;
    }
    return false;
}

/**
 * @param list<array<string,mixed>> ...$groups
 * @return list<array{title:string,url:string,category:string,pubDate:string,timestamp:int,source:string,imageSrc:string}>
 */
function writing_merge_source_posts(array ...$groups): array
{
    $byUrl = [];
    foreach ($groups as $group) {
        foreach ($group as $post) {
            if (!is_array($post)) {
                continue;
            }
            $url = writing_canonical_article_url((string) ($post['url'] ?? ''));
            if ($url === '') {
                continue;
            }
            $post['url'] = $url;
            if (!isset($byUrl[$url]) || (int) ($post['timestamp'] ?? 0) >= (int) ($byUrl[$url]['timestamp'] ?? 0)) {
                $byUrl[$url] = $post;
            }
        }
    }
    $list = array_values($byUrl);
    usort($list, static fn(array $a, array $b): int => ((int) ($b['timestamp'] ?? 0)) <=> ((int) ($a['timestamp'] ?? 0)));
    return $list;
}

/**
 * @param array{key:string,label:string,api:string,fallbackImage:string} $source
 * @return list<array{title:string,url:string,category:string,pubDate:string,timestamp:int,source:string,imageSrc:string}>
 */
function writing_fetch_wp_source(array $source, int $cacheTtl = 3600): array
{
    $key = preg_replace('/[^a-z0-9\-]/i', '', (string) ($source['key'] ?? 'wp')) ?: 'wp';
    $cacheCandidates = [
        APP_ROOT . '/cache/writing-' . $key . '.json',
        rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/jpromanonet-writing-' . $key . '.json',
    ];

    $cached = writing_read_json_cache($cacheCandidates, $cacheTtl);
    if ($cached !== []) {
        return $cached;
    }

    $previous = writing_read_json_cache($cacheCandidates, null);
    $fresh = writing_pull_wp_posts($source);
    if ($fresh === []) {
        return $previous;
    }

    writing_write_json_cache($cacheCandidates, $fresh);
    return $fresh;
}

/**
 * @param array{key:string,label:string,api:string,fallbackImage:string} $source
 * @return list<array{title:string,url:string,category:string,pubDate:string,timestamp:int,source:string,imageSrc:string}>
 */
function writing_pull_wp_posts(array $source): array
{
    $api = rtrim((string) ($source['api'] ?? ''), '?&');
    if ($api === '') {
        return [];
    }

    $label = (string) ($source['label'] ?? 'Blog');
    $fallback = (string) ($source['fallbackImage'] ?? '');
    $sourceKey = (string) ($source['key'] ?? 'wp');
    $posts = [];

    for ($page = 1; $page <= 20; $page++) {
        // Do not use _fields here: it strips _embedded featured media.
        $url = $api . '?per_page=100&page=' . $page . '&_embed=1';
        $raw = writing_http_get($url, [
            'Accept: application/json',
            'User-Agent: jpromanonet-site/1.0 (+https://jpromano.net)',
        ], 25);
        if ($raw === null || $raw === '') {
            break;
        }

        $batch = json_decode($raw, true);
        if (!is_array($batch) || $batch === []) {
            break;
        }

        foreach ($batch as $item) {
            if (!is_array($item)) {
                continue;
            }
            $title = trim(html_entity_decode(strip_tags((string) (($item['title']['rendered'] ?? '') ?: '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $link = writing_canonical_article_url((string) ($item['link'] ?? ''));
            if ($title === '' || $link === '') {
                continue;
            }
            $ts = strtotime((string) ($item['date'] ?? '')) ?: 0;
            $image = writing_wp_featured_image($item);
            if ($image === '') {
                $image = $fallback;
            }
            $posts[] = [
                'title' => $title,
                'url' => $link,
                'category' => $label,
                'pubDate' => $ts > 0 ? gmdate('D, d M Y H:i:s', $ts) . ' GMT' : '',
                'timestamp' => $ts,
                'source' => $sourceKey,
                'imageSrc' => $image,
            ];
        }

        if (count($batch) < 100) {
            break;
        }
    }

    usort($posts, static fn(array $a, array $b): int => $b['timestamp'] <=> $a['timestamp']);
    return $posts;
}

/**
 * @param array<string,mixed> $item
 */
function writing_wp_featured_image(array $item): string
{
    $embedded = $item['_embedded']['wp:featuredmedia'][0] ?? null;
    if (is_array($embedded)) {
        $src = trim((string) ($embedded['source_url'] ?? ''));
        if ($src !== '') {
            return $src;
        }
        $sizes = $embedded['media_details']['sizes'] ?? null;
        if (is_array($sizes)) {
            foreach (['large', 'medium_large', 'medium', 'full'] as $size) {
                $candidate = trim((string) ($sizes[$size]['source_url'] ?? ''));
                if ($candidate !== '') {
                    return $candidate;
                }
            }
        }
    }
    return '';
}

function writing_canonical_article_url(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    if (function_exists('medium_canonical_url')) {
        return medium_canonical_url($url);
    }
    $parts = parse_url($url);
    if ($parts === false || empty($parts['host'])) {
        return $url;
    }
    $path = $parts['path'] ?? '/';
    return ($parts['scheme'] ?? 'https') . '://' . strtolower((string) $parts['host']) . $path;
}

/**
 * @param list<string> $cacheCandidates
 * @return list<array<string,mixed>>
 */
function writing_read_json_cache(array $cacheCandidates, ?int $maxAge): array
{
    foreach ($cacheCandidates as $cacheFile) {
        if (!is_file($cacheFile)) {
            continue;
        }
        if ($maxAge !== null && (time() - filemtime($cacheFile)) >= $maxAge) {
            continue;
        }
        $cached = json_decode((string) file_get_contents($cacheFile), true);
        if (is_array($cached) && $cached !== []) {
            return $cached;
        }
    }
    return [];
}

/**
 * @param list<string> $cacheCandidates
 * @param list<array<string,mixed>> $posts
 */
function writing_write_json_cache(array $cacheCandidates, array $posts): void
{
    $json = json_encode($posts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) {
        return;
    }
    foreach ($cacheCandidates as $cacheFile) {
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (@file_put_contents($cacheFile, $json) !== false) {
            return;
        }
    }
}

function writing_http_get(string $url, array $headers = [], int $timeout = 25): ?string
{
    if (function_exists('medium_http_get')) {
        return medium_http_get($url, $headers, $timeout);
    }

    if (!function_exists('curl_init')) {
        return null;
    }
    $ch = curl_init($url);
    if ($ch === false) {
        return null;
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => max(5, $timeout),
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_HTTPHEADER => $headers,
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false || $code >= 400) {
        return null;
    }
    return (string) $body;
}

/**
 * One-time cleanup: remove CMS writing cards that now come from auto feeds.
 */
function writing_purge_cms_auto_publishers_once(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    if (!class_exists(\MicroCMS\Content::class)) {
        return;
    }

    $flagCandidates = [
        APP_ROOT . '/cache/writing-auto-purged.flag',
        rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/jpromanonet-writing-auto-purged.flag',
    ];
    foreach ($flagCandidates as $flag) {
        if (is_file($flag)) {
            return;
        }
    }

    try {
        $deleted = \MicroCMS\Content::purgeWritingAutoPublisherCards();
    } catch (Throwable $e) {
        return;
    }

    $payload = json_encode([
        'purgedAt' => time(),
        'deleted' => $deleted,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($payload === false) {
        return;
    }
    foreach ($flagCandidates as $flag) {
        $dir = dirname($flag);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (@file_put_contents($flag, $payload) !== false) {
            return;
        }
    }
}
