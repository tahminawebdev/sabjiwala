<form role="search" method="get" class="sjw-header__search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <input
        type="search"
        placeholder="<?php esc_attr_e( 'Search for products, or ask me anything...', 'groser-children' ); ?>"
        value="<?php echo esc_attr( get_search_query() ); ?>"
        name="s"
        class="sjw-header__search-input"
        aria-label="<?php esc_attr_e( 'Search', 'groser-children' ); ?>"
    >
    <button type="submit" class="sjw-header__search-btn" aria-label="<?php esc_attr_e( 'Search', 'groser-children' ); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
    </button>
    <?php if ( class_exists( 'WooCommerce' ) ) : ?>
        <input type="hidden" name="post_type" value="product">
    <?php endif; ?>
</form>
