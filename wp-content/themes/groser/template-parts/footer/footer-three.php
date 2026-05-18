<?php
    $shortabout = cs_get_option('shortabout'); 
    $footer_apps = cs_get_option('footer_apps'); 
    $add_payment_supprt = cs_get_option('add_payment_supprt'); 
    $groser_copywrite_text = cs_get_option('groser_copywrite_text'); 
    $footer_social = cs_get_option('footer_social');
    $app_title = cs_get_option('app_title');
    $app_txt = cs_get_option('app_txt');
?>
<footer class="footer groser-footer-six pt-80">
    <div class="container">
        <div class="footer__main pb-90">
            <div class="row mt-none-40">
                <!-- Big Column -->
                <div class="big-column col-lg-8 col-md-12 col-sm-12">
                    <div class="row clearfix">
                        
                        <div class="footer__widget col-lg-4 col-md-6 col-sm-6 mt-40">
                            <div class="footer__logo mb-20">
                                <?php groser_logo();?>
                            </div>
                            <?php if(!empty($shortabout)):?>
                                <p class="logo-text"><?php echo wp_kses($shortabout, true);?></p>
                            <?php endif;?>
                            <?php if(!empty($footer_social)):?>
                            <div class="footer__social-two mt-15">
                                <?php foreach($footer_social as $social):?>
                                    <a <?php if($social['title']):?> class="<?php echo strtolower(esc_attr($social['title']))?>" <?php endif;?> href="<?php echo esc_url($social['link']);?>"><i class="<?php echo esc_attr($social['icon']);?>"></i></a>
                                <?php endforeach;?>
                            </div>
                            <?php endif;?>
                            <div class="install_app">
                                <?php if(!empty($app_title)){ echo wp_kses($app_title, true);}?>
                                <?php if(!empty($app_txt)):?>
                                    <span><?php echo wp_kses($app_txt, true);?></span>
                                <?php endif;?>
                            </div>
                            <?php if(!empty($footer_apps)):?>
                            <div class="apps-img ul_li">
                                <?php foreach($footer_apps as $app):?>
                                    <div class="app mt-15">
                                        <a href="<?php echo esc_url($app['app_link']);?>"><img src="<?php echo esc_url($app['app_logo_img']['url'])?>" alt="<?php echo esc_attr($app['app_logo_img']['alt'])?>"></a>
                                    </div>
                                <?php endforeach;?>
                            </div>
                            <?php endif;?>
                        </div>
                        <?php if(is_active_sidebar('groser-footer-1')):?>
                            <div class="footer__widget col-lg-4 col-md-6 col-sm-6 mt-40">
                                <?php dynamic_sidebar('groser-footer-1');?>
                            </div>
                        <?php endif;?>

                        <?php if(is_active_sidebar('groser-footer-2')):?>
                            <div class="footer__widget col-lg-4 col-md-6 col-sm-6 mt-40">
                                <?php dynamic_sidebar('groser-footer-2');?>
                            </div>
                        <?php endif;?>
                    </div>
                </div>
                <!-- Big Column -->
                <div class="big-column col-lg-4 col-md-12 col-sm-12">
                    <div class="row clearfix">
                        <?php if(is_active_sidebar('groser-footer-3')):?>
                            <div class="footer__widget col-lg-6 col-md-6 col-sm-6 mt-40">
                                <?php dynamic_sidebar('groser-footer-3');?>
                            </div>
                        <?php endif;?>
                        <?php if(is_active_sidebar('groser-footer-4')):?>
                            <div class="footer__widget col-lg-6 col-md-6 col-sm-6 mt-40">
                                <?php dynamic_sidebar('groser-footer-4');?>
                            </div>
                        <?php endif;?>
                        
                    </div>
                </div>
                
            </div>
        </div>
        <div class="footer__bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="footer__copyright mt-15">
                    <?php 
                        if(!empty($groser_copywrite_text)){
                            echo wp_kses( $groser_copywrite_text, true );
                        }else{
                            esc_html_e( '&copy; 2023 Groser - Grocery Store. All Rights Reserved.', 'groser' );
                        }
                    ?> 
                </div>
                <?php if(!empty($add_payment_supprt)):?>
                <div class="payment_method mt-15">
                    <img src="<?php echo esc_url($add_payment_supprt['url']);?>" alt="<?php echo esc_attr($add_payment_supprt['alt']);?>">
                </div>
                <?php endif;?>
            </div>
        </div>
    </div>
</footer>