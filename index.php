<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = $site['name'];
$pageDescription = 'Juan P. Romano — Engineering Manager at Hybrid Bee Technology, Packt author, and polyglot software engineer based in Buenos Aires.';
$activeNav = 'home';

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

        <section class="section reveal" id="explore">
            <h2 class="section-heading">Explore</h2>
            <p class="section-lead">Everything lives in one place now — projects, writing, ventures, and more.</p>
            <div class="catalog-grid">
                <?php
                $explore = [
                    ['Portfolio', 'Shipped products, experiments, and open source.', '/portfolio/'],
                    ['Books', 'Published and upcoming titles.', '/books/'],
                    ['Writing', 'Articles across freeCodeCamp, ENE, and more.', '/writing/'],
                    ['Ventures', 'Companies and products I’m building.', '/ventures/'],
                    ['News', 'Press and media mentions.', '/news/'],
                    ['Resumes', 'Downloadable CVs in Spanish and English.', '/resumes/'],
                ];
                foreach ($explore as [$title, $brief, $path]):
                ?>
                    <a class="catalog-item" href="<?= e(url($path)) ?>" style="text-decoration:none;color:inherit;">
                        <div class="catalog-item__body">
                            <span class="catalog-item__meta">Section</span>
                            <h3 class="catalog-item__title"><?= e($title) ?></h3>
                            <p class="catalog-item__brief"><?= e($brief) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
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
