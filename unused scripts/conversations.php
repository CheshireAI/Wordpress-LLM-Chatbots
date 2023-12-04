<?php
/*
 * conversations.php
 * Template for displaying conversation history shortcode.
 */

// CSS Styles
function conversation_history_styles() {
    ?>
    <style>
        /* Conversation History Container */
        .conversation-history {
            margin-top: 20px;
        }

        /* Conversation Session */
        .conversation-session {
            margin-bottom: 20px;
        }

        /* Session Header */
        .session-header {
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* Chat Bubble */
        .chat-bubble {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
        }

        /* Alternating Colors for Chat Bubbles */
        .chat-bubble:nth-child(even) {
            background-color: #f7f7f7;
        }

        /* Responsive Styles */
        @media screen and (max-width: 767px) {
            /* Adjust styles for mobile devices */
            .conversation-history {
                margin-top: 10px;
            }

            .chat-bubble {
                padding: 5px;
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'conversation_history_styles');

// Shortcode Function
function display_conversation_history_shortcode() {
    // Get the current user's ID
    $user_id = get_current_user_id();

    // Check if the user is logged in
    if ($user_id !== 0) {
        // Get the conversation history from the database
        global $wpdb;
        $tableName = $wpdb->prefix . 'chatbot_conversations';
        $conversation_data = $wpdb->get_row($wpdb->prepare("SELECT conversation FROM $tableName WHERE user_id = %d", $user_id), ARRAY_A);

        // Check if conversation data exists for the user
        if ($conversation_data) {
            $output = '<div class="conversation-history">';
            
            // Split the conversation into individual sessions
            $sessions = explode("\n\n", $conversation_data['conversation']);
            
            // Loop through each session
            foreach ($sessions as $i => $session) {
                $output .= '<div class="conversation-session">';
                $output .= '<div class="session-header">Session ' . ($i + 1) . '</div>';
                
                // Split the session into individual chat bubbles
                $chat_bubbles = explode("\n", $session);
                
                // Loop through each chat bubble in the session
                foreach ($chat_bubbles as $bubble) {
                    $output .= '<div class="chat-bubble">' . $bubble . '</div>';
                }
                
                $output .= '</div>';
            }
            
            $output .= '</div>';
        } else {
            $output = 'No conversation history available.';
        }
    } else {
        $output = 'Please log in to view conversation history.';
    }

    return $output;
}
add_shortcode('conversation_history', 'display_conversation_history_shortcode');
