<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = $site['name'];
$pageDescription = 'Juan P. Romano — Engineering Manager at Hybrid Bee Technology, Packt author, and polyglot software engineer based in Buenos Aires.';
$activeNav = 'home';

// Newest projects first in projects.json — take the latest 9
$featuredProjects = array_slice(load_json('projects'), 0, 9);

require APP_ROOT . '/includes/header.php';
?>

<section class="hero" aria-label="Introduction">
    <div class="hero__inner">
        <p class="hero__kicker">Engineering Manager · Writer · Polyglot</p>
        <h1 class="hero__title"><?= e($site['name']) ?></h1>
        <p class="hero__subtitle">
            I ship products, lead teams, and write about software.
            Based in Buenos Aires — currently Engineering Manager at Hybrid Bee Technology.
        </p>
        <div class="hero__actions">
            <a class="btn btn--primary" href="<?= e($site['linkedin']) ?>" target="_blank" rel="noopener noreferrer">Connect on LinkedIn</a>
            <a class="btn btn--ghost" href="<?= e(url('/portfolio/')) ?>">View portfolio</a>
        </div>
    </div>
</section>

<main id="main" class="layout">
    <div>
        <section class="section reveal" id="about">
            <h2 class="section-heading">About</h2>
            <p class="section-lead">Curious mind. Builder by trade. ~15 years shipping software, leading teams, and teaching the craft.</p>
            <div class="prose">
                <p>
                    I’m Juan, a software engineer and Engineering Manager based in Buenos Aires.
                    I work across Java, Python (Django), C++, and JavaScript (Node, React, Angular, Vue),
                    and I love turning messy problems into clean, shippable systems.
                </p>
                <p>
                    Right now I’m Engineering Manager at
                    <a href="https://hybridbeetechnology.com/" target="_blank" rel="noopener noreferrer">Hybrid Bee Technology</a>,
                    partnering with clients and building the custom tools that keep infrastructure projects moving.
                    I’m also writing and building different ventures.
                </p>
                <p>
                    Before that I led front-end at
                    <a href="https://www.oca.com.ar/" target="_blank" rel="noopener noreferrer">OCA</a>,
                    managed engineering at Adviters, and directed software development at Andreani,
                    plus years as a professor and technical writer for freeCodeCamp, Henry, and more.
                    I’m a polyglot
                    (<span class="lang-flags" aria-label="Languages: Spanish, Chinese, English, Russian, Portuguese, German, French, Italian, Japanese">🇪🇸 🇨🇳 🇬🇧 🇷🇺 🇧🇷 🇩🇪 🇫🇷 🇮🇹 🇯🇵</span>)
                    and a full-time nerd — in the best way.
                </p>
                <p>
                    See the full path on
                    <a href="<?= e($site['linkedin']) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>,
                    browse the <a href="<?= e(url('/portfolio/')) ?>">portfolio</a>,
                    or read on <a href="<?= e($site['blog']) ?>" target="_blank" rel="noopener noreferrer">Medium</a>.
                </p>
            </div>

            <ul class="signal-list">
                <li>
                    <span class="signal-list__label">Now</span>
                    <span class="signal-list__value">Engineering Manager · Hybrid Bee Technology</span>
                </li>
                <li>
                    <span class="signal-list__label">Building</span>
                    <span class="signal-list__value">Soup IT, Puestito, Mate Gestión and more</span>
                </li>
                <li>
                    <span class="signal-list__label">Base</span>
                    <span class="signal-list__value">Buenos Aires, Argentina</span>
                </li>
            </ul>
        </section>

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
            <p class="section-lead">What I’m actively shipping with — more tools live in the drawer, but these get the most airtime.</p>
            <div class="skills-grid">
                <div class="skill-block">
                    <h3>Languages</h3>
                    <ul class="skill-list">
                        <li>JavaScript</li><li>TypeScript</li><li>Python</li><li>Java</li>
                        <li>PHP</li><li>C#</li><li>C++</li><li>C</li>
                        <li>R</li><li>Ruby</li><li>Elixir</li><li>Perl</li><li>Scala</li>
                    </ul>
                </div>
                <div class="skill-block">
                    <h3>Frameworks</h3>
                    <ul class="skill-list">
                        <li>React</li><li>React Native</li><li>Vue</li><li>Angular</li>
                        <li>Node.js</li><li>Django</li><li>Flask</li><li>.NET</li>
                    </ul>
                </div>
                <div class="skill-block">
                    <h3>Style &amp; UI</h3>
                    <ul class="skill-list">
                        <li>HTML</li><li>CSS</li><li>Sass</li><li>Tailwind</li><li>Bootstrap</li>
                    </ul>
                </div>
                <div class="skill-block">
                    <h3>Platforms &amp; tools</h3>
                    <ul class="skill-list">
                        <li>SQL</li><li>MySQL</li><li>Docker</li><li>Kubernetes</li>
                        <li>AWS</li><li>Azure</li><li>Linux</li><li>Git</li>
                        <li>NGINX</li><li>Apache</li><li>Jira</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="section reveal" id="contact">
            <h2 class="section-heading">Contact</h2>
            <p class="section-lead">Ideas, collaborations, or a good conversation about building things.</p>
            <div class="contact-list">
                <a class="contact-row" href="<?= e($site['linkedin']) ?>" target="_blank" rel="noopener noreferrer">
                    <span class="contact-row__meta">LinkedIn</span>
                    <span class="contact-row__value">linkedin.com/in/jpromanonet</span>
                </a>
                <a class="contact-row" href="mailto:<?= e($site['email']) ?>">
                    <span class="contact-row__meta">Email</span>
                    <span class="contact-row__value"><?= e($site['email']) ?></span>
                </a>
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
