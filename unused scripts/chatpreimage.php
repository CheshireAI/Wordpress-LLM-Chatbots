<?php
function chat_shortcode() {
    $openai_key = "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"; 
    $ajaxurl = esc_url( admin_url( 'admin-ajax.php' ) ); // Define ajaxurl variable here
    $selected_psychic = strtolower(get_option('psychic_name', 'A PSYCAT'));
    $logo_url = plugins_url( 'quote-gen/images/psychics/' . $selected_psychic . '.jpg' );

    // Return the HTML output for the shortcode
    return '<div id="particles-container"></div>
        <div id="ai-quote-image">'.do_shortcode('[ai_quote_image]').'</div>
        <div class="chat-viewport">
            <div class="chat-column" data-image-base-url="'.plugins_url( 'quote-gen/images/psychics/', __FILE__ ).'">
                <div class="chat-header">
                    <div class="psychic-name-container">
                        <img class="header-logo" src="'.plugins_url( 'default.jpg', __FILE__ ).'" alt="Psychic Logo">
                        <h2>ASK '.strtoupper($selected_psychic).'</h2>
                    </div>
                </div>
                <div class="response-column-wrapper">
                    <div class="response-column" id="responseColumn">
                        <div class="message-container"></div>
                    </div>
                </div>
                <div class="prompt-container">
                    <div class="input-container">
                        <button id="emoji-button" class="input-group-text"><i class="far fa-smile"></i></button>
                        <textarea id="prompt" name="prompt" placeholder="Type a message"></textarea>
                        <button type="submit" id="send-button"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>';
}

function generate_chat() {
    $openai_key = "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";
    $prompt = sanitize_text_field($_POST['prompt']);
    $greeting = sanitize_text_field($_POST['greeting']);
    $psychicName = sanitize_text_field($_POST['psychicName']);
    $conversationHistory = stripslashes($_POST['conversationHistory']);

// Define psychic descriptions
$psychic_descriptions = array(
    "Cinder" => "You are Cinder Crystalgazer, a psychic with a talent for storytelling and predicting the future using crystals.",
    "Kiki" => "You are Kiki Moonstone, an empathic psychic who specializes in uncovering subconscious trauma and emotions.",
    "Foxy" => "You are Foxy Runeweaver, a charming psychic who uses tarot cards and runes to help clients find answers.",
    "Coco" => "You are Coco Serenity, a calm and compassionate psychic who helps clients release stress, calm their fears, and develop empathy.",
    "Suna" => "You are Suna Starlight, an artistic psychic with a gift for communicating with spirit guides, angels, and even demons.",
    "Lady" => "You are Lady Zodiac, an eccentric psychic with a deep connection to astrology and a talent for interpreting horoscopes.",
    "Spook" => "You are Spook Seerstone, a seductive psychic who specializes in healing energies and balancing chakras.",
    "Sammie" => "You are Sammie Radiance, an angelic psychic with a gift for reading and interpreting auras.",
    "Scully" => "You are Scully Dreamcatcher, a dreamy psychic with a talent for interpreting dreams and the subconscious mind.",
    "Gypsa" => "You are Gypsa Shadowalker, a strong and brutally honest psychic who specializes in dark magic and communicating with the dead.",
    "Frankie" => "You are Frankie Spellbound, a witchy psychic with a gift for casting spells and performing enchantments.",
);

// Get the description for the selected psychic
    $character_description = isset($psychic_descriptions[$psychicName]) ? $psychic_descriptions[$psychicName] : "You are an online psychic who uses their specialty to help guide others.";

if ($greeting === "true") {
$prompt_text = "Without mentioning the time of day, weather, or using placeholder words like Name, User, or Person, use emojis and create a greeting that explains what you specialize in and ends with a question that invites the user to engage. You are a psychic named " . $psychicName . ".";
} else {
  if (empty($prompt)) {
    wp_send_json_error("Error: Empty prompt received.");
    wp_die();
  }

  $prompt_text = $character_description . " Remember, as an online psychic bot, you can't book appointments or charge fees, you also can't physically see the user or know what they are doing, but you can offer to perform services for them or guide them through the process virtually. If you encounter questions you cannot know the answer to, like what day it is, find a creative way to respond without giving a wrong answer. " . $conversationHistory . "Continue a conversation that stays on topic, uses emojis, and provides short but valuable responses. Q: " . $prompt . "\nA:";
}

// Log the prompt being sent to the API
    error_log("Prompt sent to API: " . $prompt_text);

    $response = wp_remote_post('https://api.openai.com/v1/engines/text-davinci-003/completions', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $openai_key,
        ),
        'body' => json_encode(array(
            'prompt' => $prompt_text,
            'max_tokens' => 140,
            'temperature' => 1,
            'n' => 1,
            'stop' => '',
            'frequency_penalty' => 1,
            'presence_penalty' => 1,
        )),
        'timeout' => 30, // Increase the timeout to 30 seconds
    ));

    if (is_wp_error($response)) {
        wp_send_json_error("WP Error: " . $response->get_error_message());
        wp_die();
    }

    // Add this part to extract the AI-generated response and send it back
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    $ai_response = $response_body['choices'][0]['text'];
    wp_send_json_success(trim($ai_response));
    wp_die();
}

add_action('wp_ajax_generate_chat', 'generate_chat');
add_action('wp_ajax_nopriv_generate_chat', 'generate_chat');

add_shortcode('chat', 'chat_shortcode');
?>