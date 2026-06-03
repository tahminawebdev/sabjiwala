<section class="sjw-hero">
    <div class="sjw-hero__inner">

        <!-- Left: copy & CTAs -->
        <div class="sjw-hero__content">
            <h2 class="sjw-hero__title">
                <?php esc_html_e( 'Fresh Bangladeshi, Indian &', 'groser-children' ); ?>
                <span><?php esc_html_e( 'Asian Groceries', 'groser-children' ); ?></span>
                <?php esc_html_e( 'Delivered to Your Doorstep', 'groser-children' ); ?>
            </h2>
            <div class="sjw-hero__sub">
                <p><?php esc_html_e( 'Same-day delivery • Fresh vegetables daily', 'groser-children' ); ?></p>
                <p><?php esc_html_e( 'Authentic groceries at unbeatable prices', 'groser-children' ); ?></p>
            </div>
            <div class="sjw-hero__actions">
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sjw-btn sjw-btn--primary">
                    <!-- ShoppingCart icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                    <?php esc_html_e( 'Shop Now', 'groser-children' ); ?>
                </a>
                <?php
                $deals = get_term_by( 'name', 'Deals', 'product_cat' );
                $deals_url = $deals ? get_term_link( $deals ) : wc_get_page_permalink( 'shop' );
                ?>
                <a href="<?php echo esc_url( $deals_url ); ?>" class="sjw-btn sjw-btn--outline">
                    <!-- Tag icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/></svg>
                    <?php esc_html_e( "View Today's Deals", 'groser-children' ); ?>
                </a>
            </div>
        </div>

        <!-- Right: hero image -->
        <div class="sjw-hero__image">
            <img
                src="https://media.base44.com/images/public/6a1f6c0fe8097563324311bb/967451fee_generated_36b0e130.png"
                alt="<?php esc_attr_e( 'Fresh groceries basket', 'groser-children' ); ?>"
                loading="eager"
                width="540"
                height="480"
            >
        </div>

    </div>
</section>
