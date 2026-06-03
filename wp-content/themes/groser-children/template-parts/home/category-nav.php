<nav class="sjw-cat-nav" aria-label="<?php esc_attr_e( 'Shop categories', 'groser-children' ); ?>">
    <div class="sjw-cat-nav__inner">

        <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sjw-cat-nav__all-btn">
            <!-- LayoutGrid icon -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
            <span class="sjw-cat-nav__all-label-full"><?php esc_html_e( 'Browse All Categories', 'groser-children' ); ?></span>
            <span class="sjw-cat-nav__all-label-short"><?php esc_html_e( 'All', 'groser-children' ); ?></span>
        </a>

        <div class="sjw-cat-nav__items">
            <?php
            $nav_cats = [
                'Fresh Vegetables',
                'Fruits',
                'Rice & Flour',
                'Meat & Fish',
                'Spices & Pulses',
                'Drinks',
                'Frozen',
            ];
            foreach ( $nav_cats as $cat ) :
                // Try to find a matching WooCommerce category term
                $term = get_term_by( 'name', $cat, 'product_cat' );
                $url  = $term ? get_term_link( $term ) : get_permalink( wc_get_page_id( 'shop' ) );
            ?>
                <a href="<?php echo esc_url( $url ); ?>" class="sjw-cat-nav__item">
                    <?php echo esc_html( $cat ); ?>
                </a>
            <?php endforeach; ?>
            <?php
            $deals_term = get_term_by( 'name', 'Deals', 'product_cat' );
            $deals_url  = $deals_term ? get_term_link( $deals_term ) : get_permalink( wc_get_page_id( 'shop' ) );
            ?>
            <a href="<?php echo esc_url( $deals_url ); ?>" class="sjw-cat-nav__item sjw-cat-nav__item--deals">
                <?php esc_html_e( 'Deals', 'groser-children' ); ?>
            </a>
        </div>

        <a href="#" class="sjw-cat-nav__track-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            <?php esc_html_e( 'Track Your Order', 'groser-children' ); ?>
        </a>
    </div>
</nav>
