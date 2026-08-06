<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pageTitle = 'Books';
$pageDescription = 'Books by Juan P. Romano.';
$activeNav = 'books';

$books = load_json('books');
$categories = unique_categories($books);

require APP_ROOT . '/includes/header.php';
render_page_header('Books', '', 'Writing');
?>

<main id="main" class="layout layout--with-sidebar">
    <div>
        <?php if ($categories): ?>
            <div class="toolbar">
                <div class="filter-row" role="group" aria-label="Filter by category">
                    <button type="button" class="filter-btn is-active" data-filter="all">All</button>
                    <?php foreach ($categories as $category): ?>
                        <button type="button" class="filter-btn" data-filter="<?= e(strtolower($category)) ?>"><?= e($category) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <p class="catalog-count" data-catalog-count data-noun="books" aria-live="polite"><?= count($books) ?> books</p>

        <div class="catalog-grid catalog-grid--list" data-catalog>
            <?php foreach ($books as $book):
                $title = (string) ($book['title'] ?? 'Untitled');
                $category = (string) ($book['category'] ?? '');
                $image = (string) ($book['imageSrc'] ?? '');
                $brief = (string) ($book['brief'] ?? '');
                $author = (string) ($book['author'] ?? '');
                $status = (string) ($book['status'] ?? '');
                $buying = (string) ($book['buyingLink'] ?? '');
                $search = strtolower($title . ' ' . $category . ' ' . $brief);
            ?>
                <article
                    class="catalog-item catalog-item--horizontal"
                    data-item
                    data-category="<?= e(strtolower($category)) ?>"
                    data-search="<?= e($search) ?>"
                >
                    <div class="catalog-item__media">
                        <?php if ($image !== ''): ?>
                            <img src="<?= e(media_url('books', $image)) ?>" alt="<?= e($title) ?>" loading="lazy" />
                        <?php endif; ?>
                    </div>
                    <div class="catalog-item__body">
                        <?php if ($category !== ''): ?>
                            <span class="catalog-item__meta"><?= e($category) ?></span>
                        <?php endif; ?>
                        <h2 class="catalog-item__title"><?= e($title) ?></h2>
                        <?php if ($brief !== ''): ?>
                            <p class="catalog-item__brief"><?= e($brief) ?></p>
                        <?php endif; ?>
                        <?php if ($author !== ''): ?>
                            <p class="catalog-item__brief"><?= e($author) ?></p>
                        <?php endif; ?>
                        <div class="catalog-item__actions">
                            <?php if ($buying !== ''): ?>
                                <a href="<?= e($buying) ?>" target="_blank" rel="noopener noreferrer">Buy</a>
                            <?php elseif ($status === 'coming_soon'): ?>
                                <span class="is-disabled">Coming soon</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="catalog-empty is-hidden" data-catalog-empty>No books match that filter.</p>
    </div>

    <aside class="sidebar">
        <h2>On this site</h2>
        <ul class="sidebar-nav">
            <li><a href="<?= e(url('/portfolio/')) ?>">Portfolio</a></li>
            <li><a class="is-active" href="<?= e(url('/books/')) ?>">Books</a></li>
            <li><a href="<?= e(url('/writing/')) ?>">Writing</a></li>
            <li><a href="<?= e(url('/ventures/')) ?>">Ventures</a></li>
            <li><a href="<?= e(url('/news/')) ?>">News</a></li>
            <li><a href="<?= e(url('/resumes/')) ?>">Resumes</a></li>
        </ul>
        <div class="sidebar-cta">
            <a class="btn btn--soft" href="<?= e($site['blog']) ?>" target="_blank" rel="noopener noreferrer">Read on Medium</a>
        </div>
    </aside>
</main>

<?php require APP_ROOT . '/includes/footer.php'; ?>
