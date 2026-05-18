<?php
$top_text = cs_get_option('top_text');
$phone_no = cs_get_option('phone_no');
$support_icon = cs_get_option('support_icon');
$support_title = cs_get_option('support_title');
$tracking_text = cs_get_option('tracking_text');
$tracking_link = cs_get_option('tracking_link');
$top_wislist_link = cs_get_option('top_wislist_link');
$top_checkout_link = cs_get_option('top_checkout_link');
$email_title = cs_get_option('email_title');
$email_title = cs_get_option('email_title');
$email_id = cs_get_option('email_id');
?>
<header id="gr-header" class="gr-header-section">
    <div class="gr-header-top-content">
        <div class="container">
            <div class="gr-header-top-info-wrap d-flex justify-content-between">
                <div class="gr-header-top-info-item d-flex align-items-center">
                    <?php if(!empty($top_text)):?>
                    <div class="gr-header-top-slug">
                        <?php echo wp_kses($top_text, true)?>
                    </div>
                    <?php endif;?>
                    <?php if(!empty($tracking_text)):?>
                    <div class="gr-header-track-order">
                        <a href="<?php echo esc_url($tracking_link);?>"><i class="fas fa-luggage-cart"></i> <?php echo wp_kses($tracking_text, true)?></a>
                    </div>
                    <?php endif;?>
                </div>
                <div class="gr-header-top-list-area ul-li d-flex align-items-center">
                    <div class="gr-header-top-list">
                        <ul>
                            <?php if(!empty($top_wislist_link['text'])):?>
                                <li><a href="<?php echo esc_url($top_wislist_link['url']);?>"><?php echo esc_html($top_wislist_link['text']);?></a></li>
                            <?php endif;?>
                            <?php if(!empty($top_checkout_link['text'])):?>
                                <li><a href="<?php echo esc_url($top_checkout_link['url']);?>"><?php echo esc_html($top_checkout_link['text']);?></a></li>
                            <?php endif;?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="gr-header-search-cart-button-wrap">
        <div class="container">
            <div class="gr-header-search-cart-button-area d-flex align-items-center justify-content-between">
                <div class="gr-brand-logo">
                    <?php groser_logo();?>
                </div>
                <div class="gr-header-search-area position-relative">
                    <?php echo do_shortcode('[fibosearch]'); ?>
                </div>
                <?php if(!empty($phone_no) || !empty($support_title)):?>
                <div class="gr-header-cta-area d-flex align-items-center">
                    <div class="inner-icon">
                        <i class="<?php echo isset($support_icon) ? esc_attr($support_icon) : ''; ?>"></i>
                    </div>
                    <div class="inner-text">
                        <?php if(!empty($support_title)):?>
                        <span><?php echo wp_kses($support_title, true)?></span>
                        <?php endif;?>
                        <b><?php echo wp_kses($phone_no, true)?></b>
                    </div>
                </div>
                <?php endif;?>
                <div class="gr-header-cart-wishlist-btn d-flex align-items-center">
                    <?php if ( class_exists('WooCommerce') ) {?>
                    <div class="gr-header-action-btn">
                        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) );?>">
                        <button><i class="fas fa-user"></i></button>
                        </a>

                    </div>
                    <?php } ?>
                    <div class="gr-header-action-btn position-relative">
                    <a href="<?php echo esc_url(get_permalink($woosw_id)); ?>">
                        <?php
                        $header_heart_count = cs_get_option('header_heart_count');
                        ?>
                        <?php if(class_exists('WPCleverWoosw') && $header_heart_count == true) :
                        $woosw_id = get_option( 'woosw_page_id' );
                        ?>
                        <button><i class="fas fa-heart"></i></button>
                        <span class="top-tag position-absolute d-flex justify-content-center align-items-center"><?php echo WPcleverWoosw::get_count(); ?></span>
                        <?php endif;?>
                        </a>
                    </div>
                    <?php
                        if ( class_exists('WooCommerce') ) { ?>
                    <div class="gr-header-action-btn position-relative">
                        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'cart' ) ) );?>">
                            <button class="or-canvas-cart-trigger"><i class="fas fa-shopping-basket"></i></button>
                            <?php $items_count = WC()->cart->get_cart_contents_count(); ?>
                            <span class="top-tag position-absolute d-flex justify-content-center align-items-center"><?php echo esc_html($items_count) ? $items_count : '&nbsp;'; ?></span>
                        </a>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="mobile_menu position-relative">
                <div class="mobile_menu_button open_mobile_menu">
                    <i class="fal fa-bars"></i>
                </div>
                <div class="mobile_menu_wrap">
                    <div class="mobile_menu_overlay open_mobile_menu"></div>
                    <div class="mobile_menu_content">
                        <div class="mobile_menu_close open_mobile_menu">
                            <i class="fal fa-times"></i>
                        </div>
                        <div class="m-brand-logo">
                            <?php groser_logo();?>
                        </div>
                        <div class="in-m-search">
                            <form action="#">
                                <input type="text" name="search" placeholder="Search..">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </form>
                        </div>
                        <nav class="mobile-main-navigation  clearfix ul-li">
                            <?php groser_menu_two_register();?>
                        </nav>
                    </div>
                </div>
                <!-- /Mobile-Menu -->
            </div>
        </div>
    </div>
    <div class="gr-header-menu-cta-area">
        <div class="container">
            <div class="gr-header-category-menu-cta d-flex justify-content-between align-items-center">
                <div class="gr-header-category-menu d-flex align-items-center">
                    <div class="gr-header-category-area position-relative">
                        <div class="gr-header-category-btn">
                            <span><i class="fas fa-bars"></i> <?php esc_html_e( 'Browse All Categories', 'groser' )?> </span>
                        </div>
                        <div class="gr-header-category-item ul-li-block">
                            <?php groser_cate_menu_register();?>
                        </div>
                    </div>
                    <div class="gr-header-menu-area">
                        <nav class="gr-main-navigation-area clearfix ul-li">
                            <?php groser_menu_two_register();?>
                        </nav>
                    </div>
                </div>
                <div class="gr-header-email-cta d-flex align-items-center">
                    <div class="inner-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <?php if(!empty($email_title) || !empty($email_id)):?>
                    <div class="inner-text">
                        <span><?php echo wp_kses($email_title, true)?></span>
                        <b><?php echo wp_kses($email_id, true)?></b>
                    </div>
                    <?php endif;?>
                </div>
            </div>

        </div>
    </div>
</header>