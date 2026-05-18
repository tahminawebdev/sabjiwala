<?php
$top_text = cs_get_option('top_text');
$tracking_text = cs_get_option('tracking_text');
$tracking_link = cs_get_option('tracking_link');
$phone_no = cs_get_option('phone_no');
$contact_info_items = cs_get_option('contact_info_items');
$header_social_icons = cs_get_option('header_social_icons');
$header_top_bar_enable = cs_get_option('header_top_bar_enable');
$top_wislist_link = cs_get_option('top_wislist_link');
$top_checkout_link = cs_get_option('top_checkout_link');
$support_title = cs_get_option('support_title');
$phone_icon = cs_get_option('phone_icon');
$store_location_text = cs_get_option('store_location_text');
$store_location_link = cs_get_option('store_location_link');
$header_top_bar_enable = cs_get_option('header_top_bar_enable');
$header_heart_count = cs_get_option('header_heart_count');
?>
<header class="header viando_header-six">
    <?php if($header_top_bar_enable == true):?>
    <div class="header__top-wrap">
        <div class="container">
            <div class="header__top">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="left-box d-flex">
                        <ul class="header__top-left ul_li">
                            <?php if(!empty($top_text)):?>
                                <li><?php echo wp_kses($top_text, true)?></li>
                            <?php endif;?>
                            <?php if(!empty($tracking_text)):?>
                                <li><img src="<?php echo esc_url(get_template_directory_uri());?>/assets/img/cart-icon-1.svg" alt="<?php esc_attr_e( 'track', 'groser' );?>"> <a href="<?php echo esc_url($tracking_link);?>"><?php echo wp_kses($tracking_text, true)?></a></li>
                            <?php endif;?>
                        </ul>
                    </div>
                    <div class="right-box">
                        <ul class="header__top-right ul_li">
                            <?php if(!empty($top_wislist_link['text'])):?>
                                <li><a href="<?php echo esc_url($top_wislist_link['url']);?>"><?php echo esc_html($top_wislist_link['text']);?></a></li>
                            <?php endif;?>
                            <?php if(!empty($top_checkout_link['text'])):?>
                                <li><a href="<?php echo esc_url($top_checkout_link['url']);?>"><?php echo esc_html($top_checkout_link['text']);?></a></li>
                            <?php endif;?>
                            <li>
                                <div class="header__language">
                                    <?php groser_currency_name();?>
                                </div>
                            </li>
                            <li>
                                <div class="header__language">
                                    <?php groser_languages_name();?>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <?php endif;?>
    <div class="container">
        <div class="header__middle ul_li_between">
            <!-- Left Box -->
            <div class="left-box d-flex">
                <div class="header__logo">
                    <?php groser_logo();?>
                </div>
                <?php if(!empty($phone_no) || !empty($support_title)):?>
                    <div class="header__info-item ul_li">
                        <?php if(!empty($phone_icon['url'])):?>
                        <div class="icon">
                            <img src="<?php echo esc_url($phone_icon['url']);?>" alt="<?php echo esc_attr($phone_icon['alt']);?>">
                        </div>
                        <?php endif;?>
                        <div class="content">
                            <h4><?php echo wp_kses($phone_no, true)?></h4>
                            <?php if(!empty($support_title)):?>
                                <span><?php echo wp_kses($support_title, true)?></span>
                            <?php endif;?>
                        </div>
                    </div>
                <?php endif;?>
            </div>

            <!-- Right Box -->
            <div class="right-box d-flex">

                <form class="header__search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php if (class_exists('WooCommerce')): ?>
                        <input type="hidden" value="product" name="post_type">
                    <?php endif;?>
                    <input type="text"  name="search" placeholder="<?php esc_attr_e( 'I’m searching for...', 'groser' );?>" value="<?php the_search_query();?>">
                    <button><i class="fas fa-search"></i></button>
                </form>
                <?php if(!empty($store_location_text) || !empty($store_location_link)):?>
                <div class="form-group">
                    <div class="location-form-group">
                        <a class="store__location" href="<?php echo esc_url($store_location_link);?>"><img src="<?php echo esc_url(get_template_directory_uri());?>/assets/img/location-1.svg" alt="<?php esc_attr_e( 'Location', 'groser' );?>" /> <?php echo esc_html($store_location_text);?></a>
                    </div>
                </div>
                <?php endif;?>
            </div>

        </div>
    </div>
    <div class="header__wrap" data-uk-sticky="top: 250; animation: uk-animation-slide-top;">
        <div class="container">
            <div class="header__main ul_li" >
                <div class="header__logo">
                    <?php groser_logo();?>
                </div>
                <div class="header__category">
                    <a class="header__category-nav" href="#!"><img class="bar" src="<?php echo esc_url(get_template_directory_uri());?>/assets/img/bars.svg" alt="<?php esc_html_e( 'Select categories', 'groser' );?>"><?php esc_attr_e( 'Select Categories', 'groser' );?><i class="fas fa-chevron-down"></i></a>
                </div>
                <div class="hamburger_menu d-lg-none">
                    <a href="javascript:void(0);" class="active">
                        <div class="icon bar">
                            <span><i class="fal fa-bars"></i></span>
                        </div>
                    </a>
                </div>
                <div class="main-menu navbar navbar-expand-lg">
                    <nav class="main-menu__nav collapse navbar-collapse">
                        <?php groser_menu_register();?>
                    </nav>
                </div>
                <div class="header__main-right ul_li">

                    <div class="header__icons ul_li">
                        <?php if(class_exists('WPCleverWoosw') && $header_heart_count == true) : ?>
                        <div class="icon wishlist-icon">
                            <?php groser_wishlist_count();?>
                        </div>
                        <?php endif;?>
                        <?php
                        if ( class_exists('WooCommerce') ) { ?>
                        <div class="cart_btn ul_li">
                            <div class="icon shopping-bag">
                                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'cart' ) ) );?>">
                                    <img src="<?php echo esc_url(get_template_directory_uri());?>/assets/img/cart-icon-2.svg" alt="<?php esc_attr_e( 'Cart Count', 'groser' );?>">
                                    <?php $items_count = WC()->cart->get_cart_contents_count(); ?>
                                    <span class="count" id="mini-cart-count"><?php echo esc_html($items_count) ? $items_count : '&nbsp;'; ?></span>
                                </a>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ( class_exists('WooCommerce') ) {?>
                        <div class="icon">
                            <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) );?>"><img src="<?php echo esc_url(get_template_directory_uri());?>/assets/img/icon/user.svg" alt="<?php esc_attr_e( 'User', 'groser' );?>"></a>
                        </div>
                        <?php } ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</header>
<?php groser_mobile_menu();?>