<div class="blog-post-wrap mt-50">
    <article id="post-<?php the_ID(); ?>"  <?php post_class('post-details'); ?>>
        <figure class="post-thumb mb-30">
            <?php
                if(has_post_thumbnail()){
                    the_post_thumbnail('groser-image-size4');
                }
            ?>
        </figure>
        <?php if(function_exists('groser_entry_footer')):?>
        <ul class="post-tags ul_li mb-20">
            <?php groser_entry_footer();?>
        </ul>
        <?php endif;?>
        <h2><?php the_title();?></h2>
        <ul class="post-meta meta-bottom-border ul_li mt-25">
            <li>
                <div class="post-meta__author ul_li">
                    <div class="avatar">
                        <?php groser_main_author_avatars(22);?>
                    </div>
                    <span><?php the_author()?><?php
                            if(function_exists('groser_ready_time_ago')){ ?>
                                / <span class="year"><?php echo groser_ready_time_ago();?></span>
                            <?php }
                            ?> </span>
                </div>
            </li>
            <li><i class="fas fa-comment"></i><?php echo esc_html(get_comments_number());?></li>
            <li><i class="fas fa-clock"></i><?php echo groser_reading_time();?></li>
        </ul>
        <div class="entry-content">
            <?php
            the_content(
                sprintf(
                    wp_kses(
                        /* translators: %s: Name of current post. Only visible to screen readers */
                        __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'groser' ),
                        array(
                            'span' => array(
                                'class' => array(),
                            ),
                        )
                    ),
                    wp_kses_post( get_the_title() )
                )
            );

            wp_link_pages(
                array(
                    'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'groser' ),
                    'after'  => '</div>',
                )
            );
            ?>
        </div><!-- .entry-content -->
    </article>
    <div class="post-footer">
        <div class="post-tags-share mb-55">
            <?php if(function_exists('groser_entry_footer_two')):?>
            <div class="tags ul_li mt-30">
                <h5 class="title"><?php esc_html_e('Tags:', 'groser' );?></h5>
                <ul class="list-unstyled ul_li">
                    <?php groser_entry_footer_two();?>
                </ul>
            </div>
            <?php endif;?>
            <?php if(function_exists('groser_post_share')):?>
            <div class="social-share ul_li mt-30">
                <?php groser_post_share()?>
            </div>
            <?php endif;?>
        </div>
    </div>
</div>