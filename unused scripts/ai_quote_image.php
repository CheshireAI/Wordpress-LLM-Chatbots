<?php
// Define the shortcode
function ai_quote_image_shortcode() {

$pixabay_key = get_option('pixabay_key');

// Get a random image from Pixabay
$response = wp_remote_get( 'https://pixabay.com/api/', array(
    'timeout' => 5, // add timeout of 5 seconds
    'headers' => array(
        'Content-Type' => 'application/json',
    ),
    'body' => array(
        'key' => $pixabay_key,
        'q' => 'universe',
        'image_type' => '',
        'orientation' => '',
        'safesearch' => 'true',
        'category' => 'nature',
        'min_width' => '1440',
        'min_height' => '1440',
        'colors' => '',
        'per_page' => '10',
        'editors_choice' => 'yes',
        'page' => rand(1, 50)
    ),
) );

$response = json_decode( wp_remote_retrieve_body( $response ), true );

if (!$response || !isset($response['hits']) || count($response['hits']) == 0) {
    // handle the error - maybe show a default image or an error message
$image_url = 'https://faenomena.com/wp-content/plugins/quote-gen/default.jpg';

} else {
    $random_hit = $response['hits'][array_rand($response['hits'])];
    $image_url = isset($random_hit['largeImageURL']) ? $random_hit['largeImageURL'] : '';
    $image_width = isset($random_hit['imageWidth']) ? $random_hit['imageWidth'] : 2880;
    $image_height = isset($random_hit['imageHeight']) ? $random_hit['imageHeight'] : 2880;
}

// Initialize the imageUrl variable
$imageUrl = $image_url;

$html = '<div class="viewport-container" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;">';
$html .= '<div class="background-image-container" style="position: relative; width: 100%; height: 100%;">';
$html .= '<img id="background-image" src="' . $image_url . '" alt="" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">';
$html .= '</div>'; 
$html .= '</div>'; 
return $html;
}

add_shortcode('ai_quote_image', 'ai_quote_image_shortcode');
?>