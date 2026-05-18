<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package groser
 */

get_header();
$groserPostClass = '';
if(is_active_sidebar('sidebar-1')){
	$groserPostClass = 'col-xl-9 col-lg-8 sticky-coloum-item';
}else{
	$groserPostClass = 'col-lg-10 offset-lg-1 no-active-sidebar';
}
groser_page_breadcrumb();
?>
	<section class="blog pb-90">
		<div class="container">
			<div class="row mt-none-50 sticky-coloum-wrap">
				<div class="<?php echo esc_attr($groserPostClass);?>">
					<?php groser_single_post_loop();?>
				</div>
				<?php get_sidebar();?>
			</div>
		</div>
	</section>
<?php

get_footer();
