<?php

// character_chatdemo.php

// Define the [character_chatdemo] shortcode

function character_chatdemo_shortcode($atts, $content = null) {
    // Contents of the character_chatdemo shortcode
    ob_start();
    ?>
    <!-- Character Chat Demo shortcode HTML contents -->
    <div class="character-chat-container">
        <?php echo do_shortcode('[characterdemo]'); ?>
        <?php echo do_shortcode('[chatdemo]'); ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('character_chatdemo', 'character_chatdemo_shortcode');

?>