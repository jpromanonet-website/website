<?php
declare(strict_types=1);

/**
 * Goodreads "read" shelf — automatic from PHP (not CMS-managed).
 *
 * Uses the public shelf RSS feed (no API key):
 * https://www.goodreads.com/review/list_rss/{userId}?shelf=read&per_page=200&page=N
 *
 * @return list<array{
 *   title:string,author:string,url:string,cover:string,pages:int,
 *   rating:int,readAt:string,timestamp:int,bookId:string
 * }>
 */
function fetch_goodreads_read_books(int $cacheTtl = 3600): array
{
    $cacheCandidates = [
        APP_ROOT . '/cache/goodreads-read.json',
        rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/jpromanonet-goodreads-read.json',
    ];

    foreach ($cacheCandidates as $cacheFile) {
        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($cached) && isset($cached['books']) && is_array($cached['books'])) {
                return $cached['books'];
            }
            if (is_array($cached) && $cached !== [] && isset($cached[0]) && is_array($cached[0])) {
                return $cached;
            }
        }
    }

    $userId = goodreads_user_id();
    $books = goodreads_fetch_all_read($userId);
    if ($books !== []) {
        goodreads_write_cache($cacheCandidates, [
            'fetchedAt' => time(),
            'userId' => $userId,
            'books' => $books,
        ]);
        return $books;
    }

    return goodreads_stale_cache($cacheCandidates);
}

/**
 * @return array{books:int,pages:int}
 */
function goodreads_read_stats(): array
{
    $books = fetch_goodreads_read_books();
    $pages = 0;
    foreach ($books as $book) {
        $pages += max(0, (int) ($book['pages'] ?? 0));
    }
    return [
        'books' => count($books),
        'pages' => $pages,
    ];
}

function goodreads_user_id(): string
{
    return '52808164';
}

function goodreads_profile_url(): string
{
    return 'https://www.goodreads.com/user/show/52808164-juan-romano';
}

/**
 * @return list<array<string,mixed>>
 */
function goodreads_fetch_all_read(string $userId): array
{
    $byId = [];
    $maxPages = 40; // up to ~8k books

    for ($page = 1; $page <= $maxPages; $page++) {
        $url = sprintf(
            'https://www.goodreads.com/review/list_rss/%s?shelf=read&per_page=200&page=%d',
            rawurlencode($userId),
            $page
        );
        $xml = goodreads_http_get($url);
        if ($xml === null || $xml === '') {
            break;
        }

        $batch = goodreads_parse_rss($xml);
        if ($batch === []) {
            break;
        }

        $newCount = 0;
        foreach ($batch as $book) {
            $key = (string) ($book['bookId'] !== '' ? $book['bookId'] : $book['url']);
            if ($key === '' || isset($byId[$key])) {
                continue;
            }
            $byId[$key] = $book;
            $newCount++;
        }

        if (count($batch) < 200 || $newCount === 0) {
            break;
        }

        // Be gentle with Goodreads
        usleep(250000);
    }

    $books = array_values($byId);
    usort($books, static function (array $a, array $b): int {
        return ((int) ($b['timestamp'] ?? 0)) <=> ((int) ($a['timestamp'] ?? 0));
    });

    return $books;
}

/**
 * @return list<array<string,mixed>>
 */
function goodreads_parse_rss(string $xml): array
{
    $previous = libxml_use_internal_errors(true);
    $feed = simplexml_load_string($xml);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if ($feed === false || !isset($feed->channel->item)) {
        return [];
    }

    $out = [];
    foreach ($feed->channel->item as $item) {
        $title = trim(html_entity_decode((string) ($item->title ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($title === '') {
            continue;
        }

        $bookId = trim((string) ($item->book_id ?? ''));
        $author = trim(html_entity_decode((string) ($item->author_name ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $cover = trim((string) (
            $item->book_large_image_url
            ?? $item->book_medium_image_url
            ?? $item->book_image_url
            ?? ''
        ));
        $cover = goodreads_upgrade_cover($cover);

        $pages = 0;
        if (isset($item->book->num_pages)) {
            $pages = (int) preg_replace('/[^\d]/', '', (string) $item->book->num_pages);
        } elseif (isset($item->num_pages)) {
            $pages = (int) preg_replace('/[^\d]/', '', (string) $item->num_pages);
        }

        $link = trim((string) ($item->link ?? ''));
        if ($bookId !== '') {
            $link = 'https://www.goodreads.com/book/show/' . rawurlencode($bookId);
        } elseif ($link !== '') {
            $link = preg_replace('#\?.*$#', '', $link) ?? $link;
        }

        $readAt = trim((string) ($item->user_read_at ?? ''));
        $added = trim((string) ($item->user_date_added ?? $item->pubDate ?? ''));
        $tsSource = $readAt !== '' ? $readAt : $added;
        $timestamp = $tsSource !== '' ? (int) strtotime($tsSource) : 0;

        $out[] = [
            'title' => $title,
            'author' => $author,
            'url' => $link,
            'cover' => $cover,
            'pages' => $pages,
            'rating' => (int) ($item->user_rating ?? 0),
            'readAt' => $readAt,
            'timestamp' => $timestamp,
            'bookId' => $bookId,
        ];
    }

    return $out;
}

function goodreads_upgrade_cover(string $url): string
{
    $url = html_entity_decode(trim($url), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if ($url === '') {
        return '';
    }
    // Prefer a readable cover size over the tiny RSS default
    $url = preg_replace('/\._S[XY]\d+_/', '._SX300_', $url) ?? $url;
    return $url;
}

/**
 * @param list<string> $cacheCandidates
 * @return list<array<string,mixed>>
 */
function goodreads_stale_cache(array $cacheCandidates): array
{
    foreach ($cacheCandidates as $cacheFile) {
        if (!is_file($cacheFile)) {
            continue;
        }
        $cached = json_decode((string) file_get_contents($cacheFile), true);
        if (is_array($cached) && isset($cached['books']) && is_array($cached['books'])) {
            return $cached['books'];
        }
        if (is_array($cached) && $cached !== [] && isset($cached[0]) && is_array($cached[0])) {
            return $cached;
        }
    }
    return [];
}

/**
 * @param list<string> $cacheCandidates
 * @param array<string,mixed> $payload
 */
function goodreads_write_cache(array $cacheCandidates, array $payload): void
{
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
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

function goodreads_http_get(string $url): ?string
{
    $headers = [
        'User-Agent: jpromanonet-site/1.0 (+https://jpromano.net)',
        'Accept: application/rss+xml, application/xml, text/xml, text/html, */*',
    ];

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
