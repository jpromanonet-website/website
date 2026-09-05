<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pageTitle = 'Ventures';
$pageDescription = 'Ventures and products built by Juan P. Romano.';
$activeNav = 'ventures';

$ventures = load_catalog('ventures');
$categories = unique_categories($ventures);

// Sort by year descending when category is a year
usort($ventures, static function (array $a, array $b): int {
    return strcmp((string) ($b['category'] ?? ''), (string) ($a['category'] ?? ''));
});

require APP_ROOT . '/includes/header.php';
render_page_header('Ventures', '', 'Business');
?>

<main id="main" class="layout layout--with-sidebar">
    <div>
        <div class="toolbar">
            <div class="filter-row" role="group" aria-label="Filter by year">
                <button type="button" class="filter-btn is-active" data-filter="all">All</button>
                <?php foreach ($categories as $category): ?>
                    <button type="button" class="filter-btn" data-filter="<?= e(strtolower($category)) ?>"><?= e($category) ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <p class="catalog-count" data-catalog-count data-noun="ventures" aria-live="polite"><?= count($ventures) ?> ventures</p>

        <div class="catalog-grid" data-catalog>
            <?php foreach ($ventures as $venture):
                $title = (string) ($venture['title'] ?? 'Untitled');
                $category = (string) ($venture['category'] ?? '');
                $image = (string) ($venture['imageSrc'] ?? '');
                $link = (string) ($venture['url'] ?? '#');
                $search = strtolower($title . ' ' . $category);
            ?>
                <article
                    class="catalog-item"
                    data-item
                    data-category="<?= e(strtolower($category)) ?>"
                    data-search="<?= e($search) ?>"
                >
                    <div class="catalog-item__media">
                        <?php if ($image !== ''): ?>
                            <img src="<?= e(media_url('ventures', $image)) ?>" alt="<?= e($title) ?>" loading="lazy" />
                        <?php endif; ?>
                    </div>
                    <div class="catalog-item__body">
                        <?php if ($category !== ''): ?>
                            <span class="catalog-item__meta"><?= e($category) ?></span>
                        <?php endif; ?>
                        <h2 class="catalog-item__title"><?= e($title) ?></h2>
                        <div class="catalog-item__actions">
                            <?php if ($link !== '' && $link !== '#'): ?>
                                <a href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer">Visit</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="catalog-empty is-hidden" data-catalog-empty>No ventures match that filter.</p>
    </div>

    <aside class="sidebar">
        <h2>On this site</h2>
        <?php render_sidebar_nav('ventures'); ?>
    </aside>
</main>

<?php require APP_ROOT . '/includes/footer.php'; ?>
