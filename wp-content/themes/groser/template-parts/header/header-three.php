<?php 
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
?>
<header class="header viando_header-seven">
    <div class="container">
        <div class="header__middle ul_li_between">
        
            <!-- Left Box -->
            <div class="left-box d-flex">
                <div class="header__logo">
                    <?php groser_logo();?>
                </div>
            </div>
            
            <!-- Right Box -->
            <div class="right-box d-flex">
                
                <div class="form-group">
                    <div class="location-form-group">
                        <a class="store__location" href="<?php echo esc_url($store_location_link);?>"><img src="<?php echo esc_url(get_template_directory_uri());?>/assets/img/location-1.svg" alt="<?php esc_attr_e( 'Location', 'groser' );?>" /> <?php echo esc_html($store_location_text);?></a>
                    </div>
                </div>
                
                <?php groser_product_search();?>
                
                <div class="header__main-right ul_li">
                    
                    <div class="header__icons ul_li">
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
                        <div class="icon wishlist-icon">
                            <?php groser_wishlist_count();?>
                        </div>
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
    <div class="header__wrap" data-uk-sticky="top: 250; animation: uk-animation-slide-top;">
        <div class="container">
            <div class="header__main ul_li" >
                <div class="header__logo">
                    <?php groser_logo();?>
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
                    
                    <?php if(!empty($tracking_text)):?>
                    <div class="track-btn-box">
                        <a class="theme-btn track-btn" href="<?php echo esc_url($tracking_link);?>"><?php echo esc_html($tracking_text);?></a>
                    </div>
                    <?php endif;?>
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
            </div>
        </div>
    </div>
</header>
<?php groser_mobile_menu();?>