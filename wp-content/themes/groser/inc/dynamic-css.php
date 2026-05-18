<?php

// File Security Check
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

function groser_theme_options_style() {

    //
    // Enqueueing StyleSheet file
    //
    wp_enqueue_style( 'groser-theme-custom-style', get_template_directory_uri() . '/assets/css/custom-style.css' );
    $css_output = '';
    $groser_primary_color = cs_get_option('mazc-primery-color'); 
    $groser_secondary_color = cs_get_option('mazc-secondry-color'); 
    
    //Theme Gradient COlor
    if(!empty($groser_primary_color)){
        $css_output .= '        
        :root {
            --base-color:  '.esc_attr($groser_primary_color).';
            }
        ';
    }

    if(!empty($groser_secondary_color)){
        $css_output .= '        
            :root {
                --color-primary:  '.esc_attr($groser_secondary_color).';
            }
        ';
    }
   

    wp_add_inline_style( 'groser-theme-custom-style', $css_output );

}
add_action( 'wp_enqueue_scripts', 'groser_theme_options_style' );
