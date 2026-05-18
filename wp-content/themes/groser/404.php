<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package groser
 */

get_header();
groser_page_breadcrumb();
?>
<?php groser_error_page();?>

<?php
get_footer();
