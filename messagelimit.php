<?php

// Creates table to store message limits and IP
function create_message_limits_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'message_limits';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        ip_address VARCHAR(100) NOT NULL,
        message_limit INT(11) NOT NULL,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'create_message_limits_table');

// Stores message limit
function store_message_limit() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'message_limits';

    // Retrieve the user's IP address
    $user_ip = $_SERVER['REMOTE_ADDR'];

    // Check if the IP address already exists in the table
    $existing_ip = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ip_address = %s", $user_ip));

    if (!$existing_ip) {
        // IP address does not exist, insert a new record with the initial message limit
        $insert_result = $wpdb->insert(
            $table_name,
            array(
                'ip_address' => $user_ip,
                'message_limit' => 50, // Set the initial message limit here
            ),
            array('%s', '%d')
        );

        if ($insert_result === false) {
            error_log('Failed to store message limit. Error: ' . $wpdb->last_error);
        }
    }
}

add_action('init', 'store_message_limit');

// Get message limit
function get_message_limit() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'message_limits';

    // Retrieve the user's IP address
    $user_ip = $_SERVER['REMOTE_ADDR'];

    // Check if the IP address exists in the table
    $existing_ip = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ip_address = %s", $user_ip));

    if ($existing_ip) {
        $message_limit = $existing_ip->message_limit;
        wp_send_json_success(array('messageLimit' => $message_limit));
    } else {
        // IP address not found, set the default message limit
        $message_limit = 0;
        wp_send_json_error('Message limit not found.');
    }
}

add_action('wp_ajax_get_message_limit', 'get_message_limit');
add_action('wp_ajax_nopriv_get_message_limit', 'get_message_limit');

// Decrease message limit
function decrease_message_limit() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'message_limits';

    // Retrieve the user's IP address
    $user_ip = $_SERVER['REMOTE_ADDR'];

    // Check if the IP address exists in the table
    $existing_ip = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ip_address = %s", $user_ip));

    if ($existing_ip) {
        $update_result = $wpdb->update(
            $table_name,
            array('message_limit' => $existing_ip->message_limit - 1),
            array('id' => $existing_ip->id),
            array('%d'),
            array('%d')
        );

        if ($update_result === false) {
            $error_message = 'Failed to decrease message limit. Error: ' . $wpdb->last_error;
            error_log($error_message);
            wp_send_json_error(array('message' => $error_message));
        } else {
            wp_send_json_success(array('message' => 'Message limit decreased successfully.'));
        }
    } else {
        wp_send_json_error(array('message' => 'IP address not found in the message limits table.'));
    }
}

add_action('wp_ajax_decrease_message_limit', 'decrease_message_limit');
add_action('wp_ajax_nopriv_decrease_message_limit', 'decrease_message_limit');

function reset_message_limit() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'message_limits';

    // Reset the message limit to the initial value (30 messages)
    $update_result = $wpdb->update(
        $table_name,
        array('message_limit' => 30),
        array('1' => '1'),
        array('%d'),
        array('%d')
    );

    if ($update_result === false) {
        error_log('Failed to reset message limit. Error: ' . $wpdb->last_error);
    }
}

// Schedule the reset_message_limit function to run once every 24 hours
add_action('reset_message_limit_cron', 'reset_message_limit');
if (!wp_next_scheduled('reset_message_limit_cron')) {
    wp_schedule_event(time(), 'daily', 'reset_message_limit_cron');
}

?>