<?php
/**
 * Plugin Name: My Custom Plugin
 * Description: Minimal plugin that provides an empty front page and enqueues plugin CSS/JS.
 * Version: 1.0.2
 * Author: Raphael Shawn Taurai
 * Text Domain: my-custom-plugin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue plugin CSS and JS files.
 * Files expected next to this PHP file: `customplugin.css` and `myplugin.js`.
 */
function mcp_enqueue_assets() {
    $base_url = plugin_dir_url( __FILE__ );

    // Styles (only enqueue if file exists)
    $css_path = plugin_dir_path( __FILE__ ) . 'customplugin.css';
    if ( file_exists( $css_path ) ) {
        wp_enqueue_style(
            'mcp-style',
            $base_url . 'customplugin.css',
            array(),
            filemtime( $css_path )
        );
    }

    // Scripts (load in footer if file exists)
    $js_path = plugin_dir_path( __FILE__ ) . 'myplugin.js';
    if ( file_exists( $js_path ) ) {
        wp_enqueue_script(
            'mcp-script',
            $base_url . 'myplugin.js',
            array( 'jquery' ),
            filemtime( $js_path ),
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'mcp_enqueue_assets' );


/**
 * Replace the front page content with an empty container.
 * This makes the site's front page effectively empty while allowing CSS/JS
 * to target the `#mcp-homepage` element.
 */
function mcp_frontpage_empty_content( $content ) {
    if ( is_front_page() && in_the_loop() && is_main_query() ) {
        return '<div id="mcp-homepage"></div>';
    }
    return $content;
}
add_filter( 'the_content', 'mcp_frontpage_empty_content', 20 );
