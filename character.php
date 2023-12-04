<?php

function character_form_shortcode() {
    // Access the global $wpdb object
    global $wpdb;

    // Retrieve the current user ID
    $user_id = get_current_user_id();

    // Retrieve the meta values
    $fields = array(
        'photo' => 'Photo',
        'name' => 'Name',
        'personality' => 'Personality',
        'body' => 'Body',
        'character' => 'Character',
        'loves' => 'Loves',
        'hates' => 'Hates',
        'action' => 'Action',
        'clothes' => 'Clothes',
        'background' => 'Background',
        'style' => 'Style',
        'species' => 'Species',
        'ethnicity' => 'Ethnicity',
        'sex' => 'Sex',
        'sexuality' => 'Sexuality',
        'age' => 'Age 18+',
        'description' => 'Description',
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

    // Retrieve the character_photo meta value from user metadata
    $user_image_url = get_user_meta($user_id, 'character_photo', true);

    // Initialize the $output variable
    $output = '';

    // HTML
    $output .= '
    <div class="magic-form-container">
    <button class="magic-form-icon" id="magic-form-button" title="Toggle Form">
      <i class="fa-solid fa-user"></i>
    </button>
</div>

<div class="character-container">
    <div class="character-form-container">
    <form class="character-form" method="post" action="" enctype="multipart/form-data">
      <div class="circle">
        <img class="profile-pic" src="' . ($user_image_url ? $user_image_url : "https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg") . '">
        <div class="p-image">
            <i class="fa fa-camera upload-button" title="Upload Photo"></i>
            <input class="file-upload" type="file" name="character_photo" accept="image/*" title="Select Photo"/>
        </div>
      </div>
      <div class="form-row">
        ';     

    foreach ($fields as $field => $label) {
        if ($field === 'photo') {
            continue; // Skip displaying the photo field
        }

        $value = get_user_meta($user_id, 'character_' . $field, true);

        if ($field === 'action' || $field === 'character' || $field === 'clothes' || $field === 'background' || $field === 'style') {
            $output .= '
            <div class="form-column">
                <label for="' . $field . '" title="Select the character ' . $label . '">
                    ' . $label . '
                    <span class="help-icon" title="Help content for ' . $field . ' field" data-field="' . $field . '"></span>
                </label>
                <select name="' . $field . '">
        ';
    
            if ($field === 'clothes') {
                $output .= '
                    <option value="" disabled selected>Select ' . $label . '</option>
                    <option value=" " ' . selected($value, 'no_clothes', false) . '>None</option>     
                    <option value="armani, fashion" ' . selected($value, 'armani_fashion', false) . '>Armani</option>
                    <option value="kkw-cloth-ar, armored clothing" ' . selected($value, 'armored_clothing', false) . '>Armored Clothing</option>    
                    <option value="heibai, lingerie" ' . selected($value, 'heibai_bw_lingerie', false) . '>B&W Lingerie</option>   
                    <option value="jeansheels, jeans, high heels" ' . selected($value, 'jeans_heels', false) . '>Denim & Heels</option>
                    <option value="suijing, diamonds, sparkling" ' . selected($value, 'suijing_crystal', false) . '>Diamonds</option>
                    <option value="longpao, dragon robe" ' . selected($value, 'longpao_dragon_robe', false) . '>Dragon Robe</option>   
                    <option value="n3t0p" ' . selected($value, 'n3t0p_fishnet', false) . '>Fishnet</option>          
                    <option value="RFKTR_fshnsta, fashion" ' . selected($value, 'rfktr_fashionista', false) . '>Hot Couture</option>
                    <option value="zhubao, lingerie" ' . selected($value, 'zhubao_lingerie', false) . '>Lingerie</option>
                    <option value="mulpty2, underwear" ' . selected($value, 'modest_underwear', false) . '>Modest Underwear</option>
                    <option value="nvdi, oriental princess" ' . selected($value, 'nvdi_oriental_princess', false) . '>Oriental Princess</option>
                    <option value="neiyi, oriental robe" ' . selected($value, 'neiyi_oriental_robe', false) . '>Oriental Robe</option>
                    <option value="p0k13s, shirt, nipples" ' . selected($value, 'pointy_nipples', false) . '>Pointy Nipples</option>
                    <option value="vksw34t1, sweater dress" ' . selected($value, 'sweater_dress', false) . '>Sweater Dress</option>   
                    <option value="kkw-cloth-trleo" ' . selected($value, 'transparent_leotard', false) . '>Transparent Leotard</option>                     
                    <!-- Add more options here -->
                ';
            } 
            
            elseif ($field === 'action') {
                $output .= '
                    <option value="" disabled selected>Select ' . $label . '</option>
                    <option value=" " ' . selected($value, 'no_species', false) . '>None</option>
                    <option value="lora:ahegao,rolling_eyes:1, ahegao, rolling eyes" ' . selected($value, 'eye_roll', false) . '>Eye Roll</option>
                    <option value="HDA_female masturbation:1.2" ' . selected($value, 'female_masturbation', false) . '>Masturbation</option>
                    <option value="lora:middlefinger-10:1.6, middle finger" ' . selected($value, 'middle_finger', false) . '>Middle Finger</option>  
                    <option value="lora:OnALeash-v2:1.4, on a leash" ' . selected($value, 'bdsm_leash', false) . '>On A Leash</option>
                    <option value="oral-dildo:1.6, sucking on a dildo" ' . selected($value, 'oral_dildo', false) . '>Oral Dildo</option>
                    <option value="lora:ftm-v0:1.2, sticks out tongue" ' . selected($value, 'sticking_tongue_out', false) . '>Tongue Out</option> 
                    <option value="under-boo:1:6, shirt, underboob" ' . selected($value, 'under_boob', false) . '>Underboob</option>
                    <!-- Add more options here -->
                ';
            } 

            if ($field === 'background') {
                $output .= '
                    <option value="" disabled selected>Select ' . $label . '</option>
                    <option value=" " ' . selected($value, 'no_action', false) . '>None</option>
                    <option value="Clutter-Mechanical" ' . selected($value, 'mechanical_shop', false) . '>Mechanical Shop</option>
                    <option value="Clutter-Home" ' . selected($value, 'messy_room', false) . '>Messy Room</option>
                    <option value="changting, oriental scenery" ' . selected($value, 'changting_oriental_architecture', false) . '>Oriental Architecture</option>
            
                    <option value="lora:stuck_in_washing_machine_v0.1:1.5, ass, stuck, washing machine, bent over, from behind" ' . selected($value, 'washing_machine', false) . '>Stuck in Washing Machine</option>
                    <option value="lora:change-05:1.2, fitting room, changing room" ' . selected($value, 'fitting_room', false) . '>Fitting Room</option>
                    <option value="lora:bathtime-2:1.2, bathtub" ' . selected($value, 'bath_tub', false) . '>Bathtub</option>
                    <option value="lora:couch-10:1.2, sitting on couch" ' . selected($value, 'casting_couch', false) . '>Casting Couch</option>
                    <option value="lora:skyscraper-10:1.2, sky scraper" ' . selected($value, 'sky_scraper', false) . '>Skyscraper</option>
                    <option value="lora:jkUnderwaterShot:1.2, under water" ' . selected($value, 'under_water', false) . '>Underwater</option>
                    <option value="lora:planebathroom-10:1.2, airplane bathroom" ' . selected($value, 'airplane_bathroom', false) . '>Airplane Bathroom</option>
                    <!-- Add more options here -->
                ';
            }            
            
            elseif ($field === 'character') {
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
                    <option value="2ual1pa, dua lipa" ' . selected($value, 'dua_lipa', false) . '>Dua Lipa</option>                    
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
                    <option value="m0lly, molly ringwald" ' . selected($value, 'molly_ringwald', false) . '>Molly Ringwald</option>
                    <option value="w00ds, naomi woods" ' . selected($value, 'naomi_woods', false) . '>Naomi Woods</option>
                    <option value="natyport, natalie portman" ' . selected($value, 'natalie_portman', false) . '>Natalie Portman</option>
                    <option value="pr1nc355d1, princess diana" ' . selected($value, 'princess_diana', false) . '>Princess Diana</option>
                    <option value="diana, princess diana" ' . selected($value, 'princess_diana2', false) . '>Princess Diana 2</option>
                    <option value="r4ch3lmc4dd4ms, rachel mcadams" ' . selected($value, 'rachel_mcadams', false) . '>Rachel McAdams</option>
                    <option value="54andr4, sandra bullock" ' . selected($value, 'sandra_bullock', false) . '>Sandra Bullock</option>
                    <option value="sarasampaio, sara sampaio" ' . selected($value, 'sara_sampaio', false) . '>Sara Sampaio</option>
                    <option value="Sh4r0nT4t301-200, sharon tate" ' . selected($value, 'sharon_tate', false) . '>Sharon Tate</option>
                    <option value="SiofraCipher, siofra cipher" ' . selected($value, 'siofra_cipher', false) . '>Síofra Cipher</option>
                    <option value="s0fv3rgara, sofia vergara" ' . selected($value, 'sofia_vergara', false) . '>Sofia Vergara</option>
                    <option value="s0ph1al0ren, sofia loren" ' . selected($value, 'sophia_loren', false) . '>Sophia Loren</option>
                    <option value="zend4y4, zendaya" ' . selected($value, 'zendaya', false) . '>Zendaya</option> 
                    <option value="zoeyd1, zooey deschanel" ' . selected($value, 'zooey_deschanel', false) . '>Zooey Deschanel</option>                    
                    <!-- Add more options here -->
                ';
            }

            if ($field === 'style') {
                $output .= '
                    <option value="" disabled selected>Select ' . $label . '</option>
                    <option value=" " ' . selected($value, 'no_character', false) . '>None</option>
                    <option value="uncannime" ' . selected($value, 'uncannime', false) . '>Anime</option>
                    <option value="CLASSICSTYLE" ' . selected($value, 'classical_style', false) . '>Classical</option> 
                    <option value="NOIRKINO" ' . selected($value, 'film_noir', false) . '>Film Noir</option>
                    <option value="Style-GravityMagic" ' . selected($value, 'gravity_magic', false) . '>Gravity Magic</option>
                    <option value="GIALLOSTYLE" ' . selected($value, 'giallo_style', false) . '>Italian Horror Film</option>
                    <option value="magical-girly" ' . selected($value, 'magical_girly', false) . '>Magical Girly</option>
                    <option value="MAXFIELDPARRISH" ' . selected($value, 'maxfield_parrish', false) . '>Maxfield Parrish</option>
                    <option value="Old-Fashioned" ' . selected($value, 'old_fashioned', false) . '>Old Fashioned</option>
                    <option value="rz-purepastel-21" ' . selected($value, 'pure_pastel', false) . '>Pure Pastel</option> 
                    <!-- Add more options here -->
                ';
            }

            $output .= '
                </select>
                <p class="character-value">' . esc_html($value) . '</p>
            </div>
        ';
        } elseif ($field === 'description') {
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
    $output .= wp_nonce_field('character_nonce_action', 'character_nonce', true, false);

    return $output;
}

add_shortcode('character', 'character_form_shortcode');

function save_character_form() {
    $form_data = array();
    
    if (isset($_POST["form_data"])) {
      foreach ($_POST["form_data"] as $item) {
        $form_data[$item['name']] = $item['value'];
      }
    } 

    // Access the global $wpdb object
    global $wpdb;

    // Retrieve the current user ID
    $user_id = get_current_user_id();

    // Retrieve the meta values
    $fields = array(
        'photo' => 'Photo',
        'name' => 'Name',
        'personality' => 'Personality',
        'body' => 'Body',
        'character' => 'Character',
        'loves' => 'Loves',
        'hates' => 'Hates',
        'action' => 'Action',
        'clothes' => 'Clothes',
        'background' => 'Background',
        'style' => 'Style',
        'species' => 'Species',
        'ethnicity' => 'Ethnicity',
        'sex' => 'Sex',
        'sexuality' => 'Sexuality',
        'age' => 'Age',
        'description' => 'Description',
    );

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'character_nonce_action')) {
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
                } else {
                    // Log the successful update
                    error_log("Successfully updated '$field' with value $new_value");
                }
            }
        }
    }

    // Log a success message
    error_log("Form data has been saved successfully");

    wp_die();
}

add_action("wp_ajax_characterAjax", "save_character_form");
add_action("wp_ajax_nopriv_characterAjax", "save_character_form");

function upload_character_photo() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'character_nonce_action')) {
        error_log('Nonce check failed');
        die;
    }

    if (!isset($_FILES['character_photo']) || !is_array($_FILES['character_photo'])) {
        error_log('No character photo file uploaded');
        die;
    }

    $file = $_FILES['character_photo'];
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
        update_user_meta($user_id, 'character_photo', $upload_dir['url'] . '/' . $file_name);
        // Return the URL of the uploaded image
        echo json_encode(array('success' => true, 'imageURL' => $upload_dir['url'] . '/' . $file_name));
    } else {
        echo json_encode(array('success' => false));
    }

    wp_die();
}

add_action("wp_ajax_upload_character_photo", "upload_character_photo");
add_action("wp_ajax_nopriv_upload_character_photo", "upload_character_photo");

function clear_meta_keys() {
    // Check nonce for security
    check_ajax_referer('character_nonce_action', 'nonce');

    // Access the global $wpdb object
    global $wpdb;

    // Retrieve the current user ID
    $user_id = get_current_user_id();

    // Retrieve the meta values
    $fields = array(
        'photo',
        'name',
        'personality',
        'body',
        'character',
        'loves',
        'hates',
        'action',
        'clothes',
        'background',
        'style',
        'species',
        'ethnicity',
        'sex',
        'sexuality',
        'age',
        'description',
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

add_action('wp_ajax_clear_meta_keys', 'clear_meta_keys');
add_action('wp_ajax_nopriv_clear_meta_keys', 'clear_meta_keys');


?>