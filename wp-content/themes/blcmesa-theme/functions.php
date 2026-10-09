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
