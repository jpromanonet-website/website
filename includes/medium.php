<?php
declare(strict_types=1);

/**
 * Medium posts — fully automatic from PHP.
 *
 * Flow:
 * 1) Try Medium profile stream API (full catalog), via Jina proxy if Cloudflare blocks direct access
 * 2) Merge with RSS (fast source for newest posts)
 * 3) Fall back to local archive JSON if live sources fail
 * 4) Cache merged result (~1 hour)
 *
 * @return list<array{title:string,url:string,category:string,pubDate:string,timestamp:int,source:string,imageSrc:string}>
 */
function fetch_medium_posts(int $limit = 500, int $cacheTtl = 3600): array
{
    $cacheCandidates = [
        APP_ROOT . '/cache/medium-live.json',
        rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/jpromanonet-medium-live.json',
    ];

    foreach ($cacheCandidates as $cacheFile) {
        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($cached) && $cached !== []) {
                return array_slice($cached, 0, $limit);
            }
        }
    }

    $stream = medium_fetch_stream_posts();
    $rss = medium_parse_feed(medium_feed_url());
    $archive = medium_load_archive();
    $merged = medium_merge_posts($stream, $rss, $archive);

    if ($merged !== []) {
        medium_write_cache($cacheCandidates, $merged);
    }

    return array_slice($merged, 0, $limit);
}

function latest_medium_post(): ?array
{
    $posts = fetch_medium_posts(1);
    return $posts[0] ?? null;
}

function medium_feed_url(): string
{
    global $site;
    return (string) ($site['medium_feed'] ?? 'https://medium.com/feed/@jpromanonet');
}

function medium_user_id(): string
{
    global $site;
    return (string) ($site['medium_user_id'] ?? '768cb0ffbcaf');
}

function medium_username(): string
{
    global $site;
    $blog = (string) ($site['blog'] ?? 'https://jpromanonet.medium.com');
    if (preg_match('#https?://([a-z0-9\-]+)\.medium\.com#i', $blog, $m)) {
        return $m[1];
    }
    return 'jpromanonet';
}

/**
 * @return list<array{title:string,url:string,category:string,pubDate:string,timestamp:int,source:string,imageSrc:string}>
 */
function medium_load_archive(): array
{
    $file = APP_ROOT . '/assets/data/medium-archive.json';
    if (!is_file($file)) {
        return [];
    }
    $raw = file_get_contents($file);
    if ($raw === false || $raw === '') {
        return [];
    }
    if (str_starts_with($raw, "\xEF\xBB\xBF")) {
        $raw = substr($raw, 3);
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return [];
    }

    $posts = [];
    foreach ($data as $item) {
        if (!is_array($item)) {
            continue;
        }
        $normalized = medium_normalize_post($item);
        if ($normalized !== null) {
            $posts[] = $normalized;
        }
    }
    return $posts;
}

/**
 * @param list<array<string,mixed>> ...$groups
 * @return list<array{title:string,url:string,category:string,pubDate:string,timestamp:int,source:string,imageSrc:string}>
 */
function medium_merge_posts(array ...$groups): array
{
    $byUrl = [];
    foreach ($groups as $group) {
        foreach ($group as $post) {
            if (!is_array($post)) {
                continue;
            }
            $normalized = medium_normalize_post($post);
            if ($normalized === null) {
                continue;
            }
            $url = $normalized['url'];
            if (!isset($byUrl[$url]) || $normalized['timestamp'] >= $byUrl[$url]['timestamp']) {
                $byUrl[$url] = $normalized;
            }
        }
    }

    $posts = array_values($byUrl);
    usort($posts, static fn(array $a, array $b): int => $b['timestamp'] <=> $a['timestamp']);
    return $posts;
}

/**
 * @param array<string,mixed> $item
 * @return array{title:string,url:string,category:string,pubDate:string,timestamp:int,source:string,imageSrc:string}|null
 */
function medium_normalize_post(array $item): ?array
{
    $title = trim((string) ($item['title'] ?? ''));
    $url = trim((string) ($item['url'] ?? $item['link'] ?? ''));
    $url = preg_replace('/\?source=.*$/', '', $url) ?? $url;
    $url = medium_canonical_url($url);
    if ($title === '' || $url === '') {
        return null;
    }

    $timestamp = (int) ($item['timestamp'] ?? 0);
    if ($timestamp <= 0 && !empty($item['pubDate'])) {
        $timestamp = strtotime((string) $item['pubDate']) ?: 0;
    }

    return [
        'title' => html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'url' => $url,
        'category' => 'Medium',
        'pubDate' => (string) ($item['pubDate'] ?? ''),
        'timestamp' => $timestamp,
        'source' => 'medium',
        'imageSrc' => 'medium.svg',
    ];
}

function medium_canonical_url(string $url): string
{
    $url = trim($url);
    $parts = parse_url($url);
    if ($parts === false || empty($parts['host'])) {
        return $url;
    }

    $path = rawurldecode($parts['path'] ?? '/');
    $scheme = $parts['scheme'] ?? 'https';
    $host = strtolower($parts['host']);

    return $scheme . '://' . $host . $path;
}

/**
 * @return list<array{title:string,url:string,category:string,pubDate:string,timestamp:int,source:string,imageSrc:string}>
 */
function medium_parse_feed(string $feedUrl): array
{
    $xml = medium_http_get($feedUrl);
    if ($xml === null || $xml === '') {
        return [];
    }

    $previous = libxml_use_internal_errors(true);
    $feed = simplexml_load_string($xml);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if ($feed === false || !isset($feed->channel->item)) {
        return [];
    }

    $posts = [];
    foreach ($feed->channel->item as $item) {
        $normalized = medium_normalize_post([
            'title' => (string) $item->title,
            'url' => (string) $item->link,
            'pubDate' => (string) $item->pubDate,
            'timestamp' => strtotime((string) $item->pubDate) ?: 0,
        ]);
        if ($normalized !== null) {
            $posts[] = $normalized;
        }
    }
    return $posts;
}

/**
 * Full Medium catalog via profile stream API.
 * Tries direct request first, then Jina.ai proxy (bypasses Cloudflare on many hosts).
 *
 * @return list<array{title:string,url:string,category:string,pubDate:string,timestamp:int,source:string,imageSrc:string}>
 */
function medium_fetch_stream_posts(): array
{
    $userId = medium_user_id();
    $username = medium_username();
    $to = null;
    $byUrl = [];

    for ($page = 0; $page < 20; $page++) {
        $apiUrl = 'https://medium.com/_/api/users/' . rawurlencode($userId)
            . '/profile/stream?limit=100&source=latest';
        if ($to !== null) {
            $apiUrl .= '&to=' . rawurlencode((string) $to);
        }

        $payload = medium_fetch_stream_payload($apiUrl);
        if ($payload === null) {
            break;
        }

        $posts = $payload['references']['Post'] ?? [];
        if (!is_array($posts) || $posts === []) {
            break;
        }

        foreach ($posts as $post) {
            if (!is_array($post)) {
                continue;
            }
            $title = trim((string) ($post['title'] ?? ''));
            $slug = trim((string) ($post['uniqueSlug'] ?? ''));
            if ($title === '' || $slug === '') {
                continue;
            }
            $ts = (int) ($post['firstPublishedAt'] ?? $post['latestPublishedAt'] ?? 0);
            if ($ts > 10_000_000_000) {
                $ts = intdiv($ts, 1000);
            }
            $normalized = medium_normalize_post([
                'title' => $title,
                'url' => 'https://' . $username . '.medium.com/' . $slug,
                'timestamp' => $ts,
                'pubDate' => $ts > 0 ? gmdate('D, d M Y H:i:s', $ts) . ' GMT' : '',
            ]);
            if ($normalized !== null) {
                $byUrl[$normalized['url']] = $normalized;
            }
        }

        $nextTo = $payload['paging']['next']['to'] ?? null;
        if ($nextTo === null || $nextTo === $to) {
            break;
        }
        $to = $nextTo;
    }

    $list = array_values($byUrl);
    usort($list, static fn(array $a, array $b): int => $b['timestamp'] <=> $a['timestamp']);
    return $list;
}

/**
 * @return array<string,mixed>|null
 */
function medium_fetch_stream_payload(string $apiUrl): ?array
{
    // 1) Direct
    $raw = medium_http_get($apiUrl, [
        'Accept: application/json',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        'Referer: https://medium.com/',
    ]);
    $data = medium_decode_stream_response($raw);
    if ($data !== null) {
        return $data['payload'] ?? null;
    }

    // 2) Via Jina reader proxy (works when Cloudflare blocks the server IP)
    $proxied = medium_http_get('https://r.jina.ai/' . $apiUrl, [
        'Accept: text/plain',
        'X-Return-Format: text',
        'User-Agent: Mozilla/5.0',
    ]);
    $data = medium_decode_stream_response($proxied);
    if ($data !== null) {
        return $data['payload'] ?? null;
    }

    return null;
}

/**
 * @return array<string,mixed>|null
 */
function medium_decode_stream_response(?string $raw): ?array
{
    if ($raw === null || $raw === '') {
        return null;
    }

    $json = $raw;
    $marker = '])}while(1);</x>';
    $pos = strpos($raw, $marker);
    if ($pos !== false) {
        $json = substr($raw, $pos + strlen($marker));
    } else {
        $pos = strpos($raw, '{"success"');
        if ($pos === false) {
            return null;
        }
        $json = substr($raw, $pos);
    }

    $data = json_decode($json, true);
    if (!is_array($data) || empty($data['success'])) {
        return null;
    }
    return $data;
}

/**
 * @param list<string> $cacheCandidates
 * @param list<array<string,mixed>> $posts
 */
function medium_write_cache(array $cacheCandidates, array $posts): void
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

function medium_http_get(string $url, array $headers = []): ?string
{
    $defaultHeaders = [
        'User-Agent: jpromanonet-site/1.0 (+https://jpromano.net)',
        'Accept: application/rss+xml, application/xml, text/xml, application/json, */*',
    ];
    $headers = array_values(array_unique(array_merge($defaultHeaders, $headers)));

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch === false) {
            return null;
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 45,
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

    $ctx = stream_context_create([
        'http' => [
            'timeout' => 45,
            'header' => implode("\r\n", $headers) . "\r\n",
        ],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    return $body === false ? null : $body;
}
