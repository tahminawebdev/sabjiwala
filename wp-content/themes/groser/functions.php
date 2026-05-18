<?php
/**
 * groser functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package groser
 */


 /**
  * Define Core FIle
  */
  define( 'GROSER_THEME_DRI', get_template_directory() );
  define( 'GROSER_THEME_URI', get_template_directory_uri() );
  define( 'GROSER_CSS_PATH', GROSER_THEME_URI . '/assets/css' );
  define( 'GROSER_JS_PATH', GROSER_THEME_URI . '/assets/js' );
  define( 'GROSER_ICON_PATH', GROSER_THEME_URI . '/assets/fonts/fontawesome/css' );
  define( 'GROSER_IMG_PATH', GROSER_THEME_URI . '/assets/images' );
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function groser_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on groser, use a find and replace
		* to change 'groser' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'groser', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );
	remove_theme_support( 'widgets-block-editor' );

	add_image_size( 'groser-image-size1', 160, 160, true );
	add_image_size( 'groser-image-size2', 80, 94, true );
	add_image_size( 'groser-image-size3', 317, 240, true );
	add_image_size( 'groser-image-size4', 1450, 790, true );
	add_image_size( 'groser-image-size5', 60, 75, true );
	add_image_size( 'groser-image-size6', 183, 194, true );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
	* Enable support for Post Thumbnails on posts and pages.
	*
	* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	*/
	add_theme_support( 'post-thumbnails' );
   
	//Woocommerc
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
    
	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'groser' ),
			'category' => esc_html__( 'Category', 'groser' ),
		)
	);
     
	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'groser_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'groser_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function groser_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'groser_content_width', 640 );
}
add_action( 'after_setup_theme', 'groser_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function groser_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'groser' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'groser' ),
			'before_widget' => '<section id="%1$s" class="widget mt-40 %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget__title"><span>',
			'after_title'   => '</span></h2>',
		)
	);
	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer One', 'groser' ),
			'id'            => 'groser-footer-1',
			'description'   => esc_html__( 'Add Footer here.', 'groser' ),
			'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="title">',
			'after_title'   => '</h2>',
		)
	);
	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Two', 'groser' ),
			'id'            => 'groser-footer-2',
			'description'   => esc_html__( 'Add Footer here.', 'groser' ),
			'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="title">',
			'after_title'   => '</h2>',
		)
	);
	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Three', 'groser' ),
			'id'            => 'groser-footer-3',
			'description'   => esc_html__( 'Add Footer here.', 'groser' ),
			'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="title">',
			'after_title'   => '</h2>',
		)
	);
	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer Four', 'groser' ),
			'id'            => 'groser-footer-4',
			'description'   => esc_html__( 'Add Footer here.', 'groser' ),
			'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="title">',
			'after_title'   => '</h2>',
		)
	);
	register_sidebar(
		array(
			'name'          => esc_html__( 'Shop Siderbar', 'groser' ),
			'id'            => 'shop-sidebar-1',
			'description'   => esc_html__( 'Add Shop Sidebar here.', 'groser' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget__title"><span>',
			'after_title'   => '</span></h2>',
		)
	);
}
add_action( 'widgets_init', 'groser_widgets_init' );


/**
 *Google Font Load 
 */
if ( ! function_exists( 'groser_fonts_url' ) ) :
    /**
     * Register Google fonts for Blessing.
     */
    function groser_fonts_url() {
        $fonts_url     = '';
        $font_families = array();
        $subsets       = 'latin';


		if ( 'off' !== _x( 'on', 'Inter: on or off', 'groser' ) ) {
            $font_families[] = 'Inter:100,200,300,400,500,600,700,800,900';
        }
		if ( 'off' !== _x( 'on', 'Dosis: on or off', 'groser' ) ) {
            $font_families[] = 'Dosis:200,300,400,500,600,700,800';
        }
		if ( 'off' !== _x( 'on', 'DM Sans: on or off', 'groser' ) ) {
            $font_families[] = 'DM Sans:400,400i,500,500i,700,700i';
        }
		if ( 'off' !== _x( 'on', 'Quicksand: on or off', 'groser' ) ) {
            $font_families[] = 'Quicksand:300,400,500,600,700';
        }
		if ( 'off' !== _x( 'on', 'League Spartan: on or off', 'groser' ) ) {
            $font_families[] = 'League Spartan:100,200,300,400,500,600,700,800,900';
        }


        if ( $font_families ) {
            $fonts_url = add_query_arg( array(
                'family' => urlencode( implode( '|', $font_families ) ),
                'subset' => urlencode( $subsets ),
            ), 'https://fonts.googleapis.com/css' );
        }

        return esc_url_raw( $fonts_url );
    }
endif;


/**
 * Enqueue scripts and styles.
 */
function groser_scripts() {
	//Google Font Load	
	wp_enqueue_style( 'groser-custom-fonts', groser_fonts_url(), array(), null );

	//Load Css Files
	wp_enqueue_style( 'bootstrap', GROSER_CSS_PATH . '/bootstrap.min.css' );
	wp_enqueue_style( 'fontawesome-vi', GROSER_CSS_PATH . '/fontawesome.css' );
	wp_enqueue_style( 'owl', GROSER_CSS_PATH . '/owl.css' );
	wp_enqueue_style( 'animate', GROSER_CSS_PATH . '/animate.css' );
	wp_enqueue_style( 'metisMenu', GROSER_CSS_PATH . '/metisMenu.css' );
	wp_enqueue_style( 'uikit', GROSER_CSS_PATH . '/uikit.min.css' );
	wp_enqueue_style( 'slick', GROSER_CSS_PATH . '/slick.css' );
	wp_enqueue_style( 'magnific-popup', GROSER_CSS_PATH . '/magnific-popup.css' );
	wp_enqueue_style( 'groser-post-style', GROSER_CSS_PATH . '/post-style.css' );
	wp_enqueue_style( 'groser-main', GROSER_CSS_PATH . '/main.css' );
	
	if ( class_exists('WooCommerce') ) {
		wp_enqueue_style( 'groser-woocommerce', GROSER_CSS_PATH . '/woocommerce.css' );	
	}

	wp_enqueue_style( 'groser-style', get_stylesheet_uri(), array() );

	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script( 'bootstrap-bundle', GROSER_JS_PATH . '/bootstrap.bundle.min.js', array('jquery'), '5.0.2', true );
	wp_enqueue_script( 'slick', GROSER_JS_PATH . '/slick.js', array('jquery'), '5.0.2', true );
	wp_enqueue_script( 'backToTop', GROSER_JS_PATH . '/backToTop.js', array('jquery'), '5.0.2', true );
	wp_enqueue_script( 'owl', GROSER_JS_PATH . '/owl.js', array('jquery'), '5.0.2', true );
	wp_enqueue_script( 'uikit', GROSER_JS_PATH . '/uikit.min.js', array('jquery'), '5.0.2', true );
	wp_enqueue_script( 'resize-sensor', GROSER_JS_PATH . '/resize-sensor.min.js', array('jquery'), '5.0.2', true );
	wp_enqueue_script( 'theia-sticky-sidebar', GROSER_JS_PATH . '/theia-sticky-sidebar.min.js', array('jquery'), '5.0.2', true );
	wp_enqueue_script( 'wow', GROSER_JS_PATH . '/wow.min.js', array('jquery'), '5.0.2', true );
	wp_enqueue_script( 'touchspin', GROSER_JS_PATH . '/touchspin.js', array('jquery'), '5.0.2', true );
	wp_enqueue_script( 'countdown', GROSER_JS_PATH . '/countdown.js', array('jquery'), '5.0.2', true );
	wp_enqueue_script( 'parallax-scroll', GROSER_JS_PATH . '/parallax-scroll.js', array('jquery'), '5.0.2', true );
	wp_enqueue_script( 'jquery-magnific-popup', GROSER_JS_PATH . '/jquery.magnific-popup.min.js', array('jquery'), '5.0.2', true );
	wp_enqueue_script( 'metisMenu', GROSER_JS_PATH . '/metisMenu.min.js', array('jquery'), '5.0.2', true );
	wp_enqueue_script( 'groser-main', GROSER_JS_PATH . '/main.js', array('jquery'), '1.0', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'groser_scripts' );

/**
 * Implement the Custom Header feature.
 */
require GROSER_THEME_DRI . '/inc/custom-header.php';

/**
 * groser Core Functions
 */
require GROSER_THEME_DRI . '/inc/groser-functions.php';

/**
 * Cs Framework Functions
 */
require GROSER_THEME_DRI . '/inc/cs-framework-functions.php';

/**
 * Custom template tags for this theme.
 */
require GROSER_THEME_DRI . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require GROSER_THEME_DRI . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require GROSER_THEME_DRI . '/inc/customizer.php';

/**
 * Customizer additions.
 */
require GROSER_THEME_DRI . '/lib/ocdi/functions.php';

/**
 * Dynamic Css.
 */
require GROSER_THEME_DRI . '/inc/dynamic-css.php';

/**
 * Customizer additions.
 */
require GROSER_THEME_DRI . '/lib/plugin-activation.php';

/**
 * navwalker
 */
require GROSER_THEME_DRI . '/inc/class-wp-groser-navwalker.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require GROSER_THEME_DRI . '/inc/jetpack.php';
}

/**
 * Load Jetpack compatibility file.
 */

function groser_woo_theme_init(){
	$groser_exlude_hooks = require GROSER_THEME_DRI . '/inc/remove_actions.php';
	foreach( $groser_exlude_hooks as $k => $v )
	{
		foreach( $v as $value )
		remove_action( $k, $value[0], $value[1] );
	}

}
add_action( 'init', 'groser_woo_theme_init');



