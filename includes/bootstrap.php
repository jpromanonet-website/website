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
require_once APP_ROOT . '/includes/goodreads.php';

$cmsBootstrapCandidates = [
    APP_ROOT . '/microCMS/bootstrap.php',
    dirname(APP_ROOT) . '/microCMS/bootstrap.php',
];

$cmsBootstrapped = false;
$cmsError = null;
foreach ($cmsBootstrapCandidates as $cmsBootstrap) {
    if (!is_file($cmsBootstrap)) {
        continue;
    }
    try {
        require_once $cmsBootstrap;
        $cmsBootstrapped = class_exists(\MicroCMS\Content::class);
        break;
    } catch (Throwable $e) {
        $cmsError = $e;
        $cmsBootstrapped = false;
        break;
    }
}

if (!$cmsBootstrapped) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Site content requires microCMS + MySQL.\n";
    if ($cmsError instanceof Throwable) {
        echo 'Error: ' . $cmsError->getMessage() . "\n";
    } else {
        echo "microCMS bootstrap not found or failed to load.\n";
    }
    exit;
}

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
    'deviantart' => 'https://www.deviantart.com/jpromanonet',
    'ga_id' => $cmsSettings['ga_id'] ?? '',
    'medium_feed' => $cmsSettings['medium_feed'] ?? '',
    'medium_user_id' => $cmsSettings['medium_user_id'] ?? '',
];
$navItems = \MicroCMS\Content::navItems();
$homeBlocks = \MicroCMS\Content::homeBlocks();

// Goodreads "Books read" lives outside the CMS — inject into Hobbies nav only.
$readingNavItem = [
    'label' => 'Books read',
    'path' => '/reading/',
    'key' => 'reading',
];
$hobbiesInjected = false;
foreach ($navItems as &$navItem) {
    if (($navItem['key'] ?? '') !== 'hobbies') {
        continue;
    }
    $children = is_array($navItem['children'] ?? null) ? $navItem['children'] : [];
    $already = false;
    foreach ($children as $child) {
        if (($child['key'] ?? '') === 'reading') {
            $already = true;
            break;
        }
    }
    if (!$already) {
        $children[] = $readingNavItem;
        $navItem['children'] = $children;
    }
    $hobbiesInjected = true;
    break;
}
unset($navItem);
if (!$hobbiesInjected) {
    $navItems[] = [
        'label' => 'Hobbies',
        'key' => 'hobbies',
        'children' => [$readingNavItem],
    ];
}
