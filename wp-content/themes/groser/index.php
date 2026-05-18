<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
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
<div class="blog pb-90">
	<div class="container">
		<div class="row mt-none-50 sticky-coloum-wrap">
			<div class="<?php echo esc_attr($groserPostClass);?>">
				<div class="blog-post-wrap mt-50">
					<?php groser_post_loop();?>
				</div>			
			</div>			
			<?php get_sidebar(); ?>
		</div>
	</div>
</div>
<?php
get_footer();
