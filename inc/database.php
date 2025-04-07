<?php
if (!defined('ABSPATH')) {
    exit;
}

function alisa_create_database() {
    global $wpdb;
    
    try {
        if (!function_exists('dbDelta')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }

        $charset_collate = $wpdb->get_charset_collate();

        // Create users table with NOT NULL constraints and proper defaults
        $table_name = $wpdb->prefix . 'alisa_chatbot_users';
        $sql_users = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL DEFAULT '',
            email VARCHAR(100) NOT NULL DEFAULT '',
            session_id VARCHAR(255) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id)
        ) $charset_collate;";

        dbDelta($sql_users);

        // Create interactions table with NOT NULL constraints and proper defaults
        $table_name_interactions = $wpdb->prefix . 'alisa_chatbot_interactions';
        $sql_interactions = "CREATE TABLE IF NOT EXISTS $table_name_interactions (
            id INT NOT NULL AUTO_INCREMENT,
            user_id INT NOT NULL DEFAULT 0,
            session_id VARCHAR(255) NOT NULL DEFAULT '',
            message TEXT NOT NULL,
            response TEXT NOT NULL,
            timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        dbDelta($sql_interactions);

        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }

        return true;

    } catch (Exception $e) {
        error_log('Alisa Database Creation Error: ' . $e->getMessage());
        return false;
    }
}

function alisa_update_database() {
    global $wpdb;
    
    try {
        // Check if tables exist before trying to modify them
        $users_table = $wpdb->prefix . 'alisa_chatbot_users';
        $interactions_table = $wpdb->prefix . 'alisa_chatbot_interactions';

        // Check if tables exist
        $users_exists = $wpdb->get_var("SHOW TABLES LIKE '$users_table'") === $users_table;
        $interactions_exists = $wpdb->get_var("SHOW TABLES LIKE '$interactions_table'") === $interactions_table;

        if (!$users_exists || !$interactions_exists) {
            alisa_create_database();
            return;
        }

        // Add session_id column to interactions table if it doesn't exist
        if ($interactions_exists) {
            $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $interactions_table LIKE 'session_id'");
            if (empty($column_exists)) {
                $wpdb->query("ALTER TABLE $interactions_table ADD session_id VARCHAR(255) NOT NULL AFTER user_id");
            }
        }

    } catch (Exception $e) {
        error_log('Alisa Database Update Error: ' . $e->getMessage());
    }
}

// Run database creation when the plugin is activated
register_activation_hook(__FILE__, 'alisa_create_database');

// Run database update when the plugin is loaded
add_action('plugins_loaded', 'alisa_update_database');
?>
