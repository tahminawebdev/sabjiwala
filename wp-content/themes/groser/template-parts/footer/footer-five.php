<?php
   $shortabout = cs_get_option('shortabout');
   $footer_apps = cs_get_option('footer_apps');
   $add_payment_supprt = cs_get_option('add_payment_supprt');
   $groser_copywrite_text = cs_get_option('groser_copywrite_text');
   $footer_social = cs_get_option('footer_social');
   $footer_features = cs_get_option('footer_features');
   $payment_title = cs_get_option('payment_title');
   $is_enable_footer_sponser = cs_get_option('is_enable_footer_sponser');
   $sponcors = cs_get_option('add-sponcor-itm');
   $footer_bg = cs_get_option('footer_shape-bg');
   $contact_info = cs_get_option('contact_info');
?>
<footer id="gr-footer" class="gr-footer-section" <?php if(!empty($footer_bg['url'])):?>data-background="<?php echo esc_url($footer_bg['url']);?>" <?php endif;?>>
    <div class="container">
        <div class="gr-footer-widget-content">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="gr-footer-widget">
                        <div class="logo-widget">
                            <div class="footer-logo">
                                <?php groser_logo_v3();?>
                            </div>
                            <div class="logo-text">
                            <?php if(!empty($shortabout)):?>
                                <?php echo wp_kses($shortabout, true);?>
                            <?php endif;?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if(is_active_sidebar('groser-footer-1')):?>
                <div class="col-lg-3 col-md-6">
                    <div class="gr-footer-widget headline">
                        <div class="menu-widget ul-li-block">
                         <?php dynamic_sidebar('groser-footer-1');?>
                        </div>
                    </div>
                </div>
                <?php endif;?>
                <?php if(is_active_sidebar('groser-footer-2')):?>
                <div class="col-lg-3 col-md-6">
                    <div class="gr-footer-widget headline">
                        <div class="menu-widget ul-li-block">
                            <?php dynamic_sidebar('groser-footer-2');?>
                        </div>
                    </div>
                </div>
                <?php endif;?>
                <div class="col-lg-3 col-md-6">
                    <div class="gr-footer-widget headline">
                        <div class="address-widget">
                            <h3 class="gr-widget-title">Contact Us</h3>
                            <div class="gr-footer-address ul-li-block">
                                <?php echo wp_kses($contact_info, true);?>
                            </div>
                            <?php if(!empty($footer_social)):?>
                            <div class="footer-social ul-li">
                                <ul>
                                    <?php foreach($footer_social as $social):?>
                                    <li><a href="<?php echo esc_url($social['link']);?>"><i class="<?php echo esc_attr($social['icon']);?>"></i></a></li>
                                    <?php endforeach;?>
                                </ul>
                            </div>
                            <?php endif;?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if( $is_enable_footer_sponser == true ) : ?>
    <div class="gr-footer-sponsor">
        <div class="container">
            <div class="gr-footer-sponsor-content position-relative">
                <?php if(!empty($sponcors)):?>
                <div class="gr-footer-sponsor-slider" >
                    <?php
                    $gallery_ids = explode( ',', $sponcors );
                    foreach($gallery_ids as $item):?>
                    <div class="gr-footer-sponsor-slide-img">
                        <div class="item-inner-img">
                            <img src="<?php echo esc_url(wp_get_attachment_url($item))?>" alt="<?php esc_attr_e( 'Sponsor', 'groser' )?>">
                        </div>
                    </div>
                    <?php endforeach;?>
                </div>
                <?php endif;?>
                <div class="gr-store-carousel">
                    <div class="carousel_nav text-center">
                        <button type="button" class="spon_left_arrow_1 text-uppercase"><i class="fas fa-long-arrow-left"></i></button>
                        <button type="button" class="spon_right_arrow_1 text-uppercase"><i class="fas fa-long-arrow-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif;?>
    <div class="gr-footer-copyright">
        <div class="container">
            <div class="gr-footer-copyright-content d-flex justify-content-between align-items-center">
                <div class="copyright-text">
                <?php
                        if(!empty($groser_copywrite_text)){
                            echo wp_kses( $groser_copywrite_text, true );
                        }else{
                            esc_html_e( '&copy; 2023 Groser - Grocery Store. All Rights Reserved.', 'groser' );
                        }
                    ?>
                </div>
                <?php if(!empty($add_payment_supprt)):?>
                <div class="copyright-payment d-flex align-items-center">
                    <b><?php echo esc_html($payment_title);?></b >
                    <span><img src="<?php echo esc_url($add_payment_supprt['url']);?>" alt="<?php echo esc_attr($add_payment_supprt['alt']);?>"></span>
                </div>
                <?php endif;?>
            </div>
        </div>
    </div>
</footer>