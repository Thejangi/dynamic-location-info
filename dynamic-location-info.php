<?php

/**
 * Plugin Name: Dynamic Location Info
 * Description: Dynamically loads contact info, GHL forms, and social profiles by location.
 * Version: 1.18.0
 * Author: Helani Thejangi
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include loader and shortcodes
require_once plugin_dir_path(__FILE__) . 'includes/loader.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcode.php';

// Enqueue plugin styles
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'dli-social-icons',
        plugin_dir_url(__FILE__) . 'assets/style.css',
        [],
        '1.18.0'
    );
});
