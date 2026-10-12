<?php

/**
 * Centralized URL Redirection Map & Handler for Bethlehem Lutheran Church
 *
 * @package blcmesa
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Returns the centralized array of legacy slugs to new site routes.
 *
 * Keys represent request path slugs (trimmed of slashes).
 * Values represent internal site destinations (relative or absolute).
 *
 * @return array<string, string>
 */
function blcmesa_get_redirect_map()
{
    return array(
        // Legacy Sermon & Service URLs
        'lutheran-church-services-mesa' => '/sermons/',

        // Legacy News & Info URLs
        'prayers-we-use'                => '/prayers-we-use/',
        'faqs'                          => '/about/#faq',
        'faith-community-nurse'         => '/ministries/#fellowship-care',
        'the-nurses-corner'             => '/ministries/#fellowship-care',
        'harvest-garden'                => '/ministries/#community-outreach',
        'youth-groups'                  => '/ministries/#children-youth',
        'news-info'                     => '/about/',
        'newsletters'                   => '/events/',
        'contact-bethlehem-lutheran-church' => '/contact/',
    );
}

/**
 * Executes 301 redirects for matched legacy URLs.
 */
function blcmesa_legacy_redirects()
{
    $request_path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    $redirect_map = blcmesa_get_redirect_map();

    if (isset($redirect_map[$request_path])) {
        $destination = $redirect_map[$request_path];

        // Avoid redirect loops if destination matches current request
        if (trim($destination, '/') === $request_path) {
            return;
        }

        wp_safe_redirect(home_url($destination), 301);
        exit;
    }
}
add_action('template_redirect', 'blcmesa_legacy_redirects');
