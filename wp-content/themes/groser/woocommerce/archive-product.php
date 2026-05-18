<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
groser_page_breadcrumb();
global $wp_query;

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );
$groserShopClass = '';
if(is_active_sidebar('shop-sidebar-1')){
	$groserShopClass = 'woocommerce-content-wrap';
}else{
	$groserShopClass = 'woocommerce-content-wrap no-shop-active-sidebar';
}
$groser_shop_layout = cs_get_option('groser_shop_layout');
?>

<div class="shop-section pb-80">
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<div class="shop-area clearfix">
					<?php if($groser_shop_layout == 'left-sidebar'):?>
					<div class="shop-sidebar">
						<?php
						dynamic_sidebar( 'shop-sidebar-1' );
							/**
							* Hook: woocommerce_sidebar.
							*
							* @hooked woocommerce_get_sidebar - 10
							*/

							do_action( 'woocommerce_sidebar' );	
						?>
					</div>
					<?php endif;?>
					<div class="<?php echo esc_attr($groserShopClass);?> <?php echo esc_attr($groser_shop_layout);?>">
					<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
						<div class="woocommerce-toolbar-top">
							<p class="woocommerce-result-count"><?php woocommerce_result_count(); ?></p>
							<div class="products-sizes">
								<a href="#!" class="grid-4 <?php if(!is_active_sidebar('shop-sidebar-1')):?> active <?php endif;?>">
									<div class="grid-draw">
										<span></span>
										<span></span>
										<span></span>
										<span></span>
									</div>
									<div class="grid-draw">
										<span></span>
										<span></span>
										<span></span>
										<span></span>
									</div>
									<div class="grid-draw">
										<span></span>
										<span></span>
										<span></span>
										<span></span>
									</div>
								</a>
								<a href="#!" class="grid-3 <?php if(is_active_sidebar('shop-sidebar-1')):?> active <?php endif;?>">
									<div class="grid-draw">
										<span></span>
										<span></span>
										<span></span>
									</div>
									<div class="grid-draw">
										<span></span>
										<span></span>
										<span></span>
									</div>
									<div class="grid-draw">
										<span></span>
										<span></span>
										<span></span>
									</div>
								</a>
								<a href="#!" class="list-view">
									<div class="grid-draw-line">
										<span></span>
										<span></span>
									</div>
									<div class="grid-draw-line">
										<span></span>
										<span></span>
									</div>
									<div class="grid-draw-line">
										<span></span>
										<span></span>
									</div>
								</a>
							</div>
							<?php woocommerce_catalog_ordering();?>                         
						</div>
						<?php endif;?>
						<div class="woocommerce-content-inner">
							<?php
								/**
								 * woocommerce_before_main_content hook
								 *
								 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
								 * @hooked woocommerce_breadcrumb - 20
								 */
								do_action( 'woocommerce_before_main_content' );
							?>
							
							<?php
								/**
								 * woocommerce_archive_description hook
								 *
								 * @hooked woocommerce_taxonomy_archive_description - 10
								 * @hooked woocommerce_product_archive_description - 10
								 */
								do_action( 'woocommerce_archive_description' );
							?>
							
							<?php if ( have_posts() ) : ?>
						
								<?php woocommerce_product_loop_start(); ?>
					
									<?php woocommerce_product_subcategories(); ?>
					
									<?php while ( have_posts() ) : the_post(); ?>
					
										<?php wc_get_template_part( 'content', 'product' ); ?>
					
									<?php endwhile; // end of the loop. ?>
					
								<?php woocommerce_product_loop_end(); ?>
					
								<?php
									/**
									 * woocommerce_after_shop_loop hook
									 *
									 * @hooked woocommerce_pagination - 10
									 */
									do_action( 'woocommerce_after_shop_loop' );
								?>
					
							<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>
					
								<?php wc_get_template( 'loop/no-products-found.php' ); ?>
					
							<?php endif; ?>
							
							<?php
								/**
								 * woocommerce_after_main_content hook
								 *
								 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
								 */
								do_action( 'woocommerce_after_main_content' );
							?>
						</div>
					</div>
					<?php if($groser_shop_layout == 'right-sidebar'):?>
					<div class="shop-sidebar">
					    
					    ggg
						<?php
						dynamic_sidebar( 'shop-sidebar-1' );
							/**
							* Hook: woocommerce_sidebar.
							*
							* @hooked woocommerce_get_sidebar - 10
							*/

							do_action( 'woocommerce_sidebar' );	
						?>
					</div>
					<?php endif;?>
				</div>
			</div>
		</div>
	</div>
</div>


<?php 
get_footer( 'shop' );
