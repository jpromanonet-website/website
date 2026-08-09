<?php
declare(strict_types=1);

/** @var array $site */
/** @var array $navItems */
/** @var string $pageTitle */
/** @var string $pageDescription */
/** @var string $activeNav */

$pageTitle = $pageTitle ?? $site['name'];
$pageDescription = $pageDescription ?? $site['tagline'];
$activeNav = $activeNav ?? '';
$fullTitle = $pageTitle === $site['name']
    ? $site['name'] . ' — Engineering Manager & Builder'
    : $pageTitle . ' — ' . $site['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="<?= e($pageDescription) ?>" />
    <meta name="author" content="<?= e($site['name']) ?>" />
    <meta name="theme-color" content="#1a4f8b" />
    <title><?= e($fullTitle) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= e(asset('img/favicon.ico')) ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>" />
    <?php if (!empty($site['ga_id'])): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($site['ga_id']) ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= e($site['ga_id']) ?>');
    </script>
    <?php endif; ?>
</head>
<body data-theme="light">
    <a class="skip-link" href="#main">Skip to content</a>

    <header class="site-header" id="site-header">
        <div class="site-header__bar">
            <div class="container site-header__inner">
                <a class="brand" href="<?= e(url('/')) ?>">
                    <span class="brand__mark" aria-hidden="true">/</span>
                    <span class="brand__text"><?= e($site['short']) ?></span>
                    <span class="brand__name"><?= e($site['name']) ?></span>
                </a>

                <button
                    type="button"
                    class="nav-toggle"
                    id="nav-toggle"
                    aria-label="Toggle menu"
                    aria-expanded="false"
                    aria-controls="site-nav"
                >
                    <span></span><span></span><span></span>
                </button>

                <nav class="site-nav" id="site-nav" aria-label="Primary">
                    <?php foreach ($navItems as $item): ?>
                        <?php if (!empty($item['children'])): ?>
                            <div class="nav-item nav-item--dropdown">
                                <button
                                    type="button"
                                    class="nav-link nav-link--button<?= $activeNav === $item['key'] ? ' is-active' : '' ?>"
                                    aria-expanded="false"
                                    aria-haspopup="true"
                                >
                                    <?= e($item['label']) ?>
                                    <span class="nav-chevron" aria-hidden="true"></span>
                                </button>
                                <div class="nav-menu" role="menu">
                                    <?php foreach ($item['children'] as $child): ?>
                                        <a
                                            class="nav-menu__link"
                                            role="menuitem"
                                            href="<?= e($child['url']) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        ><?= e($child['label']) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <a
                                class="nav-link<?= $activeNav === $item['key'] ? ' is-active' : '' ?>"
                                href="<?= e(url($item['path'])) ?>"
                            ><?= e($item['label']) ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>

                <div class="site-header__actions">
                    <a class="btn btn--ghost" href="<?= e($site['blog']) ?>" target="_blank" rel="noopener noreferrer">Blog</a>
                    <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Switch to dark theme">
                        <svg class="theme-toggle__moon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M21 14.3A8.5 8.5 0 0 1 9.7 3 7 7 0 1 0 21 14.3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                        <svg class="theme-toggle__sun" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M12 2v2.2M12 19.8V22M4.2 4.2l1.6 1.6M18.2 18.2l1.6 1.6M2 12h2.2M19.8 12H22M4.2 19.8l1.6-1.6M18.2 5.8l1.6-1.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>
