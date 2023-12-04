<?php
function chat_shortcode() {
    $openai_key = "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";
    $ajaxurl = esc_url( admin_url( 'admin-ajax.php' ) ); // Define ajaxurl variable here
    $selected_psychic = strtolower(get_option('psychic_name', 'A PSYCHIC'));
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
        </div>
    <div id="lightbox" class="lightbox"><img src="default.png"></div>
';
}

function generate_image_ajax() {
    $prompt = sanitize_text_field($_POST['prompt']);
    $image_url = generate_image($prompt);

    if ($image_url === false) {
        wp_send_json_error();
    } else {
        wp_send_json_success(array('data' => $image_url));
    }

    wp_die();
}

function generate_image($prompt) {
    $openai_key = "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"; 

    // Send a request to the DALL-E API
    $image_response = wp_remote_post('https://api.openai.com/v1/images/generations', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $openai_key,
        ),
        'body' => json_encode(array(
            'prompt' => $prompt,
            'n' => 1,
            'size' => '256x256',
        )),
        'timeout' => 30,
    ));

    if (is_wp_error($image_response)) {
        error_log("Error when calling OpenAI DALL-E API: " . $image_response->get_error_message());
        return false; 
    }

// Get the response body and decode it
$image_response_body = json_decode(wp_remote_retrieve_body($image_response), true);

// Check if the "data" key exists in the response body
if (array_key_exists("data", $image_response_body)) {
    
// Get the image URL from the response
    $image_url = $image_response_body['data'][0]['url'];

// Return the image URL directly
    return $image_url;
} else {
// If the "data" key doesn't exist, log an error and return false
    error_log("Error: 'data' key not found in DALL-E API response. Full response: " . print_r($image_response_body, true));
    return false;
}
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
$prompt_text = "Without mentioning the time of day, weather, or using placeholder words like Name User Person Image or Attachment, create a greeting using emojis that explains what you specialize in and ends with a question that invites the user to engage. You are a psychic named " . $psychicName . ".";
} else {
  if (empty($prompt)) {
    wp_send_json_error("Error: Empty prompt received.");
    wp_die();
  }

  $prompt_text = $character_description . " Remember, as an online psychbot, you can't book appointments or charge fees, you also can't physically see the user or know what they are doing. You can send images (the user will say send me...) but you can't see the image so don't try to add details or describe the image. Do not send a link to the image, do not send an email, do not say see attached image, do not type a placeholder where the image should be, it will appear magically. Do not make the person wait for you to do something. If you encounter questions you cannot know the answer to, like what day it is, find a creative way to respond without giving a wrong answer. " . $conversationHistory . "Continue a conversation that stays on topic, using emojis, and provides short but valuable responses. Q: " . $prompt . "A:";
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

// Get the image URL
    $image_url = '';
    $responses = array();
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    $ai_response = $response_body['choices'][0]['text'];
    $responses[] = trim($ai_response);

$imagePromptPhrases = array('show me', 'send', 'illustrate', 'give', 'show', 'see', 'picture', 'image', 'photo', 'view', 'display', 'portrait', 'drawing', 'gimme', 'lemme', 'photograph', 'look', 'snapshot', 'pick', 'choose', 'draw', 'select',);
foreach ($imagePromptPhrases as $phrase) {
    $phrasePos = strpos(strtolower($prompt), $phrase);
    if ($phrasePos !== false) {
        // Get the substring after the trigger phrase
        $imagePrompt = substr($prompt, $phrasePos + strlen($phrase));
        // Generate image only if the specific keywords are in the prompt
        $image_url = generate_image($imagePrompt);
        break;
    }
}

if ($image_url !== false && $image_url !== '') {
    $image_tag = '<div class="generated-class"><a href="' . $image_url . '" data-lightbox="generated-image"><img class="generated-image" src="' . $image_url . '" alt="Generated image"></a></div>';
    $responses[] = $image_tag;
}

    wp_send_json_success($responses);
    wp_die();
}

add_action('wp_ajax_generate_image', 'generate_image_ajax');
add_action('wp_ajax_nopriv_generate_image', 'generate_image_ajax');
add_action('wp_ajax_generate_chat', 'generate_chat');
add_action('wp_ajax_nopriv_generate_chat', 'generate_chat');

add_shortcode('chat', 'chat_shortcode');
?>