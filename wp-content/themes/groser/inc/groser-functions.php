<?php
/**
 * groser Preloader
 *
 * @return void
 */
function groser_preloader(){ ?>
<!-- preloder start  -->
<div class="preloder_part">
    <div class="spinner">
        <div class="dot1"></div>
        <div class="dot2"></div>
    </div>
</div>
<!-- preloder end  -->
<?php
}

/**
 * Undocumented function
 *
 * @return void
 */
function groser_menu_register(){
    echo str_replace(['sub-menu'], ['submenu'], wp_nav_menu( array(
        'echo'           => false,
        'theme_location' => 'primary',
        'container'=>false,
        'fallback_cb'    => 'Groser_Bootstrap_Navwalker::fallback',
    )) );
}
/**
 * Undocumented function
 *
 * @return void
 */
function groser_menu_two_register(){
    echo str_replace(['menu-item-has-children', 'sub-menu'], ['dropdown', 'dropdown-menu clearfix'], wp_nav_menu( array(
        'echo'           => false,
        'theme_location' => 'primary',
        'menu_id'        =>'main-nav',
        'menu_class'     =>'nav navbar-nav clearfix',
        'container'=>false,
        'fallback_cb'    => 'Groser_Bootstrap_Navwalker::fallback',
    )) );
}

/**
 * groser Mobile Menu
 *
 * @return void
 */
function groser_mobile_menu_register(){
    echo str_replace(['menu-item-has-children'], ['dropdown'], wp_nav_menu( array(
        'echo'           => false,
        'theme_location' => 'primary',
        'menu_id' => 'mobile-menu-active',
        'container'=>false,
        'fallback_cb'    => 'Groser_Bootstrap_Navwalker::fallback',
    )) );
}

function groser_cate_menu_register(){
    echo str_replace(['menu-item-has-children', 'sub-menu'], ['list-sub-category', 'sub-category'], wp_nav_menu( array(
        'echo'           => false,
        'theme_location' => 'category',
        'menu_id' => 'cate-menu-active',
        'container'=>false,
        'fallback_cb'    => 'Groser_Bootstrap_Navwalker::fallback',
    )) );
}
/**
 * groser Header
 */

function groser_header_option(){
    if('header-style-one' === groser_site_header()){
        get_template_part('template-parts/header/header', 'one');
    }elseif('header-style-two' === groser_site_header()){
        get_template_part('template-parts/header/header', 'two');
    }elseif('header-style-three' === groser_site_header()){
        get_template_part('template-parts/header/header', 'three');
    }else{
        get_template_part('template-parts/header/header', 'one');
    }

 }

 /**
 * groser Footer function
 *
 * @return void
 */
function groser_footer_options(){
    get_template_part('template-parts/footer/footer', 'five');
}

 /**
 * Groser Page Breadcrumb
 *
 * @return void
 */
function groser_page_breadcrumb(){
?>
    <!-- breadcrumb start -->
    <section class="breadcrumb-area">
        <div class="container">
            <div class="groser-breadcrumb breadcrumbs">
                <?php echo groser_the_breadcrumb();?>
            </div>
        </div>
    </section>
    <!-- breadcrumb end -->
<?php
}

/**
 * groser Mobile Menu
 *
 * @return void
 */
function groser_mobile_menu(){ ?>
    <aside class="slide-bar">
        <div class="close-mobile-menu">
        <a href="javascript:void(0);"><i class="fal fa-times"></i></a>
    </div>
    <nav class="side-mobile-menu">
        <div class="header-mobile-search">
            <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <?php if (class_exists('WooCommerce')): ?>
                    <input type="hidden" value="product" name="post_type">
                <?php endif;?>
                <input type="text"  name="search" placeholder="<?php esc_attr_e( 'Search Keywords', 'groser' );?>" value="<?php the_search_query();?>">
                <button><i class="fas fa-search"></i></button>
            </form>
        </div>
        <?php groser_mobile_menu_register();?>
    </nav>
</aside>
<?php
}


/**
 * groser Post Loop
 *
 * @return void
 */
function groser_post_loop(){
    if ( have_posts() ) :

        /* Start the Loop */
        while ( have_posts() ) :
            the_post();

            /*
            * Include the Post-Type-specific template for the content.
            * If you want to override this in a child theme, then include a file
            * called content-___.php (where ___ is the Post Type name) and that will be used instead.
            */
            get_template_part( 'template-parts/content', get_post_format() );

        endwhile;?>
        <div class="pagination_wrap pt-50">
            <?php groser_pagination();?>
        </div>


    <?php else :

        get_template_part( 'template-parts/content', 'none' );

    endif;
}

/**
 * groser Single Loop
 *
 * @return void
 */
function groser_single_post_loop(){
    while ( have_posts() ) :
        the_post();

        get_template_part( 'template-parts/content', 'single' );

        groser_single_post_pagination();

        // If comments are open or we have at least one comment, load up the comment template.
        if ( comments_open() || get_comments_number() ) :
            comments_template();
        endif;

    endwhile; // End of the loop.
}

function groser_archive_loop(){
    if ( have_posts() ) :
        /* Start the Loop */
        while ( have_posts() ) :
            the_post();

            /*
             * Include the Post-Type-specific template for the content.
             * If you want to override this in a child theme, then include a file
             * called content-___.php (where ___ is the Post Type name) and that will be used instead.
             */
            get_template_part( 'template-parts/content', get_post_type() );

        endwhile;

        the_posts_navigation();

    else :

        get_template_part( 'template-parts/content', 'none' );

    endif;
}

function groser_search_loop(){
    if ( have_posts() ) :
        /* Start the Loop */
        while ( have_posts() ) :
            the_post();

            /**
             * Run the loop for the search to output the results.
             * If you want to overload this in a child theme then include a file
             * called content-search.php and that will be used instead.
             */
            get_template_part( 'template-parts/content', 'search' );

        endwhile;

        the_posts_navigation();

    else :

        get_template_part( 'template-parts/content', 'none' );

    endif;
}

/**
 * 404 Error
 *
 * @return void
 */
function groser_error_page(){
    $error_code = cs_get_option('error_code');
    $error_title = cs_get_option('error_title');
    $error_info_title = cs_get_option('error_info_title');
    $error_button = cs_get_option('error_button');
    ?>
    <!-- error page start -->
	<section class="error__page pb-90">
		<div class="container">
			<div class="error__text text-center">
				<h1>
                    <?php
                        if(!empty($error_code)){
                            echo esc_html($error_code);
                        }else{
                            esc_html_e( '404', 'groser' );
                        }
                    ?>
                </h1>
				<h3>
                    <?php
                        if(!empty($error_title)){
                            echo esc_html($error_title);
                        }else{
                            esc_html_e( 'Oops... It looks like you ‘re lost !', 'groser' );
                        }
                    ?>
                </h3>
				<p>
                    <?php
                        if(!empty($error_info_title)){
                            echo esc_html($error_info_title);
                        }else{
                            esc_html_e( 'Oops! The page you are looking for does not exist. It might have been moved or deleted.', 'groser' );
                        }
                    ?>
                </p>
				<div class="go-back-btn mt-50">
					<a class="thm-btn thm-btn__2" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <span class="btn-wrap">
                        <span>
                            <?php
                                if(!empty($error_button)){
                                    echo esc_html($error_button);
                                }else{
                                    esc_html_e( 'Go Back Home', 'groser' );
                                }
                            ?>
                        </span>
                        <span>
                            <?php
                                if(!empty($error_button)){
                                    echo esc_html($error_button);
                                }else{
                                    esc_html_e( 'Go Back Home', 'groser' );
                                }
                            ?>
                        </span>
                    </span>
                    <i class="fas fa-long-arrow-right"></i></a>
				</div>
			</div>
		</div>
	</section>
	<!-- error page end -->
    <?php
}

/**
 * Footer Shape
 *
 * @return void
 */
function footer_shape(){
    $f_shape1 = cs_get_option('f_shape1');
    $f_shape2 = cs_get_option('f_shape2');
    $f_shape3 = cs_get_option('f_shape3');
    $f_shape4 = cs_get_option('f_shape4');
    $f_shape5 = cs_get_option('f_shape5');
?>
    <div class="footer__shape">
        <?php if(!empty($f_shape1['url'])):?>
            <img class="shape1" src="<?php echo esc_url($f_shape1['url']);?>" alt="<?php echo esc_attr($f_shape1['alt']);?>">
        <?php endif;?>
        <?php if(!empty($f_shape2['url'])):?>
            <img class="shape2" src="<?php echo esc_url($f_shape2['url']);?>" alt="<?php echo esc_attr($f_shape2['alt']);?>">
        <?php endif;?>
        <?php if(!empty($f_shape3['url'])):?>
            <img class="shape3" src="<?php echo esc_url($f_shape3['url']);?>" alt="<?php echo esc_attr($f_shape3['alt']);?>">
        <?php endif;?>
        <?php if(!empty($f_shape4['url'])):?>
            <img class="shape4" src="<?php echo esc_url($f_shape4['url']);?>" alt="<?php echo esc_attr($f_shape4['alt']);?>">
        <?php endif;?>
        <?php if(!empty($f_shape5['url'])):?>
            <img class="shape5" src="<?php echo esc_url($f_shape5['url']);?>" alt="<?php echo esc_attr($f_shape5['alt']);?>">
        <?php endif;?>
    </div>
<?php
}

function groser_footer_newsletter(){
    $newsl_title = cs_get_option('newsl_title');
    $newsl_text = cs_get_option('newsl_text');
    $newsl_shortcode = cs_get_option('newsl_shortcode');
    $newsletter_enable = cs_get_option('newsletter_enable');

    $newsletter_cls = '';
    if(groser_site_footer() == 'footer-style-two'){
        $newsletter_cls = 'bg-primary-3 text-white';
    }
    if($newsletter_enable == true):
?>
<div class="newslater newslater__bg">
    <div class="container">
        <div class="newslater__wrap ul_li <?php echo esc_attr($newsletter_cls);?>">
            <div class="newslater__content">
                    <h2><?php echo wp_kses($newsl_title, true)?></h2>
                    <p><?php echo wp_kses($newsl_text, true)?></p>
            </div>
            <div class="newslater__form">
                <?php echo do_shortcode($newsl_shortcode);?>
            </div>
        </div>
    </div>
</div>

<?php
    endif;
}

/**
 * groser Languages Name
 *
 * @return void
 */
function groser_languages_name(){
    $active_languages = cs_get_option('active_languages');
    $active_languages_link = cs_get_option('active_languages_link');
    $groser_languages = cs_get_option('groser_languages');
    if(!empty($groser_languages)):
?>
    <ul>
        <li><a href="<?php echo esc_url($active_languages_link);?>" class="lang-btn"><?php echo esc_html($active_languages);?> <i class="fas fa-chevron-down"></i></a>
            <ul class="lang_sub_list">
                <?php foreach($groser_languages as $lang):?>
                <li><a href="<?php echo esc_url($lang['link']);?>"><?php echo esc_html($lang['language_name']);?></a></li>
                <?php endforeach;?>
            </ul>
        </li>
    </ul>
<?php
endif;
}

/**
 * groser Languages Name
 *
 * @return void
 */
function groser_currency_name(){
    $active_currency = cs_get_option('active_currency');
    $active_currency_link = cs_get_option('active_currency_link');
    $groser_currencys = cs_get_option('groser_currencys');
    if(!empty($groser_currencys)):
?>
    <ul>
        <li><a href="<?php echo esc_url($active_currency_link);?>" class="lang-btn"><?php echo esc_html($active_currency);?> <i class="fas fa-chevron-down"></i></a>
            <ul class="lang_sub_list">
                <?php foreach($groser_currencys as $curr):?>
                    <li><a href="<?php echo esc_url($curr['link']);?>"><?php echo esc_html($curr['currency_name']);?></a></li>
                <?php endforeach;?>
            </ul>
        </li>
    </ul>
<?php
endif;
}
function groser_currency_two_name(){
    $active_currency = cs_get_option('active_currency');
    $active_currency_link = cs_get_option('active_currency_link');
    $groser_currencys = cs_get_option('groser_currencys');
    if(!empty($groser_currencys)):
?>
<select>
    <?php foreach($groser_currencys as $curr):?>
        <option><?php echo esc_html($curr['currency_name']);?></option>
    <?php endforeach;?>
</select>
<?php
endif;
}

function groser_home_category(){
    $vi_header_categorys = cs_get_option('vi_header_categorys');
    if(!empty($vi_header_categorys)):
?>
    <ul class="category ul_li">
        <?php foreach($vi_header_categorys as $vicat):?>
            <li><a href="<?php echo esc_url($vicat['cate_link']);?>"><span><img src="<?php echo esc_url($vicat['cate_img']['url']);?>" alt="<?php echo esc_attr($vicat['cate_img']['alt']);?>"></span><?php echo esc_html($vicat['category_name']);?></a></li>
        <?php endforeach;?>
    </ul>
<?php
endif;
}

function groser_home2_category(){
    $vi_header_categorys = cs_get_option('vi_header_categorys');
    if(!empty($vi_header_categorys)):
?>
<div class="vertical-menu-list category-nav">
    <ul class="category-nav__list list-unstyled">
        <?php foreach($vi_header_categorys as $vicat):?>
            <li><a href="<?php echo esc_url($vicat['cate_link']);?>"><span><img src="<?php echo esc_url($vicat['cate_img']['url']);?>" alt="<?php echo esc_attr($vicat['cate_img']['alt']);?>"></span><?php echo esc_html($vicat['category_name']);?></a></li>
        <?php endforeach;?>
    </ul>
</div>
<?php
endif;
}

/**
 * Groser Wishlist
 */
function groser_wishlist_count(){
	$header_heart_count = cs_get_option('header_heart_count');
    ?>
    <?php if(class_exists('WPCleverWoosw') && $header_heart_count == true) :
    $woosw_id = get_option( 'woosw_page_id' );
    ?>
    <a href="<?php echo esc_url(get_permalink($woosw_id)); ?>">
        <img src="<?php echo esc_url(get_template_directory_uri());?>/assets/img/icon/heart.svg" alt="<?php esc_attr_e( 'Heart Count', 'groser' );?>">
        <span class="count"><?php echo WPcleverWoosw::get_count(); ?></span>
    </a>
    <?php endif; ?>
    <?php
}
