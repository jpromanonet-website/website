<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pageTitle = 'Books read';
$pageDescription = 'Books I have finished — synced automatically from Goodreads.';
$activeNav = 'reading';

$allBooks = fetch_goodreads_read_books();
$stats = goodreads_read_stats();
$profileUrl = goodreads_profile_url();

$perPage = 21;
$totalBooks = count($allBooks);
$totalPages = max(1, (int) ceil($totalBooks / $perPage));
$currentPage = max(1, min($totalPages, (int) ($_GET['page'] ?? 1)));
$offset = ($currentPage - 1) * $perPage;
$books = array_slice($allBooks, $offset, $perPage);

require APP_ROOT . '/includes/header.php';
render_page_header('Books read', '', 'Goodreads');
?>

<main id="main" class="layout layout--with-sidebar">
    <div>
        <div class="reading-stats">
            <article class="reading-stat reading-stat--books">
                <span class="reading-stat__meta">Goodreads</span>
                <p class="reading-stat__value"><?= (int) $stats['books'] ?></p>
                <h2 class="reading-stat__title">Books read</h2>
                <p class="reading-stat__note">Marked as read on Goodreads.</p>
            </article>
            <article class="reading-stat reading-stat--pages">
                <span class="reading-stat__meta">Total</span>
                <p class="reading-stat__value"><?= number_format((int) $stats['pages']) ?></p>
                <h2 class="reading-stat__title">Pages read</h2>
                <p class="reading-stat__note">Sum of page counts from the read shelf.</p>
            </article>
        </div>

        <?php if ($allBooks === []): ?>
            <p class="catalog-empty">
                No read books found yet. Check that the Goodreads shelf is public, then clear
                <code>cache/goodreads-read.json</code> to refresh.
            </p>
        <?php else: ?>
            <p class="catalog-count">
                Showing <?= count($books) ?> of <?= $totalBooks ?> books
                <?php if ($totalPages > 1): ?>
                    · Page <?= $currentPage ?> of <?= $totalPages ?>
                <?php endif; ?>
            </p>
            <div class="catalog-grid catalog-grid--reading">
                <?php foreach ($books as $book):
                    $title = (string) ($book['title'] ?? 'Untitled');
                    $author = (string) ($book['author'] ?? '');
                    $cover = (string) ($book['cover'] ?? '');
                    $url = (string) ($book['url'] ?? '');
                    $pages = (int) ($book['pages'] ?? 0);
                    $rating = (int) ($book['rating'] ?? 0);
                    $metaParts = [];
                    if ($pages > 0) {
                        $metaParts[] = number_format($pages) . ' pages';
                    }
                    if ($rating > 0) {
                        $metaParts[] = str_repeat('★', $rating);
                    }
                    $meta = implode(' · ', $metaParts);
                ?>
                    <article class="catalog-item catalog-item--reading">
                        <div
                            class="catalog-item__media catalog-item__media--cover<?= $cover !== '' ? ' catalog-item__media--zoomable' : '' ?>"
                            <?= $cover !== '' ? lightbox_data_attrs($cover, $title, $url, '', 'Open on Goodreads') : '' ?>
                        >
                            <?php if ($cover !== ''): ?>
                                <img src="<?= e($cover) ?>" alt="<?= e($title) ?>" loading="lazy" />
                            <?php else: ?>
                                <div class="catalog-item__placeholder" aria-hidden="true">B</div>
                            <?php endif; ?>
                        </div>
                        <div class="catalog-item__body">
                            <?php if ($author !== ''): ?>
                                <span class="catalog-item__meta"><?= e($author) ?></span>
                            <?php endif; ?>
                            <h2 class="catalog-item__title"><?= e($title) ?></h2>
                            <?php if ($meta !== ''): ?>
                                <p class="catalog-item__brief"><?= e($meta) ?></p>
                            <?php endif; ?>
                            <div class="catalog-item__actions">
                                <?php if ($url !== ''): ?>
                                    <a href="<?= e($url) ?>" target="_blank" rel="noopener noreferrer">Goodreads</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="pager" aria-label="Books read pagination">
                    <?php if ($currentPage > 1): ?>
                        <a class="btn btn--soft" href="<?= e(url('/reading/') . ($currentPage - 1 === 1 ? '' : '?page=' . ($currentPage - 1))) ?>">Previous</a>
                    <?php else: ?>
                        <span class="btn btn--ghost is-disabled" aria-disabled="true">Previous</span>
                    <?php endif; ?>

                    <span class="pager__status">Page <?= $currentPage ?> / <?= $totalPages ?></span>

                    <?php if ($currentPage < $totalPages): ?>
                        <a class="btn btn--soft" href="<?= e(url('/reading/') . '?page=' . ($currentPage + 1)) ?>">Next</a>
                    <?php else: ?>
                        <span class="btn btn--ghost is-disabled" aria-disabled="true">Next</span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <aside class="sidebar">
        <h2>On this site</h2>
        <?php render_sidebar_nav('reading'); ?>
        <div class="sidebar-cta">
            <a class="btn btn--soft" href="<?= e($profileUrl) ?>" target="_blank" rel="noopener noreferrer">Open Goodreads</a>
        </div>
    </aside>
</main>

<?php require APP_ROOT . '/includes/footer.php'; ?>
