<section class="sjw-promo">
    <div class="sjw-promo__inner">

        <!-- AI Recipe Finder -->
        <div class="sjw-promo__box sjw-promo__box--ai">
            <div class="sjw-promo__tag">
                <div class="sjw-promo__tag-icon">
                    <!-- ChefHat icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.87c1.355 0 2.697.055 4.024.165C17.155 8.51 18 9.473 18 10.608v2.513m-3-4.87v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.38a48.474 48.474 0 00-6-.37c-2.032 0-4.034.125-6 .37m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.254 1.337 2.23a13.716 13.716 0 01-4.5 5.14c-.4.3-1.075.3-1.475 0A13.716 13.716 0 015 14.85c-.5-.976.267-2.07 1.337-2.23.386-.058.773-.111 1.163-.16"/></svg>
                </div>
                <span class="sjw-promo__tag-text"><?php esc_html_e( 'AI Recipe Finder', 'groser-children' ); ?></span>
            </div>
            <h4 class="sjw-promo__title"><?php esc_html_e( 'Find recipes, get ingredients and add to cart in one click!', 'groser-children' ); ?></h4>
            <p class="sjw-promo__sub"><?php esc_html_e( "Tell our AI what you want to cook, and we'll find the perfect recipe with all ingredients ready to order.", 'groser-children' ); ?></p>
            <a href="#" class="sjw-btn sjw-btn--primary"><?php esc_html_e( 'Try Now', 'groser-children' ); ?></a>
        </div>

        <!-- Weekend Deals -->
        <div class="sjw-promo__box sjw-promo__box--deals">
            <div class="sjw-promo__tag">
                <div class="sjw-promo__tag-icon">
                    <!-- Percent icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 4.5h.008v.008h-.008V13.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                </div>
                <span class="sjw-promo__tag-text"><?php esc_html_e( 'Weekend', 'groser-children' ); ?></span>
            </div>
            <h4 class="sjw-promo__title sjw-promo__title--deals"><?php esc_html_e( 'SUPER DEALS', 'groser-children' ); ?></h4>
            <p class="sjw-promo__deals-sub"><?php esc_html_e( 'Up to 20% OFF', 'groser-children' ); ?></p>
            <?php
            $deals = get_term_by( 'name', 'Deals', 'product_cat' );
            $deals_url = $deals ? get_term_link( $deals ) : wc_get_page_permalink( 'shop' );
            ?>
            <a href="<?php echo esc_url( $deals_url ); ?>" class="sjw-btn sjw-btn--accent" style="margin-top:1.25rem;"><?php esc_html_e( 'Shop Deals', 'groser-children' ); ?></a>
            <img
                src="https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/cd2c69bd1_generated_3d63f7a7.png"
                alt=""
                class="sjw-promo__img"
                loading="lazy"
                width="208"
                height="208"
                aria-hidden="true"
            >
        </div>

    </div>
</section>
