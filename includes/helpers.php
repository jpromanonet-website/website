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

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function load_json(string $name): array
{
    if (class_exists(\MicroCMS\Content::class)) {
        try {
            return \MicroCMS\Content::cardsAsLegacyJson($name);
        } catch (Throwable $e) {
            // Fall through to JSON files
        }
    }

    $file = APP_ROOT . '/assets/data/' . $name . '.json';
    if (!is_file($file)) {
        return [];
    }
    $raw = file_get_contents($file);
    if ($raw === false || $raw === '') {
        return [];
    }
    // Strip UTF-8 BOM if present (common on Windows exports)
    if (str_starts_with($raw, "\xEF\xBB\xBF")) {
        $raw = substr($raw, 3);
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
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
            <?php if (!empty($item['children'])) {
                continue;
            } ?>
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
