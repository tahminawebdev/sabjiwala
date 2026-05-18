<?php
/**
 * Template Name: Groser Elementor Full Template
 *
 * 
 * @package groser
 */

get_header(); 
?>

<div class="clearfix"></div>

<div id="elementor_page_builder">

	<?php while ( have_posts() ) : the_post(); ?>
		<?php the_content(); ?>
	<?php endwhile; // End of the loop. ?>

</div><!-- #main -->

<div class="clearfix"></div>

<?php get_footer(); ?>
