<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pageTitle = 'Writing';
$pageDescription = 'Articles and publications by Juan P. Romano — including automatic Medium posts.';
$activeNav = 'writing';

$mediumPosts = fetch_medium_posts();
$staticArticles = load_json('writing');

// Static JSON first, then Medium (automatic)
$articles = [];
foreach ($staticArticles as $article) {
    $articles[] = [
        'title' => (string) ($article['title'] ?? 'Untitled'),
        'url' => (string) ($article['url'] ?? '#'),
        'category' => (string) ($article['category'] ?? ''),
        'imageSrc' => (string) ($article['imageSrc'] ?? ''),
        'source' => 'static',
    ];
}
foreach ($mediumPosts as $post) {
    $articles[] = $post;
}

$categories = unique_categories($articles);

require APP_ROOT . '/includes/header.php';
render_page_header('Writing', '', 'Articles');
?>

<main id="main" class="layout layout--with-sidebar">
    <div>
        <div class="toolbar">
            <input class="search-input" type="search" placeholder="Search articles…" data-catalog-search aria-label="Search articles" />
            <div class="filter-row" role="group" aria-label="Filter by publisher">
                <button type="button" class="filter-btn is-active" data-filter="all">All</button>
                <?php foreach ($categories as $category): ?>
                    <button type="button" class="filter-btn" data-filter="<?= e(strtolower($category)) ?>"><?= e($category) ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <p class="catalog-count" data-catalog-count data-noun="articles" aria-live="polite"><?= count($articles) ?> articles</p>

        <div class="catalog-grid" data-catalog>
            <?php foreach ($articles as $article):
                $title = (string) ($article['title'] ?? 'Untitled');
                $category = (string) ($article['category'] ?? '');
                $image = (string) ($article['imageSrc'] ?? '');
                $link = (string) ($article['url'] ?? '#');
                $source = (string) ($article['source'] ?? 'static');
                $isMedium = $source === 'medium' || strcasecmp($category, 'Medium') === 0;
                $search = strtolower($title . ' ' . $category);
                $imgSrc = $image !== '' ? media_url('writing', $image) : '';
            ?>
                <article
                    class="catalog-item"
                    data-item
                    data-category="<?= e(strtolower($category)) ?>"
                    data-search="<?= e($search) ?>"
                >
                    <div class="catalog-item__media<?= $isMedium ? ' catalog-item__media--logo' : '' ?>">
                        <?php if ($imgSrc !== ''): ?>
                            <img src="<?= e($imgSrc) ?>" alt="<?= e($isMedium ? 'Medium' : ($category !== '' ? $category : $title)) ?>" loading="lazy" />
                        <?php endif; ?>
                    </div>
                    <div class="catalog-item__body">
                        <?php if ($category !== ''): ?>
                            <span class="catalog-item__meta"><?= e($category) ?></span>
                        <?php endif; ?>
                        <h2 class="catalog-item__title"><?= e($title) ?></h2>
                        <div class="catalog-item__actions">
                            <?php if ($link !== '' && $link !== '#'): ?>
                                <a href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer">Read</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="catalog-empty is-hidden" data-catalog-empty>No articles match that filter.</p>
    </div>

    <aside class="sidebar">
        <h2>On this site</h2>
        <ul class="sidebar-nav">
            <li><a href="<?= e(url('/portfolio/')) ?>">Portfolio</a></li>
            <li><a href="<?= e(url('/books/')) ?>">Books</a></li>
            <li><a class="is-active" href="<?= e(url('/writing/')) ?>">Writing</a></li>
            <li><a href="<?= e(url('/ventures/')) ?>">Ventures</a></li>
            <li><a href="<?= e(url('/news/')) ?>">News</a></li>
            <li><a href="<?= e(url('/resumes/')) ?>">Resumes</a></li>
        </ul>
        <div class="sidebar-cta">
            <a class="btn btn--soft" href="<?= e($site['blog']) ?>" target="_blank" rel="noopener noreferrer">jpromanonet.medium.com</a>
        </div>
    </aside>
</main>

<?php require APP_ROOT . '/includes/footer.php'; ?>
