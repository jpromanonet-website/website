<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

$docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? APP_ROOT) ?: APP_ROOT;
$appRoot = realpath(APP_ROOT) ?: APP_ROOT;
$relative = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
$relative = rtrim($relative, '/');
define('BASE_URL', $relative === '' || $relative === false ? '' : $relative);

require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/medium.php';

$cmsBootstrapCandidates = [
    APP_ROOT . '/microCMS/bootstrap.php',
    dirname(APP_ROOT) . '/microCMS/bootstrap.php',
];
$cmsBootstrapped = false;
foreach ($cmsBootstrapCandidates as $cmsBootstrap) {
    if (!is_file($cmsBootstrap)) {
        continue;
    }
    try {
        require_once $cmsBootstrap;
        $cmsBootstrapped = class_exists(\MicroCMS\Content::class);
        break;
    } catch (Throwable $e) {
        $cmsBootstrapped = false;
        break;
    }
}

if ($cmsBootstrapped) {
    $cmsSettings = \MicroCMS\Content::settings();
    $site = [
        'name' => $cmsSettings['name'] ?? 'Juan P. Romano',
        'short' => $cmsSettings['short'] ?? 'JPR',
        'tagline' => $cmsSettings['tagline'] ?? '',
        'email' => $cmsSettings['email'] ?? '',
        'phone' => $cmsSettings['phone'] ?? '',
        'blog' => $cmsSettings['blog'] ?? '',
        'linkedin' => $cmsSettings['linkedin'] ?? '',
        'github' => $cmsSettings['github'] ?? '',
        'x' => $cmsSettings['x'] ?? '',
        'instagram' => $cmsSettings['instagram'] ?? '',
        'ga_id' => $cmsSettings['ga_id'] ?? '',
        'medium_feed' => $cmsSettings['medium_feed'] ?? '',
        'medium_user_id' => $cmsSettings['medium_user_id'] ?? '',
    ];
    $navItems = \MicroCMS\Content::navItems();
    $homeBlocks = \MicroCMS\Content::homeBlocks();
} else {
    $site = [
        'name' => 'Juan P. Romano',
        'short' => 'JPR',
        'tagline' => 'Engineering Manager, builder, and writer based in Buenos Aires.',
        'email' => 'contact@jpromano.net',
        'phone' => '',
        'blog' => 'https://jpromanonet.medium.com',
        'linkedin' => 'https://www.linkedin.com/in/jpromanonet/',
        'github' => 'https://github.com/jpromanonet',
        'x' => 'https://x.com/jpromanonet',
        'instagram' => 'https://instagram.com/jpromanonet',
        'ga_id' => 'G-73GRBEG00T',
        'medium_feed' => 'https://medium.com/feed/@jpromanonet',
        'medium_user_id' => '768cb0ffbcaf',
    ];

    $navItems = [
        ['label' => 'Portfolio', 'path' => '/portfolio/', 'key' => 'portfolio'],
        ['label' => 'Books', 'path' => '/books/', 'key' => 'books'],
        ['label' => 'Writing', 'path' => '/writing/', 'key' => 'writing'],
        ['label' => 'Ventures', 'path' => '/ventures/', 'key' => 'ventures'],
        ['label' => 'News', 'path' => '/news/', 'key' => 'news'],
        ['label' => 'Resumes', 'path' => '/resumes/', 'key' => 'resumes'],
        [
            'label' => 'Teaching',
            'key' => 'teaching',
            'children' => [
                ['label' => 'Learning IA', 'url' => 'https://learningiaforfree.vercel.app/'],
                ['label' => 'Learning to Code', 'url' => 'https://learningtocodeforfree.vercel.app/'],
            ],
        ],
    ];
    $homeBlocks = [];
}
