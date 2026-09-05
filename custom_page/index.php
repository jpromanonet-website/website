<?php
declare(strict_types=1);

/**
 * Front controller for custom CMS pages.
 * Pretty /{slug}/ folders cannot be created by PHP on this host (permissions),
 * so custom pages are served from /custom_page/?slug=…
 */

require_once dirname(__DIR__) . '/includes/bootstrap.php';

use MicroCMS\Content;

$slug = strtolower(trim((string) ($_GET['slug'] ?? '')));
$slug = preg_replace('/[^a-z0-9-]/', '', $slug) ?? '';

if ($slug === '') {
    http_response_code(404);
    echo 'Page not found';
    exit;
}

$page = Content::pageBySlug($slug);
if (!$page || (int) $page['is_system'] === 1) {
    http_response_code(404);
    echo 'Page not found';
    exit;
}

$pageSlug = $slug;
require dirname(__DIR__) . '/microCMS/public/catalog.php';
