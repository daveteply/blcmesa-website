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

    // Add support for editor styles (enqueued directly instead of CSS @import chains).
    add_theme_support( 'editor-styles' );
    add_editor_style(
        array(
            'assets/css/base/fonts.css',
            'assets/css/base/variables.css',
            'assets/css/base/typography.css',
            'assets/css/components/navigation.css',
            'assets/css/components/buttons.css',
            'assets/css/components/cards.css',
            'assets/css/components/footer.css',
            'assets/css/components/theme-toggle.css',
            'assets/css/home.css',
            'assets/css/pages/page-about.css',
            'assets/css/pages/page-ministries.css',
            'assets/css/pages/page-sermons.css',
            'assets/css/pages/page-prayers.css',
            'assets/css/pages/page-contact.css',
            'assets/css/pages/page-giving.css',
        )
    );
}
add_action( 'after_setup_theme', 'blcmesa_setup' );

/**
 * Emit <meta name="color-scheme">, minimal FOUC-prevention script, and critical
 * resource preloads early in <head>.
 */
function blcmesa_theme_head_script() {
    $theme_uri = get_template_directory_uri();
    ?>
    <meta name="color-scheme" content="light dark">
    <script>
    (function() {
        var stored = null;
        try {
            var val = localStorage.getItem('color-scheme');
            if (val === 'light' || val === 'dark') {
                stored = val;
            } else {
                var legacy = localStorage.getItem('blc-color-theme');
                if (legacy === 'light' || legacy === 'dark') {
                    stored = legacy;
                    localStorage.setItem('color-scheme', legacy);
                }
                if (legacy !== null) {
                    localStorage.removeItem('blc-color-theme');
                }
            }
        } catch (e) {}
        var meta = document.querySelector('meta[name="color-scheme"]');
        if (meta) {
            meta.content = stored || 'light dark';
        }
        var isDark = stored === 'dark' || (!stored && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
    })();
    </script>
    <link rel="preload" href="<?php echo esc_url( $theme_uri . '/assets/fonts/cinzel.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo esc_url( $theme_uri . '/assets/fonts/montserrat-400.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
    <?php if ( is_front_page() ) : ?>
    <link rel="preload" as="image" href="<?php echo esc_url( $theme_uri . '/assets/images/blc-mesa-hero-dec-2026.jpg' ); ?>" fetchpriority="high" type="image/jpeg">
    <?php endif; ?>
    <?php
}
add_action( 'wp_head', 'blcmesa_theme_head_script', 1 );

/**
 * Centralized URL Redirects Handler
 */
require_once get_template_directory() . '/inc/redirects.php';

/**
 * Contact Form Submission Handler
 */
require_once get_template_directory() . '/inc/contact-handler.php';

/**
 * Enqueue scripts and styles.
 */
function blcmesa_scripts() {
    $theme_version = wp_get_theme()->get( 'Version' );
    $theme_uri     = get_template_directory_uri();

    // Enqueue base, component, and shared page stylesheets directly (avoids @import waterfalls
    // and ensures versioned cache-busting behind Cloudways Varnish/Breeze).
    $shared_styles = array(
        'blcmesa-fonts'       => '/assets/css/base/fonts.css',
        'blcmesa-variables'   => '/assets/css/base/variables.css',
        'blcmesa-typography'  => '/assets/css/base/typography.css',
        'blcmesa-navigation'  => '/assets/css/components/navigation.css',
        'blcmesa-buttons'     => '/assets/css/components/buttons.css',
        'blcmesa-cards'       => '/assets/css/components/cards.css',
        'blcmesa-footer'      => '/assets/css/components/footer.css',
        'blcmesa-theme-toggle'=> '/assets/css/components/theme-toggle.css',
        'blcmesa-page-about'  => '/assets/css/pages/page-about.css',
        'blcmesa-page-ministries' => '/assets/css/pages/page-ministries.css',
        'blcmesa-page-sermons'    => '/assets/css/pages/page-sermons.css',
        'blcmesa-page-prayers'    => '/assets/css/pages/page-prayers.css',
    );

    foreach ( $shared_styles as $handle => $relative_path ) {
        wp_enqueue_style(
            $handle,
            $theme_uri . $relative_path,
            array(),
            $theme_version
        );
    }

    if ( is_front_page() ) {
        wp_enqueue_style(
            'blcmesa-home',
            $theme_uri . '/assets/css/home.css',
            array( 'blcmesa-variables' ),
            $theme_version
        );
    }

    if ( is_page( 'contact' ) ) {
        wp_enqueue_style(
            'blcmesa-page-contact',
            $theme_uri . '/assets/css/pages/page-contact.css',
            array( 'blcmesa-variables' ),
            $theme_version
        );
    }

    if ( is_page( 'giving' ) ) {
        wp_enqueue_style(
            'blcmesa-page-giving',
            $theme_uri . '/assets/css/pages/page-giving.css',
            array( 'blcmesa-variables' ),
            $theme_version
        );
    }

    $deferred_args = array(
        'in_footer' => true,
        'strategy'  => 'defer',
    );

    // Enqueue mobile navigation helper script
    wp_enqueue_script(
        'blcmesa-navigation',
        $theme_uri . '/assets/js/navigation.js',
        array(),
        $theme_version,
        $deferred_args
    );

    // Enqueue color theme mode toggle script (Smart 2-state Light / Dark with OS sync)
    wp_enqueue_script(
        'blcmesa-theme-toggle',
        $theme_uri . '/assets/js/theme-toggle.js',
        array(),
        $theme_version,
        $deferred_args
    );

    // Enqueue smooth accordion fallback (exits early where CSS interpolate-size is supported)
    wp_enqueue_script(
        'blcmesa-accordion',
        $theme_uri . '/assets/js/accordion.js',
        array(),
        $theme_version,
        $deferred_args
    );

    // Enqueue contact form routing on contact page
    if ( is_page( 'contact' ) ) {
        wp_enqueue_script(
            'blcmesa-contact',
            $theme_uri . '/assets/js/contact.js',
            array(),
            $theme_version,
            $deferred_args
        );

        wp_localize_script(
            'blcmesa-contact',
            'blcContactData',
            array(
                'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
                'nonce'           => wp_create_nonce( 'blc_contact_nonce' ),
                'isLocal'         => ( isset( $_SERVER['HTTP_HOST'] ) && ( str_contains( $_SERVER['HTTP_HOST'], 'localhost' ) || str_contains( $_SERVER['HTTP_HOST'], '127.0.0.1' ) ) ),
                'validRecipients' => array_keys( blcmesa_get_contact_recipient_map() ),
            )
        );
    }
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

