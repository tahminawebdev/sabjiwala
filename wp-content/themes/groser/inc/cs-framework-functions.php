<?php

/**
 *
 * Get groser Theme options
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! function_exists( 'cs_get_option' ) ) {
    function cs_get_option( $option = '', $default = null ) {
        $options = get_option( 'groser_theme_options' ); // Attention: Set your unique id of the framework
        return ( isset( $options[$option] ) ) ? $options[$option] : $default;
    }
}

/**
 *
 * Get get switcher option
 *  for theme options
 * @since 1.0.0
 * @version 1.0.0
 *
 */

if ( ! function_exists( 'cs_get_switcher_option' )) {

    function cs_get_switcher_option( $option = '', $default = null ) {
        $options = get_option( 'groser_theme_options' ); // Attention: Set your unique id of the framework
        $return_val =  ( isset( $options[$option] ) ) ? $options[$option] : $default;
        $return_val =  (is_null($return_val) || '1' == $return_val ) ? true : false;;
        return $return_val;
    }
}

if ( ! function_exists( 'cs_switcher_option' )) {

    function cs_switcher_option( $option = '', $default = null ) {
        $options = get_option( 'groser_theme_options' ); // Attention: Set your unique id of the framework
        $return_val =  ( isset( $options[$option] ) ) ? $options[$option] : $default;
        $return_val =  ( '1' == $return_val ) ? true : false;;
        return $return_val;
    }
}


/**
 * Function for get a metaboxes
 *
 * @param $prefix_key Required Meta unique slug
 * @param $meta_key Required Meta slug
 * @param $default Optional Set default value
 * @param $id Optional Set post id
 *
 * @return mixed
 */
function groser_get_meta( $prefix_key, $meta_key, $default = null, $id = '' ) {
    if ( !$id ) {
        $id = get_the_ID();
    }

    $meta_boxes = get_post_meta( $id, $prefix_key, true );
    return ( isset( $meta_boxes[$meta_key] ) ) ? $meta_boxes[$meta_key] : $default;
}

/**
 * Get Header layout
 *
 * @return string
 */
function groser_site_header() {
    $headers_layout = cs_get_option( 'header_glob_style', 'header-style-four' );
    if ( is_page() ) {
        $page_header = groser_get_meta( 'groser_page_meta', 'header_layout_pos', 'default' );

        if ( 'default' !== $page_header ) {
            $headers_layout = $page_header;
        }
    }

    return $headers_layout;
}


 /**
  * Site Logo
  */
function groser_logo(){ 
    $global_logo = cs_get_option('theme_logo');
    $page_main = groser_get_meta( 'groser_page_meta', 'page_logo', 'default' );
    ?>
    <?php if(!empty($page_main['url'])):?>
        <a class="groser__logo-item" href="<?php echo esc_url( home_url( '/' ) ); ?>" >
            <img src="<?php echo esc_url($page_main['url']);?>" alt="<?php echo esc_attr(get_bloginfo());?>">
        </a>
    <?php elseif(isset($global_logo['url']) && $global_logo['url']):?>
        <a class="groser__logo-item" href="<?php echo esc_url( home_url( '/' ) ); ?>" >
        <img src="<?php echo esc_url($global_logo['url']);?>" alt="<?php echo esc_attr(get_bloginfo());?>">
        </a>
    <?php else:?>
        <?php 
            if(has_custom_logo()){
                the_custom_logo();
            }else{ ?>
            <a class="groser__logo-item" href="<?php echo esc_url( home_url( '/' ) ); ?>" >
                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/img/logo.svg" alt="<?php esc_attr_e('Logo', 'groser'); ?>">
            </a>
        <?php    }
        ?>
    <?php endif;?>
<?php }


 /**
  * Site V5 Logo
  */
function groser_logo_v2(){ 
    $theme_v2_logo = cs_get_option('theme_v2_logo');
    $page_footer = groser_get_meta( 'groser_page_meta', 'page_logo', 'default' );
    ?>
    <?php if(!empty($page_footer['url'])):?>
        <a class="groser__logo-item" href="<?php echo esc_url( home_url( '/' ) ); ?>" >
            <img src="<?php echo esc_url($page_footer['url']);?>" alt="<?php echo esc_attr(get_bloginfo());?>">
        </a>
    <?php elseif(isset($theme_v2_logo['url']) && $theme_v2_logo['url']):?>
        <a class="groser__logo-item" href="<?php echo esc_url( home_url( '/' ) ); ?>" >
            <img src="<?php echo esc_url($theme_v2_logo['url']);?>" alt="<?php echo esc_attr(get_bloginfo());?>">
        </a>
    <?php else:?>
        <?php 
            if(has_custom_logo()){
                the_custom_logo();
            }else{ ?>
            <a class="groser__logo-item" href="<?php echo esc_url( home_url( '/' ) ); ?>" >
                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/img/logo-2.svg" alt="<?php esc_attr_e('Logo', 'groser'); ?>">
            </a>
        <?php    }
        ?>
    <?php endif;?>
<?php }

 /**
  * Site V5 Logo
  */
function groser_logo_v3(){ 
    $theme_v3_logo = cs_get_option('theme_v3_logo');
    $page_footer = groser_get_meta( 'groser_page_meta', 'page_logo', 'default' );
    ?>
    <?php if(!empty($page_footer['url'])):?>
        <a class="groser__logo-item" href="<?php echo esc_url( home_url( '/' ) ); ?>" >
            <img src="<?php echo esc_url($page_footer['url']);?>" alt="<?php echo esc_attr(get_bloginfo());?>">
        </a>
    <?php elseif(isset($theme_v3_logo['url']) && $theme_v3_logo['url']):?>
        <a class="groser__logo-item" href="<?php echo esc_url( home_url( '/' ) ); ?>" >
            <img src="<?php echo esc_url($theme_v3_logo['url']);?>" alt="<?php echo esc_attr(get_bloginfo());?>">
        </a>
    <?php else:?>
        <?php 
            if(has_custom_logo()){
                the_custom_logo();
            }else{ ?>
            <a class="groser__logo-item" href="<?php echo esc_url( home_url( '/' ) ); ?>" >
                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/img/white-logo.svg" alt="<?php esc_attr_e('Logo', 'groser'); ?>">
            </a>
        <?php    }
        ?>
    <?php endif;?>
<?php }


/**
 * Get Header layout
 *
 * @return string
 */
function groser_site_footer() {
    $footer_layout = cs_get_option( 'footer_glob_style', 'footer-style-five' );
    if ( is_page() ) {
        $page_header = groser_get_meta( 'groser_page_meta', 'footer_layout', 'default' );

        if ( 'default' !== $page_header ) {
            $footer_layout = $page_header;
        }
    }

    return $footer_layout;
}