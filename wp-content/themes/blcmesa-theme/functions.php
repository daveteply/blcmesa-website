<?php
/**
 * BLC Mesa Theme functions and definitions
 *
 * @package blcmesa
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function blcmesa_setup() {
    // Add default posts and comments RSS feed links to head.
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the document title.
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails on posts and pages.
    add_theme_support( 'post-thumbnails' );

    // Enable support for full site editing and block template parts.
    add_theme_support( 'block-template-parts' );

    // Enable responsive embedded content (YouTube, Vimeo, etc.).
    add_theme_support( 'responsive-embeds' );

    // Add support for editor styles.
    add_theme_support( 'editor-styles' );
    add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'blcmesa_setup' );

/**
 * Inline pre-render script in head to prevent Flash of Unstyled Theme (FOUT).
 * Resolves localStorage or OS dark mode before browser paints the first element.
 */
function blcmesa_theme_head_script() {
    ?>
    <script>
    (function() {
        try {
            var pref = localStorage.getItem('blc-color-theme') || 'system';
            var isDark = pref === 'dark' || (pref === 'system' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
            var root = document.documentElement;
            root.setAttribute('data-theme', isDark ? 'dark' : 'light');
            root.setAttribute('data-theme-pref', pref);
        } catch (e) {}
    })();
    </script>
    <?php
}
add_action( 'wp_head', 'blcmesa_theme_head_script', 1 );

/**
 * Enqueue scripts and styles.
 */
function blcmesa_scripts() {
    // Enqueue theme main stylesheet (includes self-hosted fonts)
    wp_enqueue_style(
        'blcmesa-theme-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get( 'Version' )
    );

    // Enqueue mobile navigation helper script
    wp_enqueue_script(
        'blcmesa-navigation',
        get_template_directory_uri() . '/assets/js/navigation.js',
        array(),
        wp_get_theme()->get( 'Version' ),
        true
    );

    // Enqueue color theme mode toggle script (Light / Dark / System)
    wp_enqueue_script(
        'blcmesa-theme-toggle',
        get_template_directory_uri() . '/assets/js/theme-toggle.js',
        array(),
        wp_get_theme()->get( 'Version' ),
        true
    );

    // Enqueue smooth Material-style accordion animations
    wp_enqueue_script(
        'blcmesa-accordion',
        get_template_directory_uri() . '/assets/js/accordion.js',
        array(),
        wp_get_theme()->get( 'Version' ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'blcmesa_scripts' );


/**
 * Register custom block pattern categories for Bethlehem Lutheran Church.
 */
function blcmesa_register_pattern_categories() {
    register_block_pattern_category(
        'blcmesa-sermons',
        array(
            'label'       => __( 'BLC Sermons & Media', 'blcmesa' ),
            'description' => __( 'Templates and blocks for weekly church sermons, video embeds, and audio bulletins.', 'blcmesa' ),
        )
    );

    register_block_pattern_category(
        'blcmesa-church',
        array(
            'label'       => __( 'BLC Church Layouts', 'blcmesa' ),
            'description' => __( 'Hero banners, service times, and ministry cards for Bethlehem Lutheran Church.', 'blcmesa' ),
        )
    );
}
add_action( 'init', 'blcmesa_register_pattern_categories' );
