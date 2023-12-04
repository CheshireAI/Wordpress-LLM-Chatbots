<?php
function chat2_shortcode() {
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
        </div>
    <div id="lightbox" class="lightbox">
        <img src="your_image_source">
    </div>
';
}

function generate_image2_ajax() {
    $prompt = sanitize_text_field($_POST['prompt']);
    $image_url = generate_image2($prompt);

    if ($image_url === false) {
        wp_send_json_error();
    } else {
        wp_send_json_success(array('data' => $image_url));
    }

    wp_die();
}

function generate_image2($prompt) {
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

function generate_chat2() {
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
    $prompt_text = "### Instruction:\n" . $character_description . "\nPlease greet your customer as " . $psychicName . " the psychic.\n### Response:\n";
} else {
    if (empty($prompt)) {
        wp_send_json_error("Error: Empty prompt received.");
        wp_die();
    }

    $prompt_text = "### Instruction:\n" . $character_description . "\n" . $conversationHistory . "\nContinue the conversation by asking another question about psychic topics such as: " . $prompt . "\n### Response:\n";
}

    // Log the prompt being sent to the API
    error_log("Prompt sent to API: " . $prompt_text);

    $params = array(
        'prompt' => $prompt_text,
        'max_new_tokens' => 250,
        'autoload_model' => true,
        'max_new_tokens_min' => 1,
        'max_new_tokens_max' => 2000,
        'seed' => -1,
        'turn_template' => "",
        'custom_stopping_strings' => "",
    'stop_at_newline' => false,
    'add_bos_token' => true,
    'ban_eos_token' => false,
    'skip_special_tokens' => true,
    'truncation_length' => 2048,
    'truncation_length_min' => 0,
    'truncation_length_max' => 8192,
    'instruction_template' => "None",
    'chat_prompt_size' => 2048,
    'chat_prompt_size_min' => 0,
    'chat_prompt_size_max' => 2048,
    'chat_generation_attempts' => 1,
    'chat_generation_attempts_min' => 1,
    'chat_generation_attempts_max' => 10,
    'default_extensions' => [],
    'chat_default_extensions' => [
        "gallery"
    ],
    'do_sample' => true,
    'temperature' => 0.7,
    'top_p' => 0.5,
    'typical_p' => 0.19,
    'repetition_penalty' => 1.1,
    'encoder_repetition_penalty' => 1.0,
    'top_k' => 0,
    'min_length' => 0,
    'no_repeat_ngram_size' => 0,
    'num_beams' => 1,
    'penalty_alpha' => 0,
    'length_penalty' => 1,
    'early_stopping' => false,
    'stopping_strings' => ["\n[", "\n>", "]:", "\n#", "\n##", "\n###", "##", "###", "000000000000", "1111111111", "0.0.0.0.", "1.1.1.1.", "2.2.2.2.", "3.3.3.3.", "4.4.4.4.", "5.5.5.5.", "6.6.6.6.", "7.7.7.7.", "8.8.8.8.", "9.9.9.9.", "22222222222222", "33333333333333", "4444444444444444", "5555555555555", "66666666666666", "77777777777777", "888888888888888", "999999999999999999", "01010101", "0123456789", "<noinput>", "<nooutput>"],
    );

    $response = wp_remote_post('http://100.79.77.80:5000/api/v1/generate', array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode($params),
        'timeout' => 30, // Increase the timeout to 30 seconds
    ));

    // Log the entire response.
    error_log("API response: " . print_r($response, true));

// Add error handling here.
if ( is_wp_error( $response ) ) {
    $error_message = $response->get_error_message();
    wp_send_json_error("WP Error: " . $error_message);
    wp_die();
}

// Check the response code
if ( 200 == wp_remote_retrieve_response_code( $response ) ) {
    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    // Extract the result text
    $result = isset($response_body['results'][0]['text']) ? $response_body['results'][0]['text'] : null;
    if ($result === null) {
        wp_send_json_error("Error: Unexpected API response.");
        wp_die();
    }

// Clean up the result
$result = preg_replace('/[^\p{Common}\p{Latin}\p{N} .,?!]|##|`/u', '', $result);
    
    // Print or further process the result.
    // For example, you might want to send it as JSON response.
    $responses[] = trim($result);
    wp_send_json_success($responses);
    wp_die();

} else {
    wp_send_json_error("Error: API response status code is not 200.");
    wp_die();
}

// Get the image URL
    $image_url = '';
    $responses = array();
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    $ai_response = $response_body['choices'][0]['text'];
    $responses[] = trim($ai_response);

$imagePromptPhrases = array('show me', 'send me', 'can I see', 'illustrate', 'give me', 'can you show', 'could I see', 'would you show', 'want to see', 'like to see', 'wish to see', 'allow me to see', 'picture of', 'image of', 'photo of', 'may I view', 'display', 'portrait of', 'drawing of', 'photograph of', 'snapshot of', 'pick', 'choose', 'draw', 'select',);
foreach ($imagePromptPhrases as $phrase) {
    $phrasePos = strpos(strtolower($prompt), $phrase);
    if ($phrasePos !== false) {
        // Get the substring after the trigger phrase
        $imagePrompt = substr($prompt, $phrasePos + strlen($phrase));
        // Generate image only if the specific keywords are in the prompt
        $image_url = generate_image2($imagePrompt);
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

add_action('wp_ajax_generate_image2', 'generate_image2_ajax');
add_action('wp_ajax_nopriv_generate_image2', 'generate_image2_ajax');
add_action('wp_ajax_generate_chat2', 'generate_chat2');
add_action('wp_ajax_nopriv_generate_chat2', 'generate_chat2');

add_shortcode('chat2', 'chat2_shortcode');
?>