<?php
declare(strict_types=1);

function url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    if ($path === '/') {
        return BASE_URL === '' ? '/' : BASE_URL . '/';
    }
    return BASE_URL . $path;
}

/** @param array<string, mixed> $stat */
function stat_href(array $stat): string
{
    $raw = (string) ($stat['url'] ?? $stat['path'] ?? '/');
    if (preg_match('#^https?://#i', $raw) === 1) {
        return $raw;
    }
    return url($raw);
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function load_catalog(string $name): array
{
    if (!class_exists(\MicroCMS\Content::class)) {
        return [];
    }

    try {
        return \MicroCMS\Content::cardsAsLegacyJson($name);
    } catch (Throwable $e) {
        return [];
    }
}

function writing_article_image_url(string $image): string
{
    $image = trim($image);
    if ($image === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $image) === 1) {
        return $image;
    }
    return media_url('writing', $image);
}

function media_url(string $section, string $filename): string
{
    $filename = basename(str_replace('\\', '/', $filename));
    $section = trim($section, '/');

    $disk = APP_ROOT . '/assets/media/' . $section . '/' . $filename;
    if (is_file($disk)) {
        return asset('media/' . $section . '/' . $filename);
    }

    if (class_exists(\MicroCMS\MediaStore::class) && \MicroCMS\MediaStore::exists($section, $filename)) {
        return url('microCMS/public/file.php?section=' . rawurlencode($section) . '&file=' . rawurlencode($filename));
    }

    // Fall back to classic asset path (may 404 if missing)
    return asset('media/' . $section . '/' . $filename);
}

function pdf_url(string $filename): string
{
    $filename = basename($filename);
    $disk = APP_ROOT . '/assets/pdfs/' . $filename;
    if (is_file($disk)) {
        return asset('pdfs/' . $filename);
    }
    if (class_exists(\MicroCMS\MediaStore::class) && \MicroCMS\MediaStore::exists('pdfs', $filename)) {
        return url('microCMS/public/file.php?section=pdfs&file=' . rawurlencode($filename));
    }
    return asset('pdfs/' . $filename);
}

function pdf_exists(string $filename): bool
{
    $filename = basename($filename);
    if (is_file(APP_ROOT . '/assets/pdfs/' . $filename)) {
        return true;
    }
    if (class_exists(\MicroCMS\MediaStore::class)) {
        return \MicroCMS\MediaStore::exists('pdfs', $filename);
    }
    return false;
}

function unique_categories(array $items, string $key = 'category'): array
{
    $cats = [];
    foreach ($items as $item) {
        if (!empty($item[$key])) {
            $cats[] = (string) $item[$key];
        }
    }
    $cats = array_values(array_unique($cats));
    sort($cats, SORT_NATURAL | SORT_FLAG_CASE);
    return $cats;
}

function is_external(string $url): bool
{
    return (bool) preg_match('#^https?://#i', $url);
}

function render_page_header(string $title, string $subtitle = '', string $eyebrow = ''): void
{
    ?>
    <header class="page-banner">
        <div class="page-banner__inner">
            <?php if ($eyebrow !== ''): ?>
                <p class="page-banner__eyebrow"><?= e($eyebrow) ?></p>
            <?php endif; ?>
            <h1 class="page-banner__title"><?= e($title) ?></h1>
            <?php if ($subtitle !== ''): ?>
                <p class="page-banner__subtitle"><?= e($subtitle) ?></p>
            <?php endif; ?>
        </div>
    </header>
    <?php
}

function render_sidebar_nav(string $active = ''): void
{
    /** @var array $navItems */
    global $navItems;
    $items = is_array($navItems ?? null) ? $navItems : [];
    ?>
    <ul class="sidebar-nav">
        <?php foreach ($items as $item): ?>
            <?php if (!empty($item['children'])): ?>
                <li class="sidebar-nav__group sidebar-nav__group--<?= e((string) ($item['key'] ?? 'section')) ?>">
                    <span class="sidebar-nav__group-label">
                        <span class="sidebar-nav__group-pip" aria-hidden="true"></span>
                        <?= e((string) ($item['label'] ?? '')) ?>
                    </span>
                    <ul class="sidebar-nav__children">
                        <?php foreach ($item['children'] as $child):
                            if (!empty($child['heading'])) {
                                $headingTone = preg_replace('/[^a-z0-9\-]/i', '', (string) ($child['tone'] ?? 'default')) ?: 'default';
                                echo '<li class="sidebar-nav__heading sidebar-nav__heading--' . e($headingTone) . '" role="presentation">'
                                    . e((string) ($child['label'] ?? ''))
                                    . '</li>';
                                continue;
                            }
                            if (!empty($child['divider'])) {
                                echo '<li class="sidebar-nav__divider" role="separator" aria-hidden="true"></li>';
                                continue;
                            }
                            $childUrl = (string) ($child['url'] ?? '');
                            $childPath = (string) ($child['path'] ?? '');
                            if ($childUrl === '' && $childPath === '') {
                                continue;
                            }
                            $href = $childUrl !== '' ? $childUrl : url($childPath);
                            $key = (string) ($child['key'] ?? '');
                            $tone = preg_replace('/[^a-z0-9\-]/i', '', (string) ($child['tone'] ?? '')) ?: '';
                            $childClass = 'sidebar-nav__child'
                                . (($active !== '' && $key === $active) ? ' is-active' : '')
                                . ($childUrl !== '' ? ' sidebar-nav__child--external' : '')
                                . ($tone !== '' ? ' sidebar-nav__child--card sidebar-nav__child--' . $tone : '');
                        ?>
                            <li>
                                <a
                                    class="<?= e($childClass) ?>"
                                    href="<?= e($href) ?>"
                                    <?= $childUrl !== '' ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
                                ><?= e((string) ($child['label'] ?? '')) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php continue; ?>
            <?php endif; ?>
            <li>
                <a
                    class="<?= ($active !== '' && ($item['key'] ?? '') === $active) ? 'is-active' : '' ?>"
                    href="<?= e(url((string) ($item['path'] ?? '/'))) ?>"
                ><?= e((string) ($item['label'] ?? '')) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
}

/**
 * HTML attributes for desktop image lightbox triggers.
 */
function lightbox_data_attrs(
    string $src,
    string $title,
    string $live = '',
    string $code = '',
    string $liveLabel = '',
    string $codeLabel = ''
): string {
    if ($src === '') {
        return '';
    }
    $parts = [
        'data-lightbox',
        'data-lightbox-src="' . e($src) . '"',
        'data-lightbox-title="' . e($title) . '"',
        'data-lightbox-live="' . e(($live !== '' && $live !== '#') ? $live : '') . '"',
        'data-lightbox-code="' . e(($code !== '' && $code !== '#') ? $code : '') . '"',
    ];
    if ($liveLabel !== '') {
        $parts[] = 'data-lightbox-live-label="' . e($liveLabel) . '"';
    }
    if ($codeLabel !== '') {
        $parts[] = 'data-lightbox-code-label="' . e($codeLabel) . '"';
    }
    return implode(' ', $parts);
}

/**
 * Run work after the HTML has already been sent, so a slow API refresh
 * does not leave the browser on a blank page.
 *
 * Returns false when the host cannot finish the response early. Callers
 * should keep serving the cache in that case.
 */
function defer_after_response(callable $task, string $key): bool
{
    if (!function_exists('fastcgi_finish_request') && !function_exists('litespeed_finish_request')) {
        return false;
    }

    static $queued = [];
    static $registered = false;

    if (isset($queued[$key])) {
        return true;
    }
    $queued[$key] = $task;
    if ($registered) {
        return true;
    }
    $registered = true;

    register_shutdown_function(static function () use (&$queued): void {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            litespeed_finish_request();
        }

        ignore_user_abort(true);
        @set_time_limit(120);
        foreach ($queued as $job) {
            try {
                $job();
            } catch (Throwable $e) {
                // A failed refresh must not affect the page already sent.
            }
        }
    });

    return true;
}

/**
 * Normalize home signal groups into label + entry rows.
 * Supports legacy {label,value} and new {label,entries:[{role,name,url}]}.
 *
 * @param list<array<string,mixed>> $items
 * @return list<array{label:string,entries:list<array{role:string,name:string,url:string}>}>
 */
function normalize_signal_items(array $items): array
{
    $urlByName = [
        'soup it' => 'https://soupit.ar',
        'homespot' => 'https://homespot.com.ar',
        'hybrid bee technology' => 'https://hybridbee.tech',
    ];

    $out = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $label = trim((string) ($item['label'] ?? ''));
        $entries = [];

        if (isset($item['entries']) && is_array($item['entries'])) {
            foreach ($item['entries'] as $entry) {
                if (!is_array($entry)) {
                    continue;
                }
                $role = trim((string) ($entry['role'] ?? ''));
                $name = trim((string) ($entry['name'] ?? ''));
                $url = trim((string) ($entry['url'] ?? ''));
                if ($name === '' && $role === '') {
                    continue;
                }
                if ($url === '' && $name !== '') {
                    $url = $urlByName[strtolower($name)] ?? '';
                }
                $entries[] = ['role' => $role, 'name' => $name !== '' ? $name : $role, 'url' => $url];
            }
        }

        if ($entries === []) {
            $value = trim((string) ($item['value'] ?? ''));
            if ($value !== '') {
                $parts = preg_split('/\s*,\s*/u', $value) ?: [];
                foreach ($parts as $part) {
                    $part = trim((string) $part);
                    if ($part === '' || preg_match('/^and more$/iu', $part)) {
                        continue;
                    }
                    $role = '';
                    $name = $part;
                    if (preg_match('/^(.+?)\s+at\s+(.+)$/iu', $part, $m)) {
                        $role = trim($m[1]);
                        $name = trim($m[2]);
                    }
                    $url = $urlByName[strtolower($name)] ?? '';
                    $entries[] = ['role' => $role, 'name' => $name, 'url' => $url];
                }
            }
        }

        if ($label === '' && $entries === []) {
            continue;
        }
        $out[] = ['label' => $label, 'entries' => $entries];
    }

    return $out;
}

/** Inline SVG icon for a signal group label. */
function signal_group_icon(string $label): string
{
    $key = strtolower(trim($label));
    if ($key === 'now') {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M10 4h4l1 3h4v3h-2l-1.5 10h-7L7 10H5V7h4l1-3z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 14h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
    }
    if ($key === 'building') {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 20h16M7 20V9l5-4 5 4v11M10 20v-5h4v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }
    if ($key === 'base') {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s6-5.2 6-10a6 6 0 1 0-12 0c0 4.8 6 10 6 10z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="11" r="2.2" stroke="currentColor" stroke-width="1.8"/></svg>';
    }

    return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="7" stroke="currentColor" stroke-width="1.8"/></svg>';
}
