<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

$docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? APP_ROOT) ?: APP_ROOT;
$appRoot = realpath(APP_ROOT) ?: APP_ROOT;
$relative = str_replace('\\', '/', substr($appRoot, strlen($docRoot)));
$relative = rtrim($relative, '/');
define('BASE_URL', $relative === '' || $relative === false ? '' : $relative);

require_once APP_ROOT . '/includes/helpers.php';

$site = [
    'name' => 'Juan P. Romano',
    'short' => 'JPR',
    'tagline' => 'Engineering Manager, builder, and writer based in Buenos Aires.',
    'email' => 'contact@jpromano.net',
    'blog' => 'https://jpromanonet.medium.com',
    'linkedin' => 'https://www.linkedin.com/in/jpromanonet/',
    'github' => 'https://github.com/jpromanonet',
    'x' => 'https://x.com/jpromanonet',
    'instagram' => 'https://instagram.com/jpromanonet',
    'ga_id' => 'G-73GRBEG00T',
];

$navItems = [
    ['label' => 'Home', 'path' => '/', 'key' => 'home'],
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
