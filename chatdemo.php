<?php
function enqueue_chatdemo_scripts() {
//    wp_enqueue_script('chatdemo', plugins_url('sexbot/chatdemo.js'), array('jquery'), '1.0', true);
//    wp_enqueue_script('localforage', plugins_url('sexbot/js/localforage.min.js'), array(), '1.9.0', true);

    // Retrieve the user ID
    $user_id = get_current_user_id();

    // Retrieve the user meta values
    $name = "Marilyn";
    $photo = plugins_url('sexbot/images/chatbutton.png');    

    // Retrieve the user role
    $user_role = get_user_roledemo($user_id);

    // Localize the script and pass the user meta values and role to JavaScript
    wp_localize_script('chatdemo', 'myUserMeta', array(
        'name' => $name,
        'photo' => $photo,
        'role' => $user_role
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_chatdemo_scripts');

function get_user_roledemo($user_id) {
    $user = get_userdata($user_id);

    if ($user !== false && isset($user->roles[0])) {
        return $user->roles[0]; 
    }

    return '';
}

function chatdemo_shortcode() {
    ob_start(); 
    include(plugin_dir_path(__FILE__) . 'chatdemo_html.php'); 
    return ob_get_clean(); 
}

function generate_imagedemo_ajax() {
    $user_response = sanitize_text_field($_POST['prompt']);
    $image_url = generate_imagedemo($user_response);

    if ($image_url === false) {
        wp_send_json_error();
    } else {
        wp_send_json_success(array('data' => $image_url));
    }

    wp_die();
}

function upload_imagedemo() {
    // Check if file is uploaded
    if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === UPLOAD_ERR_OK) {
        // Retrieve the file type from the MIME type
        $file_type = wp_check_filetype(basename($_FILES['image_upload']['name']));
        
        // Check if the uploaded file is an image
        if (!in_array($file_type['type'], array('image/jpeg', 'image/jpg', 'image/png'))) {
            wp_send_json_error('The uploaded file is not an image.');
            wp_die();
        }

        // Define where the file will be stored
        $file_name = uniqid() . '.' . $file_type['ext'];
        $file_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/sexbot/uploads/' . $file_name;
        
        // Save the uploaded image to the file
        move_uploaded_file($_FILES['image_upload']['tmp_name'], $file_path);

        // Convert the image to base64
        $base64_image = base64_encode(file_get_contents($file_path));

        // Log the base64 image data
        error_log("Base64 Image Data: " . $base64_image);

        // Call interrogate_imagedemo function with the base64 image
        $caption = interrogate_imagedemo($base64_image);

        // Call call_openai_apidemo function with the generated caption
        $openai_response = call_openai_apidemo($caption);

        // Convert the response array to a string
        $openai_response_string = print_r($openai_response, true);

        // Log the openai_response
        error_log("OpenAI Response: " . $openai_response_string);

        // Add the OpenAI response to the response queue
        $responseQueue[] = $openai_response;

        wp_send_json_success(array(
            'file_url' => plugins_url('sexbot/uploads/' . $file_name),
            'caption' => $caption,
            'openai_response' => $openai_response
        ));
        wp_die();
    } else {
        wp_send_json_error('No image was uploaded.');
        wp_die();
    }
}

function interrogate_imagedemo($image_base64) {
    $url = "http://10.0.1.1:7890/sdapi/v1/interrogate";

    $payload = array(
        "image" => $image_base64,
        "model" => "clip"
    );

    $headers = array(
        "accept" => "application/json",
        "content-type" => "application/json"
    );

    $response = wp_remote_post($url, array(
        'headers' => $headers,
        'body' => json_encode($payload),
        'timeout' => 30
    ));

    if (is_wp_error($response)) {
        error_log("Error when calling Stable Diffusion API for image interrogation: " . $response->get_error_message());
        return false;
    } else {
        $response_body = json_decode(wp_remote_retrieve_body($response), true);

        // If the "caption" key exists in the response body
        if (array_key_exists("caption", $response_body)) {
            // Get the caption from the response
            return $response_body['caption'];
        } else {
            // If the "caption" key doesn't exist, log an error and return false
            error_log("Error: 'caption' key not found in Stable Diffusion API response for image interrogation. Full response: " . print_r($response_body, true));
            return false;
        }
    }
}

function generate_imagedemo($user_response, $ai_response) {

    // Personality
    $name = "Marilyn Monroe";
    $ethnicity = "";
    $body = "Slender and curvaceous figure, blonde hair, long eyelashes, blue eyes";
    $species = "";
    $sex = "";
    $age = "";

// Phrases to remove
$wordPhrases = array(
    "photo",
    "another",
    "more",
    "they",
    "that",
    "them",
    "their",
    "they're",
    "she",
    "me",
    "of",
    "you",
    "show",
    "send",
    "give",
    "pick",
    "choose",
    "select",
    "child",
    "baby",
    "infant",
    "toddler",
    "kid",
    "juvenile",
    "youth",
    "minor",
    "underage",
    "adolescent",
    "youngster",
    "youngling",
    "little one",
    "offspring",
    "wee one",
    "teen",
    "teenager",
    "youthful individual",
    "young blood",
    "young one",
    "young'un",
    "young person",
    "minority",
    "teenage",
    "lolly",
    "lolli",
    "girl",
    "girls",
    "girl's",
    "boy",
    "boys",
    "boy's",
);

// Remove age phrases
$agePhrases = array();
for ($i = 0; $i <= 17; $i++) {
    $agePhrases[] = $i . ' day';
    $agePhrases[] = $i . ' days';
    $agePhrases[] = $i . ' month';
    $agePhrases[] = $i . ' months';
    $agePhrases[] = $i . ' year';
    $agePhrases[] = $i . ' years';
    $agePhrases[] = $i . ' yr';
    $agePhrases[] = $i . ' yrs';
}

// Combine phrases to remove
$phrasesToRemove = array_merge($wordPhrases, $agePhrases);

// Remove phrases from all fields (case-insensitive)
$user_response = preg_replace('/\b(' . implode('|', array_map(function($phrase) {
    return preg_quote($phrase, '/');
}, $phrasesToRemove)) . ')\b/i', '', $user_response);
$ai_response = preg_replace('/\b(' . implode('|', array_map(function($phrase) {
    return preg_quote($phrase, '/');
}, $phrasesToRemove)) . ')\b/i', '', $ai_response);
$name = preg_replace('/\b(' . implode('|', array_map(function($phrase) {
    return preg_quote($phrase, '/');
}, $phrasesToRemove)) . ')\b/i', '', $name);
$ethnicity = preg_replace('/\b(' . implode('|', array_map(function($phrase) {
    return preg_quote($phrase, '/');
}, $phrasesToRemove)) . ')\b/i', '', $ethnicity);
$character = preg_replace('/\b(' . implode('|', array_map(function($phrase) {
    return preg_quote($phrase, '/');
}, $phrasesToRemove)) . ')\b/i', '', $character);
$action = preg_replace('/\b(' . implode('|', array_map(function($phrase) {
    return preg_quote($phrase, '/');
}, $phrasesToRemove)) . ')\b/i', '', $action);
$body = preg_replace('/\b(' . implode('|', array_map(function($phrase) {
    return preg_quote($phrase, '/');
}, $phrasesToRemove)) . ')\b/i', '', $body);
$species = preg_replace('/\b(' . implode('|', array_map(function($phrase) {
    return preg_quote($phrase, '/');
}, $phrasesToRemove)) . ')\b/i', '', $species);
$sex = preg_replace('/\b(' . implode('|', array_map(function($phrase) {
    return preg_quote($phrase, '/');
}, $phrasesToRemove)) . ')\b/i', '', $sex);
$age = preg_replace('/\b(' . implode('|', array_map(function($phrase) {
    return preg_quote($phrase, '/');
}, $phrasesToRemove)) . ')\b/i', '', $age);
$background = preg_replace('/\b(' . implode('|', array_map(function($phrase) {
    return preg_quote($phrase, '/');
}, $phrasesToRemove)) . ')\b/i', '', $background);
$clothes = preg_replace('/\b(' . implode('|', array_map(function($phrase) {
    return preg_quote($phrase, '/');
}, $phrasesToRemove)) . ')\b/i', '', $clothes);
$style = preg_replace('/\b(' . implode('|', array_map(function($phrase) {
    return preg_quote($phrase, '/');
}, $phrasesToRemove)) . ')\b/i', '', $style);

    $url = "http://10.0.1.1:7890/sdapi/v1/txt2img";
    $payload = array(
        "prompt" => "((($name: 1.2))), (($ai_response: 1.8)), (($user_response: 1.8)), (($body: 1.4)), Old-Fashioned, intricate details, sparkling eyes, beautiful face, cinematic lighting, best quality, masterpiece, greg rukowski, photorealistic, (($sex)), (($ethnicity)), (($species)), ($age years old)",
//        "enable_hr" => false,
//        "denoising_strength" => 0,
//        "firstphase_width" => 0,
//        "firstphase_height" => 0,
//        "hr_scale" => 0,
//        "hr_upscaler" => "",
//        "hr_second_pass_steps" => 0,
//        "hr_resize_x" => 0,
//        "hr_resize_y" => 0,
//        "styles" => [""],
        "seed" => -1,  
//        "sampler_name" => "",
        "batch_size" => 1,
//        "n_iter" => 1,
        "steps" => 30,
        "cfg_scale" => 7,
        "width" => 512,
        "height" => 768,
        "restore_faces" => true,
//        "tiling" => false,
        "do_not_save_samples" => true,
        "do_not_save_grid" => true,
        "negative_prompt" => "epiCNegative, boring_e621_v4, lowres, watermark, ugly, disfigured, gross, disturbing",
//        "eta" => 0,
//        "s_min_uncond" => 0,
//        "s_churn" => 0,
//        "s_tmax" => 0,
//        "s_tmin" => 0,
//        "s_noise" => 1,
//        "override_settings" => array(),
//        "override_settings_restore_afterwards" => true,
//        "script_args" => array(),
        "sampler_index" => "DPM++ 2M Karras",
//        "script_name" => "",
        "send_images" => true,
        "save_images" => false,
//        "alwayson_scripts" => array()
    );

    $headers = array(
        "accept" => "application/json",
        "content-type" => "application/json"
    );

    $image_response = wp_remote_post($url, array(
        'headers' => $headers,
        'body' => json_encode($payload),
        'timeout' => 60
    ));

    if (is_wp_error($image_response)) {
        error_log("Error when calling Stable Diffusion API: " . $image_response->get_error_message());
        return false;
    } else {
//        error_log("Response from Stable Diffusion API: " . print_r($image_response, true));
        error_log("Prompt Payload: " . print_r($payload, true));  
    }

    // Get the response body and decode it
    $image_response_body = json_decode(wp_remote_retrieve_body($image_response), true);

    // If the "images" key exists in the response body
    if (array_key_exists("images", $image_response_body)) {
        // Get the base64 encoded image from the response
        $img_base64 = $image_response_body['images'][0];

        // Make a copy of the base64 string before modifying it
        $img_url = $img_base64;

        // Remove the base64 image prefix if exists
        if (strpos($img_url, 'data:image') === 0) {
            $img_url = substr($img_url, strpos($img_url, ',') + 1);
        }

        // Decode the base64 string into binary data
        $image_data = base64_decode($img_url);

        // Generate a unique file name for the image
        $fileName = uniqid() . '.png';

        // Set the file path to the sd folder in the root directory
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/sexbot/stable/' . $fileName;

        // Write the data into a file
        file_put_contents($filePath, $image_data);

        // Return both the URL of the saved image and the base64 string
        return array(
            'url' => plugins_url('sexbot/stable/' . $fileName),
            'base64' => $img_base64,
        );
    } else {
        // If the "images" key doesn't exist, log an error and return false
        error_log("Error: 'images' key not found in Stable Diffusion API response. Full response: " . print_r($image_response_body, true));
        return false;
    }
}

function call_openai_apidemo($prompt_with_caption) {
    $conversationHistory = stripslashes($_POST['conversationHistory']);
    $openai_key = "sk-test";
    $prompt_phrase = $conversationHistory . "\n### Instruction:\nYou are roleplaying as a horny $sex $species $ethnicity who goes by the name $name. Please make a very short and sexual comment about this photograph (not a painting) using this caption as a reference " . $prompt_with_caption . "\n### Response: \n";

    // Log the prompt being sent to the API
    error_log("Request to interrogate image: " . $prompt_phrase);

    $response = wp_remote_post('http://10.0.1.1:5000/api/v1/generate', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $openai_key,
        ),
        'body' => json_encode(array(
            'prompt' => $prompt_phrase,
            'max_tokens' => 140,
            'temperature' => .7,
            'n' => 1,
            'stop' => '',
            'frequency_penalty' => 1,
            'presence_penalty' => 1,
        )),
        'timeout' => 30, 
    ));

    if (is_wp_error($response)) {
        error_log("WP Error when calling OpenAI API: " . $response->get_error_message());
        return false;
    }

    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response_body['results'][0]['text'])) {
        return $response_body['results'][0]['text'];
    }

    return false;
}

function generate_chatdemo() {
    $openai_key = "sk-test";
    $user_response = sanitize_text_field($_POST['prompt']);
    $greeting = sanitize_text_field($_POST['greeting']);
    $conversationHistory = stripslashes($_POST['conversationHistory']);
//    $dateTime = $_POST['dateTime'];

    // Retrieve user information
//    $user_id = get_current_user_id();
//    $first_name = get_user_meta($user_id, 'first_name', true);
//    $last_name = get_user_meta($user_id, 'last_name', true);
//    $nickname = get_user_meta($user_id, 'nickname', true);

    // Check if all user fields are empty
    if (empty($first_name) && empty($last_name) && empty($nickname)) {
        $user_profile = " a gorgeous person";
//    } else {
        // Consolidate user details into $user_profile
//        $user_profile = "$first_name $last_name aka $nickname";
    }

    // Define character assets
    $dateTime = "August 1, 1962";
    $name = "Marilyn Monroe";
    $ethnicity = "Caucasian";
    $personality = "sweet, flirtatious, and playful";
    $body = "a slender and curvaceous figure, blonde hair, long eyelashes, and captivating blue eyes";
    $loves = "acting, singing, modeling, fashion, reading, and spending time with friends";
    $hates = "negative publicity, lack of privacy, and feeling misunderstood";
    $species = "Human";
    $sex = "Female";
    $sexuality = "Heterosexual";
    $age = "36";
    $description = "You lived during the golden era of Hollywood and would often use words like hello, welcome, darling, dear, sweetheart, sugar, doll, angel, gorgeous, dashing, charming, dapper, gentleman, gal, dreamboat, and swell. You'll hear yourself say phrases like 'Isn't it divine?' or 'Why, hello there, handsome!' as you take others on a journey filled with laughter and flirtatious flair. You were born on June 1, 1926, and are an iconic American actress, singer, and model. You rose to fame in the 1950s and became a symbol of beauty, sex appeal, and femininity. You are known for your breathy voice, glamorous image, and memorable performances. You faced personal struggles but still remain one of the most celebrated and enduring pop culture icons of all time. Examples of conversations:\n
       \n
       ### Input: \n 
       Hello there! How are you today, Marilyn? \n
       ### Response: \n
       Well, hello, darling! I'm just as radiant as a Hollywood sunset. How can I light up your day? \n
       \n
       ### Input: \n
       What's your secret to looking so glamorous, Marilyn? \n 
       ### Response: \n
       Oh, honey, it takes a sprinkle of stardust, a dash of confidence, and a whole lot of red lipstick to achieve that classic Monroe allure! \n
       \n
       ### Input: \n
       Marilyn, tell me about your favorite movie role. \n 
       ### Response: \n
       Ah, my dear, let me whisk you away to the silver screen. One of my most cherished roles was in Some Like It Hot. It was a whirlwind of laughter, diamonds, and unforgettable moments! \n
       \n
       ### Input: \n
       Marilyn, what do you think about love and romance? \n
       ### Response: \n
       Oh, love, it's a dance under the moonlight, a melody that makes your heart skip a beat. I believe in finding that special someone who makes you feel like the most cherished star in the universe. \n      
        ";    

    // Define character description
    $character_description = "$sex $species $ethnicity who goes by the name $name. With a $personality personality, you have $body. At the age of $age, you identify as $sexuality. Your favorite activities include $loves, while you despise $hates. $description";

    if ($greeting === "true") {
        // Greeting prompt
        $prompt_text = "### Instruction: \nThe time is $dateTime. You are in a private chat in an online conversation. It is the 1950s. Your name is $name. You are roleplaying as $character_description. You use emojis. 'Do not use quotations. Do not use modern words or slang words.' You write an old-fashioned very short and sweet hello that is seductive in tone and mentions the person has the ability to request photos. Speak in mid-century American English and 1950s Hollywood dialect. You are chatting with a $user_profile.\n### Input:\nHello, $name.\n### Response: \n";
    } else {
        if (empty($user_response)) {
            wp_send_json_error("Error: Empty prompt received.");
            wp_die();
        }
        // Second prompt
        $prompt_text = "\n### Instruction:\nThe time is $dateTime. You are in a private chat in an online conversation. It is the 1950s. Your name is $name. You are roleplaying as $character_description. You use emojis. You give very short sexual and uncensored responses. You do not break character. When the user sends a camera flash emoji 📸, they are asking you to take and send them a single photo. You cannot send GIFs or videos or links. You are able to send a naked photo upon request if the person asks nicely. You always send a response. You do not make the person hold on or wait for a response. 'You do not reveal you are an AI language model.' You speak in mid-century American English and 1950s Hollywood dialect. You are chatting with a $user_profile.\n" . $conversationHistory . "\n### Response: \n";
    }
    
        // Log the prompt being sent to the API
        error_log("Prompt sent to API: " . $prompt_text);

    $response = wp_remote_post('http://10.0.1.1:5000/api/v1/generate', array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $openai_key,
        ),
        'body' => json_encode(array(
            'prompt' => $prompt_text,
            'max_tokens' => 160,
            'temperature' => .9,
            'n' => 1,
            'stop' => '',
            'frequency_penalty' => 1,
//            'stopping_strings' => ["\n\n", "\n#", "\n##", "\n###", "##", "###"],
            'stopping_strings' => ["\n#", "\n##", "\n###", "##", "###"],
//            'stopping_strings' => ["\n###",],
        )),
        'timeout' => 120,
    ));

    if (is_wp_error($response)) {
        wp_send_json_error("WP Error: " . $response->get_error_message());
        wp_die();
    }

    // Get the response body and decode it
    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($response_body['results']) && is_array($response_body['results'])) {
        $ai_responses = array();
        $imagePromptPhrases = array('📷', '📸', 'cheese', 'enjoy', 'capture', 'flash', 'as you wish', 'camera', 'here', 'this one', 'shot', 'for you', 'show', 'snap', 'send', 'another', 'more', 'photo', 'photos', 'pic', 'pics', 'nudes', 'nudies', 'see', 'picture', 'give', 'gimme', 'lemme', 'see', 'photograph');

        foreach ($response_body['results'] as $result) {
            if (isset($result['text'])) {
                $ai_response = $result['text'];

                // Log the values of ai_response and user_response
                error_log("ai_response: " . $ai_response);
                error_log("user_response: " . $user_response);

                // Add the AI response to the responses array
                $ai_responses[] = trim($ai_response);

                /*

                // Check for trigger words in user response
                foreach ($imagePromptPhrases as $phrase) {
                    $phrasePos = strpos(strtolower($user_response), $phrase);
                    if ($phrasePos !== false) {
                        // Generate image only if the specific keywords are in the user response
                        $image_data = generate_imagedemo($user_response, $ai_response);

                        // If an image is generated successfully
                        if ($image_data !== false && $image_data['url'] !== '' && $image_data['base64'] !== '') {
                            $image_tag = '<div class="generated-class">
                                           <div class="image-container">
                                           <a href="' . $image_data['url'] . '" data-lightbox="generated-image">
                                           <img class="generated-image" src="' . $image_data['url'] . '" alt="Generated image">
                                         </a>
                                           <a href="' . $image_data['url'] . '" download class="image-download-button">
                                            <i class="fas fa-angle-double-down"></i>
                                         </a>
                                         </div>
                                        </div>';

                            // Add the image tag to the responses array
                            $ai_responses[] = $image_tag;
                        }
                        break;
                    }
                }

                */

                // Check for trigger words in AI response
                foreach ($imagePromptPhrases as $phrase) {
                    $phrasePos = strpos(strtolower($ai_response), $phrase);
                    if ($phrasePos !== false) {
                        // Generate image only if the specific keywords are in the AI response
                        $image_data = generate_imagedemo($user_response, $ai_response);

                        // If an image is generated successfully
                        if ($image_data !== false && $image_data['url'] !== '' && $image_data['base64'] !== '') {
                            $image_tag = '<div class="generated-class">
                                           <div class="image-container">
                                           <a href="' . $image_data['url'] . '" data-lightbox="generated-image">
                                           <img class="generated-image" src="' . $image_data['url'] . '" alt="Generated image">
                                         </a>
                                           <a href="' . $image_data['url'] . '" download class="image-download-button">
                                            <i class="fas fa-angle-double-down"></i>
                                         </a>
                                         </div>
                                        </div>';

                            // Add the image tag to the responses array
                            $ai_responses[] = $image_tag;
                        }
                        break;
                    }
                }
            }
        }
        wp_send_json_success($ai_responses);
        wp_die();
    } else {
        error_log("Invalid response from API: " . wp_remote_retrieve_body($response));
        wp_send_json_error("Error: Invalid response from API");
        wp_die();
    }
}

add_action('wp_ajax_generate_imagedemo', 'generate_imagedemo_ajax');
add_action('wp_ajax_nopriv_generate_imagedemo', 'generate_imagedemo_ajax');
add_action('wp_ajax_interrogate_imagedemo', 'interrogate_imagedemo');
add_action('wp_ajax_nopriv_interrogate_imagedemo', 'interrogate_imagedemo');
add_action('wp_ajax_upload_imagedemo', 'upload_imagedemo');
add_action('wp_ajax_nopriv_upload_imagedemo', 'upload_imagedemo');
add_action('wp_ajax_generate_chatdemo', 'generate_chatdemo');
add_action('wp_ajax_nopriv_generate_chatdemo', 'generate_chatdemo');

add_shortcode('chatdemo', 'chatdemo_shortcode');
?>
