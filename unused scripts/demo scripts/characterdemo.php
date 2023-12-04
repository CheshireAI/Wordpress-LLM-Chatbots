<?php

function characterdemo_form_shortcode() {
    // Access the global $wpdb object
    global $wpdb;

    // Retrieve the current user ID
    $user_id = get_current_user_id();

    // Retrieve the meta values
    $fields = array(
        'photo_demo' => 'Photo',
        'name_demo' => 'Name',
        'personality_demo' => 'Personality',
        'body_demo' => 'Body',
        'character_demo' => 'Character',
        'loves_demo' => 'Loves',
        'hates_demo' => 'Hates',
        'action_demo' => 'Action',
        'clothes_demo' => 'Clothes',
        'background_demo' => 'Background',
        'style_demo' => 'Style',
        'species_demo' => 'Species',
        'ethnicity_demo' => 'Ethnicity',
        'sex_demo' => 'Sex',
        'sexuality_demo' => 'Sexuality',
        'age_demo' => 'Age 18+',
        'description_demo' => 'Description',
    );

    // Create the meta keys if they don't exist for a first-time user
    foreach ($fields as $field => $label) {
        if (!metadata_exists('user', $user_id, 'character_' . $field)) {
            $updated = add_user_meta($user_id, 'character_' . $field, '', true);
            if (!$updated) {
                $last_error = $wpdb->last_error ? $wpdb->last_error : "Update returned false, but no last error was found.";
                error_log("Failed to create '$field'. Database error: " . $last_error);
            }
        }
    }

    add_action('init', 'create_character_meta_keys');

    // Retrieve the character_photo meta value from user metadata
    $user_image_url = get_user_meta($user_id, 'character_photo', true);

    // Initialize the $output variable
    $output = '';

    // HTML
    $output .= '
    <div class="magic-wand-container">
    <button class="magic-form-icon" id="magic-form-button">
      <i class="fa-solid fa-user"></i>
    </button>
  </div>
  
  <div class="character-container">
    <div class="character-form-container">
    <form class="character-form" method="post" action="" enctype="multipart/form-data">
      <div class="circle">
        <img class="profile-pic" src="' . ($user_image_url ? $user_image_url : "https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg") . '">
        <div class="p-image">
                        <i class="fa fa-camera upload-button"></i>
                        <input class="file-upload" type="file" name="character_photo" accept="image/*"/>
                    </div>
                </div>
                <div class="form-row">
        ';     

    foreach ($fields as $field => $label) {
        if ($field === 'photo_demo') {
            continue; // Skip displaying the photo field
        }

        $value = get_user_meta($user_id, 'character_' . $field, true);

        if ($field === 'action_demo' || $field === 'character_demo' || $field === 'clothes+demo' || $field === 'background_demo' || $field === 'style_demo') {
            $output .= '
            <div class="form-column">
                <label for="' . $field . '" title="Select the character ' . $label . '">
                    ' . $label . '
                    <span class="help-icon" title="Help content for ' . $field . ' field" data-field="' . $field . '"></span>
                </label>
                <select name="' . $field . '">
        ';
    
            if ($field === 'clothes_demo') {
                $output .= '
                    <option value="" disabled selected>Select ' . $label . '</option>
                    <option value=" " ' . selected($value, 'no_clothes', false) . '>None</option>     
                    <option value="armani, fashion" ' . selected($value, 'armani_fashion', false) . '>Armani</option>
                    <option value="RFKTR_fshnsta, fashion" ' . selected($value, 'rfktr_fashionista', false) . '>Hot Couture</option>
                    <option value="zhubao, lingerie" ' . selected($value, 'zhubao_lingerie', false) . '>Lingerie</option>
                    <option value="suijing, diamonds, sparkling" ' . selected($value, 'suijing_crystal', false) . '>Diamonds</option>
                    <option value="longpao, dragon robe" ' . selected($value, 'longpao_dragon_robe', false) . '>Dragon Robe</option>
                    <option value="nvdi, oriental, princess" ' . selected($value, 'nvdi_oriental_princess', false) . '>Oriental Princess</option>
                    <option value="neiyi, oriental robe" ' . selected($value, 'neiyi_oriental_robe', false) . '>Oriental Robe</option>
                    <option value="heibai, lingerie" ' . selected($value, 'heibai_bw_lingerie', false) . '>B&W Lingerie</option>
                    <option value="n3t0p" ' . selected($value, 'n3t0p_fishnet', false) . '>Fishnet</option>
                    <option value="p0k13s, nipples" ' . selected($value, 'pointy_nipples', false) . '>Pointy Nipples</option>
                    <option value="vksw34t1, sweater dress" ' . selected($value, 'sweater_dress', false) . '>Sweater Dress</option>            
                    <option value="mulpty2, underwear" ' . selected($value, 'modest_underwear', false) . '>Modest Underwear</option>
                    <option value="jeansheels, jeans, heels" ' . selected($value, 'jeans_heels', false) . '>Jeans & High Heels</option>
                    <!-- Add more options here -->
                ';
            } 
            
            elseif ($field === 'action_demo') {
                $output .= '
                    <option value="" disabled selected>Select ' . $label . '</option>
                    <option value=" " ' . selected($value, 'no_species', false) . '>None</option>
                    <option value="suckhertonguealexzuov5, tongue out" ' . selected($value, 'tongue_out', false) . '>Tongue Out</option>
                    <option value="oral-dildo, sucking dildo" ' . selected($value, 'oral_dildo', false) . '>Oral Dildo</option>
                    <option value="under-boo, underboob" ' . selected($value, 'under_boob', false) . '>Underboob</option>
                    <!-- Add more options here -->
                ';
            } 

            if ($field === 'background_demo') {
                $output .= '
                    <option value="" disabled selected>Select ' . $label . '</option>
                    <option value=" " ' . selected($value, 'no_action', false) . '>None</option>
                    <option value="Clutter-Mechanical" ' . selected($value, 'mechanical_shop', false) . '>Mechanical Shop</option>
                    <option value="Clutter-Home" ' . selected($value, 'messy_room', false) . '>Messy Room</option>
                    <option value="changting, oriental" ' . selected($value, 'changting_oriental_architecture', false) . '>Oriental Architecture</option>
            
                    <option value="lora:stuck_in_washing_machine_v0.1:1.5, ass, stuck, washing machine, bent over, from behind" ' . selected($value, 'washing_machine', false) . '>Stuck in Washing Machine</option>
                    <option value="lora:change-05:1.2" ' . selected($value, 'fitting_room', false) . '>Fitting Room</option>
                    <option value="lora:bathtime-2:1.2" ' . selected($value, 'bath_tub', false) . '>Bathtub</option>
                    <option value="lora:couch-10:1.2" ' . selected($value, 'casting_couch', false) . '>Casting Couch</option>
                    <option value="lora:skyscraper-10:1.2" ' . selected($value, 'sky_scraper', false) . '>Skyscraper</option>
                    <option value="lora:jkUnderwaterShot:1.2" ' . selected($value, 'under_water', false) . '>Underwater</option>
                    <option value="lora:planebathroom-10:1.2, airplane bathroom" ' . selected($value, 'airplane_bathroom', false) . '>Airplane Bathroom</option>
                            
                    <!-- Add more options here -->
                ';
            }            
            
            elseif ($field === 'character_demo') {
                $output .= '
                    <option value="" disabled selected>Select ' . $label . '</option>
                    <option value=" " ' . selected($value, 'no_character', false) . '>None</option>
                    <option value="AmberHeard1, amber heard" ' . selected($value, 'amber_heard', false) . '>Amber Heard</option>
                    <option value="arma1, ana de armas" ' . selected($value, 'ana_de_armas', false) . '>Ana de Armas</option>
                    <option value="4nj0lie, angelina jolie" ' . selected($value, 'angelina_jolie', false) . '>Angelina Jolie</option>
                    <option value="AudreyHepburn, audry hepburn" ' . selected($value, 'audrey_hepburn', false) . '>Audrey Hepburn</option>
                    <option value="Bell4L0b4t001, bella lobato" ' . selected($value, 'bella_lobato', false) . '>Bella Lobato</option>
                    <option value="b1j0u, bijou phillips" ' . selected($value, 'bijou_phillips', false) . '>Bijou Phillips</option>
                    <option value="ch103s3v, chloe sevigny" ' . selected($value, 'chloe_sevigny', false) . '>Chloë Sevigny</option>
                    <option value="cr1styren, cristy ren" ' . selected($value, 'cristy_ren', false) . '>Cristy Ren</option>
                    <option value="the_trump, donald trump" ' . selected($value, 'donald_trump', false) . '>Donald Trump</option>
                    <option value="EmWat69, emma watson" ' . selected($value, 'emma_watson', false) . '>Emma Watson</option>
                    <option value="evag1, eva green" ' . selected($value, 'eva_green', false) . '>Eva Green</option>
                    <option value="evanW1, evan rachel wood" ' . selected($value, 'evan_rachel_wood', false) . '>Evan Rachel Wood</option>
                    <option value="evnclr, evelyn claire" ' . selected($value, 'evelyn_claire', false) . '>Evelyn Claire</option>
                    <option value="GraceKelly, grace kelly" ' . selected($value, 'grace_kelly', false) . '>Grace Kelly</option>
                    <option value="p54k1, jen psaki" ' . selected($value, 'jen_psaki', false) . '>Jen Psaki</option>
                    <option value="W3DDDN3SD4Y, jenna ortega" ' . selected($value, 'jenna_ortega', false) . '>Jenna Ortega</option>
                    <option value="jenn1f1850, jennifer connelly" ' . selected($value, 'jennifer_connelly', false) . '>Jennifer Connelly</option>
                    <option value="KateKuray, kate kuray" ' . selected($value, 'kate_kuray', false) . '>Kate Kuray</option>
                    <option value="KateMara2, kate mara" ' . selected($value, 'kate_mara', false) . '>Kate Mara</option>
                    <option value="Katr1n4K41f01, katrina kaif" ' . selected($value, 'katrina_kaif', false) . '>Katrina Kaif</option>
                    <option value="klebr0ck, kelly lebrock" ' . selected($value, 'kelly_lebrock', false) . '>Kelly LeBrock</option>
                    <option value="knightley3, kiera knightly" ' . selected($value, 'kiera_knightly', false) . '>Kiera Knightly</option>
                    <option value="krst3w4rt, kristen stewart" ' . selected($value, 'kristen_stewart', false) . '>Kristen Stewart</option>
                    <option value="GITSKrystenR, krysten ritter" ' . selected($value, 'jessica_jones', false) . '>Krysten Ritter</option>
                    <option value="Koh_KylieJenner, kylie jenner" ' . selected($value, 'kylie_jenner', false) . '>Kylie Jenner</option>
                    <option value="Mala1k44r0r401, malaika arora" ' . selected($value, 'malaika_arora', false) . '>Malaika Arora</option>
                    <option value="milak1, mila kunis" ' . selected($value, 'mila_kunis', false) . '>Mila Kunis</option>
                    <option value="w00ds, naomi woods" ' . selected($value, 'naomi_woods', false) . '>Naomi Woods</option>
                    <option value="natyport, natalie portman" ' . selected($value, 'natalie_portman', false) . '>Natalie Portman</option>
                    <option value="pr1nc355d1, princess diana" ' . selected($value, 'princess_diana', false) . '>Princess Diana</option>
                    <option value="diana, princess diana" ' . selected($value, 'princess_diana2', false) . '>Princess Diana 2</option>
                    <option value="r4ch3lmc4dd4ms, rachel mcadams" ' . selected($value, 'rachel_mcadams', false) . '>Rachel McAdams</option>
                    <option value="54andr4, sandra bullock" ' . selected($value, 'sandra_bullock', false) . '>Sandra Bullock</option>
                    <option value="sarasampaio, sara sampaio" ' . selected($value, 'sara_sampaio', false) . '>Sara Sampaio</option>
                    <option value="SiofraCipher, siofra cipher" ' . selected($value, 'siofra_cipher', false) . '>Síofra Cipher</option>
                    <option value="s0fv3rgara, sofia vergara" ' . selected($value, 'sofia_vergara', false) . '>Sofia Vergara</option>
                    <option value="zoeyd1, zooey deschanel" ' . selected($value, 'zooey_deschanel', false) . '>Zooey Deschanel</option>                    
                    <!-- Add more options here -->
                ';
            }

            if ($field === 'style_demo') {
                $output .= '
                    <option value="" disabled selected>Select ' . $label . '</option>
                    <option value=" " ' . selected($value, 'no_character', false) . '>None</option>
                    <option value="MAXFIELDPARRISH" ' . selected($value, 'maxfield_parrish', false) . '>Maxfield Parrish</option>
                    <option value="CLASSICSTYLE" ' . selected($value, 'classical_style', false) . '>Classical</option> 
                    <option value="NOIRKINO" ' . selected($value, 'film_noir', false) . '>Film Noir</option>
                    <option value="uncannime" ' . selected($value, 'uncannime', false) . '>Anime</option>
                    <option value="magical-girly" ' . selected($value, 'magical_girly', false) . '>Magical Girly</option>
                    <option value="rz-purepastel-21, pastel colors" ' . selected($value, 'pure_pastel', false) . '>Pure Pastel</option> 
                    <option value="DEN_barbuccisketch, sketch" ' . selected($value, 'barbucci_sketch', false) . '>Barbucci Sketch</option>
                    <option value="DEN_barbucci_artstyle, cartoon, toon" ' . selected($value, 'barducci_cartoon', false) . '>Barbucci Cartoon</option>
                    <option value="Old-Fashioned, old fashioned" ' . selected($value, 'old_fashioned', false) . '>Old Fashioned</option>
                    <option value="GIALLOSTYLE, italian horror film" ' . selected($value, 'giallo_style', false) . '>Italian Horror Film</option>
                    <!-- Add more options here -->
                ';
            }

            $output .= '
                </select>
                <p class="character-value">' . esc_html($value) . '</p>
            </div>
        ';
        } elseif ($field === 'description_demo') {
            $output .= '
            <div class="form-column">
                <label for="' . $field . '" title="Enter your character ' . $label . '">
                    ' . $label . '
                    <span class="help-icon" title="Help content for ' . $field . ' field" data-field="' . $field . '"></span>
                </label>
                <input type="text" name="' . $field . '" value="' . esc_attr($value) . '" placeholder="' . $label . '">
                <p class="character-value">' . esc_html($value) . '</p>
            </div>
        ';
        } else {
            $output .= '
            <div class="form-column">
                <label for="' . $field . '" title="Enter your character ' . $label . '">
                    ' . $label . '
                    <span class="help-icon" title="Help content for ' . $field . ' field" data-field="' . $field . '"></span>
                </label>';

            $output .= '
                <input type="text" name="' . $field . '" value="' . esc_attr($value) . '" placeholder="' . $label . '">
                <p class="character-value">' . esc_html($value) . '</p>
            </div>
        ';
        }
    }

    $output .= '
    </div> 
            <div class="form-column save-column">
                <input type="submit" name="save_character" value="Save" class="save-button">
                <input type="submit" name="reload_character" value="Reload" class="reload-button">
                <input type="submit" name="clear_character" value="Clear" class="clear-button">
            </div>
        </div>
    </form>
    </div>
    ';

    // Add nonce field
    $output .= wp_nonce_field('characterdemo_nonce_action', 'characterdemo_nonce', true, false);

    return $output;
}

add_shortcode('characterdemo', 'characterdemo_form_shortcode');

function save_characterdemo_form() {
    $form_data = array();
    foreach ($_POST["form_data"] as $item) {
        $form_data[$item['name']] = $item['value'];
    }

    // Access the global $wpdb object
    global $wpdb;

    // Retrieve the current user ID
    $user_id = get_current_user_id();

    // Retrieve the meta values
    $fields = array(
        'photo_demo' => 'Photo',
        'name_demo' => 'Name',
        'personality_demo' => 'Personality',
        'body_demo' => 'Body',
        'character_demo' => 'Character',
        'loves_demo' => 'Loves',
        'hates_demo' => 'Hates',
        'action_demo' => 'Action',
        'clothes_demo' => 'Clothes',
        'background_demo' => 'Background',
        'style_demo' => 'Style',
        'species_demo' => 'Species',
        'ethnicity_demo' => 'Ethnicity',
        'sex_demo' => 'Sex',
        'sexuality_demo' => 'Sexuality',
        'age_demo' => 'Age 18+',
        'description_demo' => 'Description',
    );

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'characterdemo_nonce_action')) {
        error_log('Nonce check failed');
        die;
    }

    // Update the meta values with the submitted form data
    foreach ($fields as $field => $label) {
        if (isset($form_data[$field])) { // Check if the form field is set
            $new_value = sanitize_text_field($form_data[$field]);
            $old_value = get_user_meta($user_id, 'character_' . $field, true);

            if ($new_value != $old_value) {
                $updated = update_user_meta($user_id, 'character_' . $field, $new_value);

                if (!$updated) {
                    error_log("Failed to update '$field' with value $new_value");
                    if ($wpdb->last_error) {
                        $last_error = $wpdb->last_error;
                        error_log("Last database error: $last_error");
                    }
                }
            }
        }
    }
    wp_die();
}

add_action("wp_ajax_characterdemoAjax", "save_character_form");
add_action("wp_ajax_nopriv_characterdemoAjax", "save_character_form");

function upload_characterdemo_photo() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'characterdemo_nonce_action')) {
        error_log('Nonce check failed');
        die;
    }

    if (!isset($_FILES['character_photo']) || !is_array($_FILES['character_photo'])) {
        error_log('No character photo file uploaded');
        die;
    }

    $file = $_FILES['character_photo_demo'];
    $upload_dir = wp_upload_dir();
    $file_name = wp_unique_filename($upload_dir['path'], $file['name']);

    if (wp_mkdir_p($upload_dir['path'])) {
        $file_path = $upload_dir['path'] . '/' . $file_name;
    } else {
        $file_path = $upload_dir['basedir'] . '/' . $file_name;
    }

    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Update the character_photo meta value
        $user_id = get_current_user_id();
        update_user_meta($user_id, 'character_photo_demo', $upload_dir['url'] . '/' . $file_name);
        echo 'success';
    } else {
        echo 'error';
    }

    wp_die();
}

add_action("wp_ajax_upload_characterdemo_photo", "upload_characterdemo_photo");
add_action("wp_ajax_nopriv_upload_characterdemo_photo", "upload_characterdemo_photo");

function clear_meta_keysdemo() {
    // Check nonce for security
    check_ajax_referer('characterdemo_nonce_action', 'nonce');

    // Access the global $wpdb object
    global $wpdb;

    // Retrieve the current user ID
    $user_id = get_current_user_id();

    // Retrieve the meta values
    $fields = array(
        'photo_demo',
        'name_demo',
        'personality_demo',
        'body_demo' => 'Body',
        'character_demo',
        'loves_demo',
        'hates_demo',
        'action_demo',
        'clothes_demo',
        'background_demo',
        'style_demo',
        'species_demo',
        'ethnicity_demo',
        'sex_demo',
        'sexuality_demo',
        'age_demo',
        'description_demo',
    );

    // Clear all meta keys here
    foreach ($fields as $field) {
        if (metadata_exists('user', $user_id, 'character_' . $field)) {
            $deleted = delete_user_meta($user_id, 'character_' . $field);
            if (!$deleted) {
                $last_error = $wpdb->last_error ? $wpdb->last_error : "Deletion returned false, but no last error was found.";
                error_log("Failed to reset '$field'. Database error: " . $last_error);
            } else {
                error_log("Successfully reset '$field'.");
            }
        }
    }

    // Send a response back to the client
    echo 'Meta keys cleared and reset';
    wp_die(); // this is required to terminate immediately and return a proper response
}

add_action('wp_ajax_clear_meta_keysdemo', 'clear_meta_keysdemo');
add_action('wp_ajax_nopriv_clear_meta_keysdemo', 'clear_meta_keysdemo');


?>