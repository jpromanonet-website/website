<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$pageTitle = 'Resumes';
$pageDescription = 'Download Juan P. Romano’s CV in Spanish and English.';
$activeNav = 'resumes';

$resumes = load_json('resumes');

require APP_ROOT . '/includes/header.php';
render_page_header('Resumes', '', 'CV');
?>

<main id="main" class="layout layout--with-sidebar">
    <div>
        <section class="section">
            <h2 class="section-heading">Download</h2>

            <div class="resume-grid">
                <?php foreach ($resumes as $resume):
                    $title = (string) ($resume['title'] ?? 'Resume');
                    $file = (string) ($resume['file'] ?? '');
                    $label = (string) ($resume['label'] ?? '');
                    $description = (string) ($resume['description'] ?? '');
                    $exists = $file !== '' && pdf_exists($file);
                ?>
                    <article class="resume-card reveal">
                        <span class="catalog-item__meta"><?= e($label !== '' ? $label : 'PDF') ?></span>
                        <h2><?= e($title) ?></h2>
                        <?php if ($description !== ''): ?>
                            <p><?= e($description) ?></p>
                        <?php endif; ?>

                        <?php if ($exists): ?>
                            <a class="btn btn--primary" href="<?= e(pdf_url($file)) ?>" download>
                                Download PDF
                            </a>
                        <?php else: ?>
                            <span class="btn btn--ghost is-disabled" aria-disabled="true">PDF coming soon</span>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <aside class="sidebar">
        <h2>On this site</h2>
        <ul class="sidebar-nav">
            <li><a href="<?= e(url('/portfolio/')) ?>">Portfolio</a></li>
            <li><a href="<?= e(url('/books/')) ?>">Books</a></li>
            <li><a href="<?= e(url('/writing/')) ?>">Writing</a></li>
            <li><a href="<?= e(url('/ventures/')) ?>">Ventures</a></li>
            <li><a href="<?= e(url('/news/')) ?>">News</a></li>
            <li><a class="is-active" href="<?= e(url('/resumes/')) ?>">Resumes</a></li>
        </ul>
        <div class="sidebar-cta">
            <a class="btn btn--soft" href="<?= e($site['linkedin']) ?>" target="_blank" rel="noopener noreferrer">LinkedIn profile</a>
        </div>
    </aside>
</main>

<?php require APP_ROOT . '/includes/footer.php'; ?>
