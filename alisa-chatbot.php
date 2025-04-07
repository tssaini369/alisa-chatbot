<?php
/**
 * Plugin Name: Alisa - AI Chatbot for WordPress
 * Plugin URI: https://github.com/tssaini369/alisa-chatbot
 * Description: A customizable AI chatbot plugin with admin panel training, conversation storage, and licensing.
 * Version: 1.0.0
 * Author: TeeJay
 * Author URI: https://github.com/tssaini369
 * License: Proprietary
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define constants
define('ALISA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ALISA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ALISA_DEFAULT_NAME', 'Alisa');
define('ALISA_VERSION', '1.0.0');

// Include necessary files
require_once ALISA_PLUGIN_DIR . 'inc/database.php';
require_once ALISA_PLUGIN_DIR . 'inc/admin.php';
require_once ALISA_PLUGIN_DIR . 'inc/frontend.php';
require_once ALISA_PLUGIN_DIR . 'inc/api.php';
require_once ALISA_PLUGIN_DIR . 'inc/ajax-handlers.php';

// Activation hook
function alisa_activate() {
    // Ensure WordPress database functions are loaded
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // Start output buffering
    ob_start();
    
    // Create database tables
    alisa_create_database();
    
    // Clean the output buffer
    ob_end_clean();
    
    // Set default options
    $default_options = array(
        'name' => 'Alisa Chatbot',
        'chatbox_header' => 'Chat with us',
        'welcome' => 'Hey! I am Alisa, How can I help you?'
    );
    
    add_option('alisa_chatbot_options', $default_options);
}
register_activation_hook(__FILE__, 'alisa_activate');

// Enqueue scripts and styles
function alisa_enqueue_scripts() {
    // Enqueue main stylesheet
    wp_enqueue_style(
        'alisa-chatbot-style',
        plugins_url('assets/css/style.css', __FILE__),
        array(),
        ALISA_VERSION
    );
    
    // Enqueue main script
    wp_enqueue_script(
        'alisa-chatbot',
        plugins_url('assets/js/chatbot.js', __FILE__),
        array('jquery'),
        ALISA_VERSION,
        true
    );

    wp_localize_script('alisa-chatbot', 'alisa_ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('alisa_chat_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'alisa_enqueue_scripts');

function alisa_admin_enqueue_scripts($hook) {
    // Only proceed if $hook is a valid string
    if (!is_string($hook)) {
        return;
    }

    // Define allowed plugin pages
    $plugin_pages = array(
        'toplevel_page_alisa-chatbot',
        'alisa-chatbot_page_alisa-chatbot-interactions',
        'alisa-chatbot_page_alisa-chatbot-appearance'
    );

    // Only proceed if we're on our plugin pages
    if (!in_array($hook, $plugin_pages, true)) {
        return;
    }

    // Enqueue required scripts and styles
    wp_enqueue_media();
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    // Use the plugin constant for URL instead of plugins_url()
    $admin_js_url = ALISA_PLUGIN_URL . 'assets/js/admin.js';
    
    // Enqueue admin script
    wp_enqueue_script(
        'alisa-admin-js',
        $admin_js_url,
        array('jquery', 'wp-color-picker'),
        ALISA_VERSION,
        true
    );

    // Add debug information
    if (defined('WP_DEBUG') && WP_DEBUG) {
        wp_add_inline_script(
            'alisa-admin-js',
            sprintf('console.log("Alisa admin scripts loaded on: %s");', esc_js($hook))
        );
    }
}
add_action('admin_enqueue_scripts', 'alisa_admin_enqueue_scripts');
?>
