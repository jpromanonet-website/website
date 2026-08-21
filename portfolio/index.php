<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pageTitle = 'Portfolio';
$pageDescription = 'Selected projects by Juan P. Romano — web, desktop, games, and more.';
$activeNav = 'portfolio';

$projects = load_json('projects');
$categories = unique_categories($projects);

require APP_ROOT . '/includes/header.php';
render_page_header('Portfolio', '', 'Projects');
?>

<main id="main" class="layout layout--with-sidebar">
    <div>
        <div class="toolbar">
            <input class="search-input" type="search" placeholder="Search projects…" data-catalog-search aria-label="Search projects" />
            <div class="filter-row" role="group" aria-label="Filter by category">
                <button type="button" class="filter-btn is-active" data-filter="all">All</button>
                <?php foreach ($categories as $category): ?>
                    <button type="button" class="filter-btn" data-filter="<?= e(strtolower($category)) ?>"><?= e($category) ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <p class="catalog-count" data-catalog-count data-noun="projects" aria-live="polite"><?= count($projects) ?> projects</p>

        <div class="catalog-grid" data-catalog>
            <?php foreach ($projects as $project):
                $title = (string) ($project['title'] ?? 'Untitled');
                $category = (string) ($project['category'] ?? '');
                $image = (string) ($project['imageSrc'] ?? '');
                $live = (string) ($project['liveUrl'] ?? '');
                $github = (string) ($project['githubUrl'] ?? '');
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
                            <img src="<?= e(media_url('portfolio', $image)) ?>" alt="<?= e($title) ?>" loading="lazy" />
                        <?php endif; ?>
                    </div>
                    <div class="catalog-item__body">
                        <?php if ($category !== ''): ?>
                            <span class="catalog-item__meta"><?= e($category) ?></span>
                        <?php endif; ?>
                        <h2 class="catalog-item__title"><?= e($title) ?></h2>
                        <div class="catalog-item__actions">
                            <?php if ($live !== '' && $live !== '#'): ?>
                                <a href="<?= e($live) ?>" target="_blank" rel="noopener noreferrer">Live</a>
                            <?php endif; ?>
                            <?php if ($github !== '' && $github !== '#'): ?>
                                <a href="<?= e($github) ?>" target="_blank" rel="noopener noreferrer">Code</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="catalog-empty is-hidden" data-catalog-empty>No projects match that filter.</p>
    </div>

    <aside class="sidebar">
        <h2>On this site</h2>
        <ul class="sidebar-nav">
            <li><a class="is-active" href="<?= e(url('/portfolio/')) ?>">Portfolio</a></li>
            <li><a href="<?= e(url('/books/')) ?>">Books</a></li>
            <li><a href="<?= e(url('/writing/')) ?>">Writing</a></li>
            <li><a href="<?= e(url('/ventures/')) ?>">Ventures</a></li>
            <li><a href="<?= e(url('/news/')) ?>">News</a></li>
            <li><a href="<?= e(url('/resumes/')) ?>">Resumes</a></li>
        </ul>
        <div class="sidebar-cta">
            <a class="btn btn--soft" href="<?= e($site['github']) ?>" target="_blank" rel="noopener noreferrer">GitHub profile</a>
        </div>
    </aside>
</main>

<?php require APP_ROOT . '/includes/footer.php'; ?>
