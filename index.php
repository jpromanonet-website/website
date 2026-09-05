<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = $site['name'];
$pageDescription = 'Juan P. Romano — Engineering Manager at Hybrid Bee Technology, Packt author, and polyglot software engineer based in Buenos Aires.';
$activeNav = 'home';

// Newest projects first — take the latest 9
$featuredProjects = array_slice(load_json('projects'), 0, 9);
$mediumPosts = fetch_medium_posts();
$latestMedium = $mediumPosts[0] ?? null;

$siteStats = [
    [
        'label' => 'Projects in my portfolio',
        'count' => count(load_json('projects')),
        'path' => '/portfolio/',
        'tone' => 'blue',
    ],
    [
        'label' => 'Books written',
        'count' => count(load_json('books')),
        'path' => '/books/',
        'tone' => 'amber',
    ],
    [
        'label' => 'Publications',
        'count' => count(load_json('writing')) + count($mediumPosts),
        'path' => '/writing/',
        'tone' => 'teal',
    ],
    [
        'label' => 'Ventures launched',
        'count' => count(load_json('ventures')),
        'path' => '/ventures/',
        'tone' => 'green',
    ],
    [
        'label' => 'Times I made the news',
        'count' => count(load_json('news')),
        'path' => '/news/',
        'tone' => 'coral',
    ],
];

// Custom CMS pages appear here (before resumes), even with 0 elements
if (class_exists(\MicroCMS\Content::class)) {
    try {
        foreach (\MicroCMS\Content::customPageStats() as $customStat) {
            $siteStats[] = $customStat;
        }
    } catch (Throwable $e) {
        // ignore
    }
}

$siteStats[] = [
    'label' => 'Resumes ready',
    'count' => count(load_json('resumes')),
    'path' => '/resumes/',
    'tone' => 'slate',
];

$hero = $homeBlocks['hero'] ?? [];
$about = $homeBlocks['about'] ?? [];
$signals = $homeBlocks['signals'] ?? [];
$skills = $homeBlocks['skills'] ?? [];
$contact = $homeBlocks['contact'] ?? [];

$heroKicker = (string) ($hero['kicker'] ?? 'Engineering Manager · Writer · Polyglot');
$heroSubtitle = (string) ($hero['subtitle'] ?? $site['tagline']);
$primaryLabel = (string) ($hero['primary_cta_label'] ?? 'Connect on LinkedIn');
$primaryKey = (string) ($hero['primary_cta_url'] ?? 'linkedin');
$secondaryLabel = (string) ($hero['secondary_cta_label'] ?? 'View portfolio');
$secondaryPath = (string) ($hero['secondary_cta_path'] ?? '/portfolio/');

if ($primaryKey === 'email') {
    $primaryHref = 'mailto:' . $site['email'];
    $primaryExternal = false;
} else {
    $primaryHref = (string) ($site[$primaryKey] ?? $site['linkedin']);
    $primaryExternal = true;
}

$aboutParagraphs = $about['paragraphs'] ?? [];
if ($aboutParagraphs === []) {
    $aboutParagraphs = [
        'I’m Juan, a software engineer and Engineering Manager based in Buenos Aires.',
    ];
}
$signalItems = $signals['items'] ?? [];
$skillsLead = (string) ($skills['lead'] ?? '');
$skillGroups = $skills['groups'] ?? [];
$contactLead = (string) ($contact['lead'] ?? 'Ideas, collaborations, or a good conversation about building things.');

require APP_ROOT . '/includes/header.php';
?>

<section class="hero" aria-label="Introduction">
    <div class="hero__inner">
        <p class="hero__kicker"><?= e($heroKicker) ?></p>
        <h1 class="hero__title"><?= e($site['name']) ?></h1>
        <p class="hero__subtitle"><?= e($heroSubtitle) ?></p>
        <div class="hero__actions">
            <a class="btn btn--primary" href="<?= e($primaryHref) ?>"<?= $primaryExternal ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($primaryLabel) ?></a>
            <a class="btn btn--ghost" href="<?= e(url($secondaryPath)) ?>"><?= e($secondaryLabel) ?></a>
        </div>
    </div>
</section>

<main id="main" class="layout">
    <div>
        <section class="section reveal" id="about">
            <h2 class="section-heading">About</h2>
            <div class="prose">
                <?php foreach ($aboutParagraphs as $paragraph): ?>
                    <p><?= e((string) $paragraph) ?></p>
                <?php endforeach; ?>
                <p>
                    See the full path on
                    <a href="<?= e($site['linkedin']) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>,
                    browse the <a href="<?= e(url('/portfolio/')) ?>">portfolio</a>,
                    or read on <a href="<?= e($site['blog']) ?>" target="_blank" rel="noopener noreferrer">Medium</a>.
                </p>
            </div>

            <?php if ($signalItems): ?>
            <ul class="signal-list">
                <?php foreach ($signalItems as $signal): ?>
                    <li>
                        <span class="signal-list__label"><?= e((string) ($signal['label'] ?? '')) ?></span>
                        <span class="signal-list__value"><?= e((string) ($signal['value'] ?? '')) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </section>

        <section class="section reveal" id="at-a-glance" aria-label="Myself in numbers">
            <h2 class="section-heading">Myself in numbers</h2>
            <div class="stat-grid">
                <?php foreach ($siteStats as $stat): ?>
                    <a
                        class="stat-card stat-card--<?= e((string) $stat['tone']) ?>"
                        href="<?= e(url((string) $stat['path'])) ?>"
                    >
                        <span class="stat-card__count"><?= (int) $stat['count'] ?></span>
                        <span class="stat-card__label"><?= e((string) $stat['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <?php if ($latestMedium): ?>
        <section class="section reveal" id="latest-writing">
            <div class="section-heading-row">
                <div>
                    <h2 class="section-heading">Latest on Medium</h2>
                    <p class="section-lead">The most recent post from my blog.</p>
                </div>
                <a class="btn btn--soft" href="<?= e(url('/writing/')) ?>">All writing</a>
            </div>

            <a
                class="medium-latest"
                href="<?= e((string) $latestMedium['url']) ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                <div class="medium-latest__logo" aria-hidden="true">
                    <img src="<?= e(media_url('writing', 'medium.svg')) ?>" alt="" />
                </div>
                <div class="medium-latest__body">
                    <span class="medium-latest__meta">Medium</span>
                    <h3 class="medium-latest__title"><?= e((string) $latestMedium['title']) ?></h3>
                    <span class="medium-latest__cta">Read on Medium →</span>
                </div>
            </a>
        </section>
        <?php endif; ?>

        <?php if ($featuredProjects): ?>
        <section class="section reveal" id="featured">
            <div class="section-heading-row">
                <div>
                    <h2 class="section-heading">Recent work</h2>
                    <p class="section-lead">A snapshot of the latest projects.</p>
                </div>
                <a class="btn btn--soft" href="<?= e(url('/portfolio/')) ?>">Full portfolio</a>
            </div>

            <div class="project-carousel" data-carousel data-carousel-desktop="3" data-carousel-mobile="1">
                <div class="project-carousel__viewport">
                    <div class="project-carousel__track" data-carousel-track>
                        <?php foreach ($featuredProjects as $project):
                            $title = (string) ($project['title'] ?? 'Untitled');
                            $category = (string) ($project['category'] ?? '');
                            $image = (string) ($project['imageSrc'] ?? '');
                            $live = (string) ($project['liveUrl'] ?? '');
                            $github = (string) ($project['githubUrl'] ?? '');
                            $href = ($live !== '' && $live !== '#') ? $live : (($github !== '' && $github !== '#') ? $github : url('/portfolio/'));
                            $external = is_external($href);
                        ?>
                            <a
                                class="catalog-item project-carousel__card"
                                data-carousel-item
                                href="<?= e($href) ?>"
                                <?= $external ? 'target="_blank" rel="noopener noreferrer"' : '' ?>
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
                                    <h3 class="catalog-item__title"><?= e($title) ?></h3>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="project-carousel__controls">
                    <button type="button" class="project-carousel__btn" data-carousel-prev aria-label="Previous projects">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div class="project-carousel__dots" data-carousel-dots role="tablist" aria-label="Carousel pages"></div>
                    <button type="button" class="project-carousel__btn" data-carousel-next aria-label="Next projects">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section class="section reveal" id="skills">
            <h2 class="section-heading">Skills &amp; technologies</h2>
            <?php if ($skillsLead !== ''): ?>
                <p class="section-lead"><?= e($skillsLead) ?></p>
            <?php endif; ?>
            <div class="skills-grid">
                <?php foreach ($skillGroups as $group): ?>
                    <div class="skill-block">
                        <h3><?= e((string) ($group['title'] ?? '')) ?></h3>
                        <ul class="skill-list">
                            <?php foreach (($group['items'] ?? []) as $item): ?>
                                <li><?= e((string) $item) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section reveal" id="contact">
            <h2 class="section-heading">Contact</h2>
            <p class="section-lead"><?= e($contactLead) ?></p>
            <div class="contact-list">
                <a class="contact-row" href="<?= e($site['linkedin']) ?>" target="_blank" rel="noopener noreferrer">
                    <span class="contact-row__meta">LinkedIn</span>
                    <span class="contact-row__value">linkedin.com/in/jpromanonet</span>
                </a>
                <a class="contact-row" href="mailto:<?= e($site['email']) ?>">
                    <span class="contact-row__meta">Email</span>
                    <span class="contact-row__value"><?= e($site['email']) ?></span>
                </a>
                <?php if (!empty($site['phone'])): ?>
                <a class="contact-row" href="tel:<?= e(preg_replace('/\s+/', '', $site['phone']) ?? $site['phone']) ?>">
                    <span class="contact-row__meta">Phone</span>
                    <span class="contact-row__value"><?= e($site['phone']) ?></span>
                </a>
                <?php endif; ?>
                <a class="contact-row" href="<?= e($site['blog']) ?>" target="_blank" rel="noopener noreferrer">
                    <span class="contact-row__meta">Blog</span>
                    <span class="contact-row__value">jpromanonet.medium.com</span>
                </a>
            </div>
            <div class="social-row" aria-label="Social links">
                <a href="<?= e($site['linkedin']) ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">in</a>
                <a href="<?= e($site['github']) ?>" target="_blank" rel="noopener noreferrer" aria-label="GitHub">gh</a>
                <a href="<?= e($site['x']) ?>" target="_blank" rel="noopener noreferrer" aria-label="X">x</a>
                <a href="<?= e($site['instagram']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram">ig</a>
            </div>
        </section>
    </div>
</main>

<?php require APP_ROOT . '/includes/footer.php'; ?>
