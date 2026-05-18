<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package groser
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area mt-50">
	<div class="row">
		<div class="col-xl-10 post-comments">
			<?php
				// You can start editing here -- including this comment!
				if ( have_comments() ) :
					?>
					<h2 class="comments-title title mb-25">
						<?php
						$groser_comment_count = get_comments_number();
						if ( '1' === $groser_comment_count ) {
							printf(
								/* translators: 1: title. */
								esc_html__( '1 Comment', 'groser' ),
								'<span>' . wp_kses_post( get_the_title() ) . '</span>'
							);
						} else {
							printf( 
								/* translators: 1: comment count number, 2: title. */
								esc_html( _nx( '%1$s Comment', '%1$s Comments', $groser_comment_count, 'comments title', 'groser' ) ),
								number_format_i18n( $groser_comment_count ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								'<span>' . wp_kses_post( get_the_title() ) . '</span>'
							);
						}
						?>
					</h2><!-- .comments-title -->

					<?php the_comments_navigation(); ?>

					<div class="latest__comments">
						<ol class="comment-list list-unstyled mb-0">
							<?php
							wp_list_comments(
								array(
									'callback'      => 'groser_comments',
								)
							);
							?>
						</ol><!-- .comment-list -->
					</div><!-- .comment-list -->

					<?php
					the_comments_navigation();

					// If comments are closed and there are comments, let's leave a little note, shall we?
					if ( ! comments_open() ) :
						?>
						<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'groser' ); ?></p>
						<?php
					endif;

				endif; // Check for have_comments().

				$args = array(
					'comment_notes_after' => '<button class="thm-btn thm-btn__2 no-icon" type="submit" id="submit-new"><span class="btn-wrap"><span>'.esc_html('Post Comment').'</span><span>'.esc_html('Post Comment').'</span></span></button>' 
				);

				comment_form($args);
				?>
		</div>
	</div>
</div><!-- #comments -->
