<?php
function alisa_admin_menu() {
    add_menu_page('Alisa Chatbot', 'Alisa Chatbot', 'manage_options', 'alisa-chatbot', 'alisa_admin_page');
    add_submenu_page('alisa-chatbot', 'Chat Interactions', 'Chat Interactions', 'manage_options', 'alisa-chatbot-interactions', 'alisa_chatbot_interactions_page');
    add_submenu_page('alisa-chatbot', 'Appearance', 'Appearance', 'manage_options', 'alisa-chatbot-appearance', 'alisa_appearance_page');
}
add_action('admin_menu', 'alisa_admin_menu');

function alisa_admin_page() {
    ?>
    <div class="wrap">
        <h2>Alisa Chatbot Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('alisa_chatbot_options_group');
            do_settings_sections('alisa-chatbot');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function alisa_register_settings() {
    register_setting('alisa_chatbot_options_group', 'alisa_chatbot_options', 'alisa_chatbot_options_validate');

    add_settings_section('alisa_main_section', 'Main Settings', 'alisa_section_text', 'alisa-chatbot');

    add_settings_field('alisa_chatbot_name', 'Chatbot Name', 'alisa_setting_name', 'alisa-chatbot', 'alisa_main_section');
    add_settings_field('alisa_chatbox_header', 'Chatbox Header', 'alisa_setting_chatbox_header', 'alisa-chatbot', 'alisa_main_section');
    add_settings_field('alisa_chatbot_welcome', 'Welcome Message', 'alisa_setting_welcome', 'alisa-chatbot', 'alisa_main_section');
}
add_action('admin_init', 'alisa_register_settings');

function alisa_section_text() {
    echo '<p>Main description of this section here.</p>';
}

function alisa_setting_name() {
    $options = get_option('alisa_chatbot_options', ['name' => 'Alisa Chatbot']); // Default value
    $name = isset($options['name']) ? $options['name'] : 'Alisa Chatbot';
    echo "<input id='alisa_chatbot_name' name='alisa_chatbot_options[name]' size='40' type='text' value='{$name}' />";
}

function alisa_setting_chatbox_header() {
    $options = get_option('alisa_chatbot_options', ['chatbox_header' => 'Chat with us']); // Default value
    $chatbox_header = isset($options['chatbox_header']) ? $options['chatbox_header'] : 'Chat with us';
    echo "<input id='alisa_chatbox_header' name='alisa_chatbot_options[chatbox_header]' size='40' type='text' value='{$chatbox_header}' />";
}

function alisa_setting_welcome() {
    $options = get_option('alisa_chatbot_options', ['welcome' => 'Hey! I am Alisa, How can I help you?']); // Default value
    $welcome = isset($options['welcome']) ? $options['welcome'] : 'Hey! I am Alisa, How can I help you?';
    echo "<input id='alisa_chatbot_welcome' name='alisa_chatbot_options[welcome]' size='60' type='text' value='{$welcome}' />";
}

function alisa_chatbot_options_validate($input) {
    $newinput['name'] = sanitize_text_field($input['name']);
    $newinput['chatbox_header'] = sanitize_text_field($input['chatbox_header']);
    $newinput['welcome'] = sanitize_text_field($input['welcome']);

    if (empty($newinput['name'])) {
        $newinput['name'] = 'Alisa Chatbot'; // Default name if empty
    }
    if (empty($newinput['chatbox_header'])) {
        $newinput['chatbox_header'] = 'Chat with us'; // Default chatbox header if empty
    }
    if (empty($newinput['welcome'])) {
        $newinput['welcome'] = "Hey! I am {$newinput['name']}, How can I help you?"; // Default message if empty
    }
    return $newinput;
}

// Register Train Your Chatbot Section
function alisa_register_train_section() {
    register_setting('alisa_chatbot_options_group', 'alisa_chatbot_training_data');

    add_settings_section('alisa_train_section', 'Train Your Chatbot', 'alisa_train_section_text', 'alisa-chatbot');

    add_settings_field('alisa_training_textarea', 'Training Data', 'alisa_training_textarea', 'alisa-chatbot', 'alisa_train_section');

    // Add file upload only for licensed users (you'll need to check their license status)
    if (get_option('alisa_chatbot_license')) {
        add_settings_field('alisa_training_upload', 'Upload Training File', 'alisa_training_upload', 'alisa-chatbot', 'alisa_train_section');
    }
}
add_action('admin_init', 'alisa_register_train_section');

function alisa_train_section_text() {
    echo '<p>Manually train the chatbot by adding Q&A pairs below. One question and answer per line.</p>';
}

function alisa_training_textarea() {
    $training_data = get_option('alisa_chatbot_training_data', '');
    echo "<textarea id='alisa_training_data' name='alisa_chatbot_training_data' rows='10' cols='60'>{$training_data}</textarea>";
}

function alisa_training_upload() {
    echo "<input type='file' id='alisa_training_file' name='alisa_training_file' accept='.txt,.csv'>";
}

function alisa_save_training_data($input) {
    return sanitize_textarea_field($input);
}

function alisa_handle_file_upload() {
    if (isset($_FILES['alisa_training_file']) && $_FILES['alisa_training_file']['error'] == 0) {
        $file = $_FILES['alisa_training_file']['tmp_name'];
        $content = file_get_contents($file);
        update_option('alisa_chatbot_training_data', $content);
    }
}
add_action('admin_post_alisa_upload_file', 'alisa_handle_file_upload');

function alisa_chatbot_interactions_page() {
    display_chat_interactions();
}

function display_chat_interactions() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'alisa_chatbot_interactions';
    $users_table = $wpdb->prefix . 'alisa_chatbot_users';

    // Modified query with proper NULL handling
    $interactions = $wpdb->get_results("
        SELECT 
            COALESCE(u.name, 'Anonymous') as user_name,
            COALESCE(u.email, 'No Email') as user_email,
            COALESCE(MIN(i.timestamp), CURRENT_TIMESTAMP) as first_message_time,
            i.session_id,
            GROUP_CONCAT(i.message) as messages,
            i.user_id
        FROM $table_name i
        LEFT JOIN $users_table u ON i.user_id = u.id
        WHERE i.session_id IS NOT NULL
        GROUP BY i.session_id, i.user_id
        ORDER BY first_message_time DESC
    ");

    if ($wpdb->last_error) {
        echo "<div class='error'><p>Database error: " . esc_html($wpdb->last_error) . "</p></div>";
        return;
    }

    echo "<div class='wrap'><h2>Chat Interactions</h2>";
    
    if (empty($interactions)) {
        echo "<p>No chat interactions found.</p>";
        return;
    }

    echo "<table class='wp-list-table widefat fixed striped'>";
    echo "<thead><tr>
            <th>User Name</th>
            <th>Email</th>
            <th>Time/Date</th>
            <th>Conversation</th>
          </tr></thead><tbody>";

    foreach ($interactions as $interaction) {
        // Ensure values are not null before output
        $user_name = !empty($interaction->user_name) ? $interaction->user_name : 'Anonymous';
        $user_email = !empty($interaction->user_email) ? $interaction->user_email : 'No Email';
        $timestamp = !empty($interaction->first_message_time) ? $interaction->first_message_time : '';
        $session_id = !empty($interaction->session_id) ? $interaction->session_id : '';

        if (!empty($session_id)) {
            echo "<tr>
                    <td>" . esc_html($user_name) . "</td>
                    <td>" . esc_html($user_email) . "</td>
                    <td>" . esc_html($timestamp) . "</td>
                    <td><a href='" . esc_url(admin_url("admin.php?page=view_conversation&session_id=" . urlencode($session_id))) . "' target='_blank'>View</a></td>
                  </tr>";
        }
    }

    echo "</tbody></table></div>";
}

function view_conversation_page() {
    // Verify session_id exists
    if (!isset($_GET['session_id']) || empty($_GET['session_id'])) {
        echo "<div class='wrap'><h2>Invalid Conversation</h2><p>No valid session ID provided.</p></div>";
        return;
    }

    global $wpdb;
    $session_id = sanitize_text_field($_GET['session_id']);
    $table_name = $wpdb->prefix . 'alisa_chatbot_interactions';
    $users_table = $wpdb->prefix . 'alisa_chatbot_users';

    // Improved query to fetch complete conversation
    $messages = $wpdb->get_results($wpdb->prepare("
        SELECT 
            i.message,
            i.response,
            i.timestamp,
            i.user_id,
            COALESCE(u.name, 'Anonymous') as user_name,
            COALESCE(u.email, 'No Email') as user_email
        FROM $table_name i
        LEFT JOIN $users_table u ON i.user_id = u.id
        WHERE i.session_id = %s 
        ORDER BY i.timestamp ASC
    ", $session_id));

    if (empty($messages)) {
        echo "<div class='wrap'><h2>No Messages Found</h2></div>";
        return;
    }

    // Enhanced CSS for better message display
    echo "<style>
        .chat-container { max-width: 800px; margin: 20px auto; }
        .chat-message { 
            margin: 10px 0; 
            padding: 15px; 
            border-radius: 10px; 
            position: relative;
        }
        .user-message { 
            background: #e3f2fd; 
            margin-right: 20%; 
            margin-left: 5%;
        }
        .bot-message { 
            background: #f5f5f5; 
            margin-left: 20%; 
            margin-right: 5%;
        }
        .message-time { 
            font-size: 0.8em; 
            color: #666; 
            position: absolute;
            bottom: 5px;
            right: 10px;
        }
        .message-content {
            margin-bottom: 15px;
        }
    </style>";

    // Display conversation header
    echo "<div class='wrap chat-container'>
        <h2>Conversation Details</h2>
        <div class='chat-info'>
            <p><strong>User:</strong> " . esc_html($messages[0]->user_name) . "</p>
            <p><strong>Email:</strong> " . esc_html($messages[0]->user_email) . "</p>
        </div>
        <div class='chat-messages' style='border:1px solid #ccc; padding:20px; background:#fff; margin-top:20px;'>";

    // Display messages and responses
    foreach ($messages as $message) {
        // User message
        if (!empty($message->message)) {
            echo "<div class='chat-message user-message'>
                    <div class='message-content'>
                        <strong>User:</strong> " . esc_html($message->message) . "
                    </div>
                    <div class='message-time'>" . esc_html($message->timestamp) . "</div>
                  </div>";
        }

        // Bot response
        if (!empty($message->response)) {
            echo "<div class='chat-message bot-message'>
                    <div class='message-content'>
                        <strong>Alisa:</strong> " . esc_html($message->response) . "
                    </div>
                    <div class='message-time'>" . esc_html($message->timestamp) . "</div>
                  </div>";
        }
    }

    echo "</div></div>";
}

function register_conversation_page() {
    add_submenu_page(
        null, // Hidden from main menu
        'View Conversation',
        'View Conversation',
        'manage_options',
        'view_conversation',
        'view_conversation_page'
    );
}
add_action('admin_menu', 'register_conversation_page');

// Add new function for appearance settings
function alisa_appearance_page() {
    ?>
    <div class="wrap">
        <h2>Chatbot Appearance Settings</h2>
        <form method="post" action="options.php" enctype="multipart/form-data">
            <?php
            settings_fields('alisa_appearance_options_group');
            do_settings_sections('alisa-chatbot-appearance');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function alisa_register_appearance_settings() {
    register_setting(
        'alisa_appearance_options_group',
        'alisa_appearance_options',
        'alisa_appearance_options_validate'
    );

    // General Appearance Section
    add_settings_section(
        'alisa_appearance_general',
        'General Appearance',
        'alisa_appearance_general_text',
        'alisa-chatbot-appearance'
    );

    // Font Settings
    add_settings_field(
        'chat_font_family',
        'Font Family',
        'alisa_font_family_callback',
        'alisa-chatbot-appearance',
        'alisa_appearance_general'
    );

    add_settings_field(
        'chat_font_size',
        'Font Size',
        'alisa_font_size_callback',
        'alisa-chatbot-appearance',
        'alisa_appearance_general'
    );

    // Color Settings
    add_settings_field(
        'header_background',
        'Header Background Color',
        'alisa_color_picker_callback',
        'alisa-chatbot-appearance',
        'alisa_appearance_general',
        ['option_name' => 'header_background']
    );

    add_settings_field(
        'header_text_color',
        'Header Text Color',
        'alisa_color_picker_callback',
        'alisa-chatbot-appearance',
        'alisa_appearance_general',
        ['option_name' => 'header_text_color']
    );

    add_settings_field(
        'button_color',
        'Button Color',
        'alisa_color_picker_callback',
        'alisa-chatbot-appearance',
        'alisa_appearance_general',
        ['option_name' => 'button_color']
    );

    // Size Settings
    add_settings_field(
        'chatbox_width',
        'Chatbox Width (px)',
        'alisa_number_field_callback',
        'alisa-chatbot-appearance',
        'alisa_appearance_general',
        ['option_name' => 'chatbox_width', 'min' => 300, 'max' => 500]
    );

    add_settings_field(
        'chatbox_height',
        'Chatbox Height (px)',
        'alisa_number_field_callback',
        'alisa-chatbot-appearance',
        'alisa_appearance_general',
        ['option_name' => 'chatbox_height', 'min' => 400, 'max' => 700]
    );

    // Send Icon Upload
    add_settings_field(
        'send_icon',
        'Send Button Icon',
        'alisa_icon_upload_callback',
        'alisa-chatbot-appearance',
        'alisa_appearance_general'
    );

    // User Message Settings
    add_settings_field(
        'user_message_color',
        'User Message Text Color',
        'alisa_color_picker_callback',
        'alisa-chatbot-appearance',
        'alisa_appearance_general',
        ['option_name' => 'user_message_color']
    );

    add_settings_field(
        'user_message_bg',
        'User Message Background',
        'alisa_color_picker_callback',
        'alisa-chatbot-appearance',
        'alisa_appearance_general',
        ['option_name' => 'user_message_bg']
    );

    // Bot Message Settings
    add_settings_field(
        'bot_message_color',
        'Bot Message Text Color',
        'alisa_color_picker_callback',
        'alisa-chatbot-appearance',
        'alisa_appearance_general',
        ['option_name' => 'bot_message_color']
    );

    add_settings_field(
        'bot_message_bg',
        'Bot Message Background',
        'alisa_color_picker_callback',
        'alisa-chatbot-appearance',
        'alisa_appearance_general',
        ['option_name' => 'bot_message_bg']
    );
}
add_action('admin_init', 'alisa_register_appearance_settings');

// Callback functions for settings fields
function alisa_appearance_general_text() {
    echo '<p>Customize the appearance of your chatbot</p>';
}

function alisa_font_family_callback() {
    $options = get_option('alisa_appearance_options');
    $fonts = array(
        'Arial, sans-serif' => 'Arial',
        'Helvetica, sans-serif' => 'Helvetica',
        'Georgia, serif' => 'Georgia',
        'Tahoma, sans-serif' => 'Tahoma',
        'Verdana, sans-serif' => 'Verdana',
        'Times New Roman, serif' => 'Times New Roman'
    );
    
    echo '<select id="chat_font_family" name="alisa_appearance_options[font_family]">';
    foreach ($fonts as $value => $label) {
        $selected = isset($options['font_family']) && $options['font_family'] === $value ? 'selected' : '';
        echo "<option value='$value' $selected>$label</option>";
    }
    echo '</select>';
}

function alisa_font_size_callback() {
    $options = get_option('alisa_appearance_options');
    $size = isset($options['font_size']) ? $options['font_size'] : '14';
    echo '<input type="number" id="chat_font_size" name="alisa_appearance_options[font_size]" value="' . esc_attr($size) . '" min="12" max="20"> px';
}

function alisa_color_picker_callback($args) {
    $options = get_option('alisa_appearance_options');
    $value = isset($options[$args['option_name']]) ? $options[$args['option_name']] : '';
    echo '<input type="color" id="' . esc_attr($args['option_name']) . '" name="alisa_appearance_options[' . esc_attr($args['option_name']) . ']" value="' . esc_attr($value) . '">';
}

function alisa_number_field_callback($args) {
    $options = get_option('alisa_appearance_options');
    $value = isset($options[$args['option_name']]) ? $options[$args['option_name']] : '';
    echo '<input type="number" id="' . esc_attr($args['option_name']) . '" 
          name="alisa_appearance_options[' . esc_attr($args['option_name']) . ']" 
          value="' . esc_attr($value) . '"
          min="' . esc_attr($args['min']) . '"
          max="' . esc_attr($args['max']) . '"> px';
}

function alisa_icon_upload_callback() {
    $options = get_option('alisa_appearance_options');
    $icon_url = isset($options['send_icon']) ? $options['send_icon'] : '';
    ?>
    <div class="icon-upload-container">
        <input type="hidden" name="alisa_appearance_options[send_icon]" id="send_icon_url" value="<?php echo esc_attr($icon_url); ?>">
        <img src="<?php echo esc_url($icon_url); ?>" id="send_icon_preview" style="max-width: 50px; <?php echo empty($icon_url) ? 'display: none;' : ''; ?>">
        <button type="button" class="button" id="upload_send_icon">Upload Icon</button>
        <button type="button" class="button" id="remove_send_icon" <?php echo empty($icon_url) ? 'style="display: none;"' : ''; ?>>Remove Icon</button>
    </div>
    <?php
}

// Add validation function
function alisa_appearance_options_validate($input) {
    $valid = array();
    
    $valid['font_family'] = sanitize_text_field($input['font_family']);
    $valid['font_size'] = absint($input['font_size']);
    $valid['header_background'] = sanitize_hex_color($input['header_background']);
    $valid['header_text_color'] = sanitize_hex_color($input['header_text_color']);
    $valid['button_color'] = sanitize_hex_color($input['button_color']);
    $valid['chatbox_width'] = absint($input['chatbox_width']);
    $valid['chatbox_height'] = absint($input['chatbox_height']);
    $valid['send_icon'] = esc_url_raw($input['send_icon']);
    $valid['user_message_color'] = sanitize_hex_color($input['user_message_color']);
    $valid['user_message_bg'] = sanitize_hex_color($input['user_message_bg']);
    $valid['bot_message_color'] = sanitize_hex_color($input['bot_message_color']);
    $valid['bot_message_bg'] = sanitize_hex_color($input['bot_message_bg']);

    return $valid;
}
?>
