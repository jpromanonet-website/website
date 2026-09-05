<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pageTitle = 'News';
$pageDescription = 'Press and media coverage featuring Juan P. Romano.';
$activeNav = 'news';

$news = load_json('news');
$categories = unique_categories($news);

require APP_ROOT . '/includes/header.php';
render_page_header('News', '', 'Coverage');
?>

<main id="main" class="layout layout--with-sidebar">
    <div>
        <div class="toolbar">
            <input class="search-input" type="search" placeholder="Search news…" data-catalog-search aria-label="Search news" />
            <div class="filter-row" role="group" aria-label="Filter by type">
                <button type="button" class="filter-btn is-active" data-filter="all">All</button>
                <?php foreach ($categories as $category): ?>
                    <button type="button" class="filter-btn" data-filter="<?= e(strtolower($category)) ?>"><?= e($category) ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <p class="catalog-count" data-catalog-count data-noun="mentions" aria-live="polite"><?= count($news) ?> mentions</p>

        <div class="catalog-grid" data-catalog>
            <?php foreach ($news as $item):
                $title = (string) ($item['title'] ?? 'Untitled');
                $category = (string) ($item['category'] ?? '');
                $image = (string) ($item['imageSrc'] ?? '');
                $link = (string) ($item['url'] ?? '#');
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
                            <img src="<?= e(media_url('news', $image)) ?>" alt="<?= e($title) ?>" loading="lazy" />
                        <?php endif; ?>
                    </div>
                    <div class="catalog-item__body">
                        <?php if ($category !== ''): ?>
                            <span class="catalog-item__meta"><?= e($category) ?></span>
                        <?php endif; ?>
                        <h2 class="catalog-item__title"><?= e($title) ?></h2>
                        <div class="catalog-item__actions">
                            <?php if ($link !== '' && $link !== '#'): ?>
                                <a href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer">Open</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="catalog-empty is-hidden" data-catalog-empty>No news items match that filter.</p>
    </div>

    <aside class="sidebar">
        <h2>On this site</h2>
        <?php render_sidebar_nav('news'); ?>
    </aside>
</main>

<?php require APP_ROOT . '/includes/footer.php'; ?>
