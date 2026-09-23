<?php
declare(strict_types=1);

/**
 * GitHub commit total — live from the GitHub API (public + private).
 * Token comes from the site .env or RepoScope. Not the CMS.
 *
 * @return array{count:int,fetchedAt:int}
 */
function github_commit_stats(int $cacheTtl = 3600): array
{
    $cacheCandidates = [
        rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/jpromanonet-github-commits.json',
        APP_ROOT . '/cache/github-commits.json',
    ];

    foreach ($cacheCandidates as $cacheFile) {
        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($cached) && isset($cached['count']) && (int) $cached['count'] > 0) {
                return [
                    'count' => (int) $cached['count'],
                    'fetchedAt' => (int) ($cached['fetchedAt'] ?? filemtime($cacheFile)),
                ];
            }
        }
    }

    $count = github_fetch_commit_total();
    if ($count > 0) {
        $payload = [
            'count' => $count,
            'fetchedAt' => time(),
        ];
        github_write_cache($cacheCandidates, $payload);
        return $payload;
    }

    foreach ($cacheCandidates as $cacheFile) {
        if (!is_file($cacheFile)) {
            continue;
        }
        $cached = json_decode((string) file_get_contents($cacheFile), true);
        if (is_array($cached) && isset($cached['count']) && (int) $cached['count'] > 0) {
            return [
                'count' => (int) $cached['count'],
                'fetchedAt' => (int) ($cached['fetchedAt'] ?? filemtime($cacheFile)),
            ];
        }
    }

    return ['count' => 0, 'fetchedAt' => 0];
}

function github_commit_count(): int
{
    return github_commit_stats()['count'];
}

function github_profile_url(): string
{
    return 'https://github.com/jpromanonet';
}

function github_fetch_commit_total(): int
{
    $token = github_token();
    if ($token === '') {
        return 0;
    }

    $total = 0;
    $startYear = 2018;
    $endYear = (int) gmdate('Y');

    for ($year = $startYear; $year <= $endYear; $year++) {
        $res = github_graphql($token, [
            'query' => 'query($login:String!,$from:DateTime!,$to:DateTime!){ user(login:$login){ contributionsCollection(from:$from, to:$to){ totalCommitContributions } } }',
            'variables' => [
                'login' => 'jpromanonet',
                'from' => sprintf('%d-01-01T00:00:00Z', $year),
                'to' => sprintf('%d-12-31T23:59:59Z', $year),
            ],
        ]);
        $total += (int) ($res['data']['user']['contributionsCollection']['totalCommitContributions'] ?? 0);
    }

    return $total;
}

function github_token(): string
{
    $siteEnv = github_read_env_file(__DIR__ . '/.env');
    $fromSite = trim((string) ($siteEnv['GITHUB_TOKEN'] ?? ''));
    if ($fromSite !== '') {
        return $fromSite;
    }

    $reposcope = github_read_env_file(dirname(APP_ROOT) . '/reposcope/.env');
    if ($reposcope === []) {
        $reposcope = github_read_env_file(dirname(APP_ROOT) . '/RepoScope/.env');
    }

    $fromRsEnv = trim((string) ($reposcope['GITHUB_TOKEN'] ?? ''));
    if ($fromRsEnv !== '') {
        return $fromRsEnv;
    }

    $host = (string) ($reposcope['DB_HOST'] ?? '');
    $name = (string) ($reposcope['DB_NAME'] ?? '');
    $user = (string) ($reposcope['DB_USER'] ?? '');
    if ($host === '' || $name === '' || $user === '') {
        return '';
    }

    $port = (string) ($reposcope['DB_PORT'] ?? '3306');
    $pass = (string) ($reposcope['DB_PASS'] ?? '');

    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        $value = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'github_token' LIMIT 1");
        return trim((string) ($value ? $value->fetchColumn() : ''));
    } catch (Throwable $e) {
        return '';
    }
}

/**
 * @return array<string, string>
 */
function github_read_env_file(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return [];
    }

    $out = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }
        $out[$key] = $value;
    }
    return $out;
}

/**
 * @param array<string, mixed> $payload
 * @return array<string, mixed>
 */
function github_graphql(string $token, array $payload): array
{
    $raw = github_http('https://api.github.com/graphql', [
        'Authorization: Bearer ' . $token,
        'Accept: application/vnd.github+json',
        'Content-Type: application/json',
    ], $payload);

    $decoded = json_decode((string) $raw, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * @param list<string> $headers
 * @param array<string, mixed>|null $payload
 */
function github_http(string $url, array $headers, ?array $payload = null): ?string
{
    $headers[] = 'User-Agent: jpromanonet-site/1.0 (+https://jpromano.net)';
    $headers = array_values(array_unique($headers));

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch === false) {
            return null;
        }
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_HTTPHEADER => $headers,
        ];
        if ($payload !== null) {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($payload);
        }
        curl_setopt_array($ch, $opts);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $code >= 400) {
            return null;
        }
        return (string) $body;
    }

    return null;
}

/**
 * @param list<string> $cacheCandidates
 * @param array<string, mixed> $payload
 */
function github_write_cache(array $cacheCandidates, array $payload): void
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
        @file_put_contents($cacheFile, $json);
    }
}
