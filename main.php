<?php
/*
Plugin Name: Sexbot
Description: AI Powered NSFW Porn Bot.
Version: 1.0
Author: Lex
License: GPL2
*/

include_once 'chat.php';
include_once 'character.php';

include_once 'chatdemo.php';
include_once 'messagelimit.php';

function api_keys_plugin_activate() {
    update_option('openai_key', 'sk-test');
    update_option('pixabay_key', '34839268-4c3901cf450801f3931f33b11');

    // Create chatbot_conversations table
    global $wpdb;
    $table_name = $wpdb->prefix . 'sexbot_sessions';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) NOT NULL,
        message text NOT NULL,
        direction ENUM('USER', 'BOT') NOT NULL,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id),
        INDEX user_id_index (user_id),
        INDEX timestamp_index (timestamp)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'api_keys_plugin_activate');

// Runs when the plugin is deactivated
function api_keys_plugin_deactivate() {
    delete_option('openai_key');
    delete_option('pixabay_key');
}
register_deactivation_hook(__FILE__, 'api_keys_plugin_deactivate');

function chatbot_enqueue_scripts() {

// Chat
if (is_page('chat')) { // URL is /chat

    // Enqueue JavaScript files with adjusted priorities
    wp_enqueue_script('hammer', plugins_url('sexbot/js/hammer.min.js'), array('jquery'), '2.0.8', true);
    wp_enqueue_script('localforage', plugins_url('sexbot/js/localforage.min.js'), array(), '1.9.0', true);
    wp_enqueue_script('pulltorefresh', plugins_url('sexbot/js/pulltorefresh.min.js'), array('jquery'), '0.1.22', true);
    wp_enqueue_script('anime-js', plugins_url('sexbot/js/anime.min.js'), array(), '3.2.1', true);

    // Enqueue other stylesheets and scripts
    wp_enqueue_style('font-awesome6', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0', 'all');

    // Enqueue other stylesheets and scripts
    wp_enqueue_style('chat_desktop', plugins_url('sexbot/css/chat_desktop.css'), array(), '1.0', 'all');
    wp_enqueue_style('chat_mobile', plugins_url('sexbot/css/chat_mobile.css'), array(), '1.0', 'only screen and (max-width: 767px)');
    wp_enqueue_style('character_desktop', plugins_url('sexbot/css/character_desktop.css'), array(), '1.0', 'all');
    wp_enqueue_style('character_mobile', plugins_url('sexbot/css/character_mobile.css'), array(), '1.0', 'only screen and (max-width: 767px)');

    // Include plugin-specific scripts with priority 20
    wp_enqueue_script('character', plugins_url('sexbot/character.js'), array('jquery'), '1.0', true);
    wp_enqueue_script('chat', plugins_url('sexbot/chat.js'), array('jquery', 'hammer'), '1.0', true);

    // Localize script for chat
    wp_localize_script('chat', 'myChatAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

    // Localize character script
    $character_data = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'action' => 'characterAjax'
    );
    wp_localize_script('character', 'character_data', $character_data);
}

    // Chat demo
    if (is_page('chatdemo')) { // URL is /chatdemo

        // Enqueue JavaScript files with adjusted priorities
        wp_enqueue_script('hammer', plugins_url('sexbot/js/hammer.min.js'), array('jquery'), '2.0.8', true);
        wp_enqueue_script('localforage', plugins_url('sexbot/js/localforage.min.js'), array(), '1.9.0', true);
        wp_enqueue_script('pulltorefresh', plugins_url('sexbot/js/pulltorefresh.min.js'), array('jquery'), '0.1.22', true);
        wp_enqueue_script('anime-js', plugins_url('sexbot/js/anime.min.js'), array(), '3.2.1', true);
    
        // Enqueue other stylesheets and scripts
        wp_enqueue_style('font-awesome6', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0', 'all');

        // Enqueue other stylesheets and scripts
        wp_enqueue_style('chatdemo_desktop', plugins_url('sexbot/css/chatdemo_desktop.css'), array(), '1.0', 'all');
        wp_enqueue_style('chatdemo_mobile', plugins_url('sexbot/css/chatdemo_mobile.css'), array(), '1.0', 'only screen and (max-width: 767px)');

        // Include plugin-specific scripts with priority 30
        wp_enqueue_script('chatdemo', plugins_url('sexbot/chatdemo.js'), array('jquery', 'hammer'), '1.0', true);
        wp_enqueue_script('chatbutton', plugins_url('sexbot/chatbutton.js'), array('jquery', 'hammer'), '1.0', true);

        // Localize script for chat
        wp_localize_script('chatdemo', 'myChatDemoAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
    }
}

add_action('wp_enqueue_scripts', 'chatbot_enqueue_scripts', 9); //must be priority 9 to come before default wordpress scripts
