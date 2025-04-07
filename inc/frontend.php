<?php
if (!defined('ABSPATH')) {
    exit;
}

function alisa_chatbot_display() {
    $default_options = array(
        'name' => 'Alisa Chatbot',
        'chatbox_header' => 'Chat with us',
        'welcome' => 'Hey! I am Alisa, How can I help you?'
    );
    
    $options = get_option('alisa_chatbot_options', $default_options);
    $options = wp_parse_args($options, $default_options);
    
    // Sanitize values
    $chatbox_header = esc_html($options['chatbox_header']);
    $chatbot_name = esc_html($options['name']);
    $welcome_message = esc_html($options['welcome']);

    // Output the chat interface with proper structure
    ?>
    <div class="alisa-chat-icon">💬</div>
    <div class="alisa-overlay"></div>
    <div class="alisa-chat-window" data-bot-name="<?php echo $chatbot_name; ?>">
        <div class="alisa-chat-header">
            <?php echo $chatbox_header; ?>
            <span class="alisa-close-chat">&times;</span>
        </div>
        <div class="alisa-chat-body">
            <p class="alisa-bot-message"><?php echo $welcome_message; ?></p>
            <div class="alisa-user-info">
                <input type="text" id="alisa-user-name" placeholder="Your Name">
                <input type="email" id="alisa-user-email" placeholder="Your Email">
                <div class="alisa-button-group">
                    <button class="alisa-submit-user">Submit</button>
                    <button class="alisa-skip-user">Skip</button>
                </div>
            </div>
            <div class="alisa-chat-container" style="display: none;"></div>
        </div>
        <div class="alisa-chat-footer">
            <input type="text" id="alisa-chat-input" placeholder="Type your message..." disabled>
            <button id="alisa-send-btn" class="alisa-send-message" disabled>Send</button>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'alisa_chatbot_display');

function alisa_get_bot_response($user_input) {
    $training_data = get_option('alisa_chatbot_training_data', '');
    
    if (!$training_data) {
        return "I'm still learning, but I got your message!";
    }

    $qa_pairs = explode("\n", $training_data);
    foreach ($qa_pairs as $pair) {
        $qa = explode("|", $pair);
        if (count($qa) == 2) {
            list($question, $answer) = $qa;
            if (stripos(trim($question), trim($user_input)) !== false) {
                return trim($answer);
            }
        }
    }

    return "I'm still learning, but I got your message!";
}

function alisa_handle_chat_message() {
    check_ajax_referer('alisa_chat_nonce', 'nonce');

    if (!isset($_POST['message']) || !isset($_POST['session_id'])) {
        wp_send_json_error(['message' => 'Missing required fields']);
        return;
    }

    $user_input = sanitize_text_field($_POST['message']);
    $session_id = sanitize_text_field($_POST['session_id']);
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    
    $response = alisa_get_bot_response($user_input);

    global $wpdb;
    $result = $wpdb->insert(
        $wpdb->prefix . 'alisa_chatbot_interactions',
        array(
            'user_id' => $user_id,
            'session_id' => $session_id,
            'message' => $user_input,
            'response' => $response,
            'timestamp' => current_time('mysql')
        ),
        array('%d', '%s', '%s', '%s', '%s')
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Database error']);
        return;
    }

    wp_send_json_success([
        'response' => $response,
        'timestamp' => current_time('mysql')
    ]);
}
add_action('wp_ajax_alisa_chat_message', 'alisa_handle_chat_message');
add_action('wp_ajax_nopriv_alisa_chat_message', 'alisa_handle_chat_message');

function alisa_save_user_info() {
    check_ajax_referer('alisa_chat_nonce', 'nonce');

    if (!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['session_id'])) {
        wp_send_json_error(['message' => 'Missing required fields']);
        return;
    }

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $session_id = sanitize_text_field($_POST['session_id']);

    global $wpdb;
    $result = $wpdb->insert(
        $wpdb->prefix . 'alisa_chatbot_users',
        array(
            'name' => $name,
            'email' => $email,
            'session_id' => $session_id,
            'created_at' => current_time('mysql')
        ),
        array('%s', '%s', '%s', '%s')
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Database error']);
        return;
    }

    wp_send_json_success([
        'user_id' => $wpdb->insert_id,
        'message' => 'User information saved successfully'
    ]);
}
add_action('wp_ajax_alisa_save_user', 'alisa_save_user_info');
add_action('wp_ajax_nopriv_alisa_save_user', 'alisa_save_user_info');

function alisa_generate_dynamic_css() {
    $appearance = get_option('alisa_appearance_options', array());
    
    // Set default values
    $defaults = array(
        'font_family' => 'Arial, sans-serif',
        'font_size' => '14',
        'header_background' => '#0073aa',
        'header_text_color' => '#ffffff',
        'button_color' => '#0073aa',
        'chatbox_width' => '350',
        'chatbox_height' => '500',
        'user_message_color' => '#000000',
        'user_message_bg' => '#e3f2fd',
        'bot_message_color' => '#000000',
        'bot_message_bg' => '#f5f5f5'
    );

    // Merge with defaults
    $appearance = wp_parse_args($appearance, $defaults);

    // Generate CSS
    $css = "
        .alisa-chat-window {
            font-family: {$appearance['font_family']};
            font-size: {$appearance['font_size']}px;
            width: {$appearance['chatbox_width']}px;
            height: {$appearance['chatbox_height']}px;
        }
        
        .alisa-chat-header {
            background-color: {$appearance['header_background']};
            color: {$appearance['header_text_color']};
        }
        
        .alisa-chat-footer button,
        .alisa-submit-user,
        .alisa-chat-icon {
            background-color: {$appearance['button_color']};
            color: #ffffff;
        }
        
        .alisa-chat-body {
            height: calc({$appearance['chatbox_height']}px - 120px);
        }
        
        .alisa-user-message {
            background-color: {$appearance['user_message_bg']};
            color: {$appearance['user_message_color']};
        }
        
        .alisa-bot-message {
            background-color: {$appearance['bot_message_bg']};
            color: {$appearance['bot_message_color']};
        }
    ";

    // If custom send icon is set
    if (!empty($appearance['send_icon'])) {
        $css .= "
        .alisa-send-message {
            background-image: url('{$appearance['send_icon']}');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            width: 30px;
            height: 30px;
            text-indent: -9999px;
        }";
    }

    return $css;
}

function alisa_enqueue_dynamic_styles() {
    $custom_css = alisa_generate_dynamic_css();
    wp_add_inline_style('alisa-chatbot-style', $custom_css);
}
add_action('wp_enqueue_scripts', 'alisa_enqueue_dynamic_styles', 20);
?>

