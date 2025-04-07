<?php
if (!defined('ABSPATH')) {
    exit;
}

function alisa_save_user() {
    try {
        // Verify nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'alisa_chat_nonce')) {
            throw new Exception('Invalid security token');
        }

        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $session_id = sanitize_text_field($_POST['session_id']);

        if (empty($name) || empty($email) || empty($session_id)) {
            throw new Exception('Missing required fields');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'alisa_chatbot_users';

        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'session_id' => $session_id,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s')
        );

        if ($result === false) {
            throw new Exception($wpdb->last_error);
        }

        wp_send_json_success(array(
            'user_id' => $wpdb->insert_id,
            'message' => 'User information saved successfully'
        ));

    } catch (Exception $e) {
        error_log('Alisa Chatbot Error: ' . $e->getMessage());
        wp_send_json_error(array(
            'message' => 'Error: ' . $e->getMessage()
        ));
    }
    
    wp_die();
}
add_action('wp_ajax_alisa_save_user', 'alisa_save_user');
add_action('wp_ajax_nopriv_alisa_save_user', 'alisa_save_user');