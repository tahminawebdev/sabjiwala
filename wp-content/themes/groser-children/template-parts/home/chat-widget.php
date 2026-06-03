<!-- Floating Chat Widget — ported from sabji-fresh-roots ChatWidget.jsx -->
<div class="sjw-chat" id="sjw-chat-widget">

    <!-- Expandable panel -->
    <div class="sjw-chat__panel" id="sjw-chat-panel" role="dialog" aria-label="<?php esc_attr_e( 'Shop assistant', 'groser-children' ); ?>">
        <div class="sjw-chat__head">
            <div>
                <p class="sjw-chat__head-name"><?php esc_html_e( "Hi! I'm Sabjiwala", 'groser-children' ); ?></p>
                <p class="sjw-chat__head-sub"><?php esc_html_e( 'Your Shop Assistant', 'groser-children' ); ?></p>
                <p class="sjw-chat__head-sub"><?php esc_html_e( 'How can I help you today?', 'groser-children' ); ?></p>
            </div>
            <button class="sjw-chat__close" id="sjw-chat-close" aria-label="<?php esc_attr_e( 'Close chat', 'groser-children' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="sjw-chat__suggestions">
            <?php
            $suggestions = [
                [
                    'text' => __( "What's fresh today?", 'groser-children' ),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>',
                ],
                [
                    'text' => __( 'Ingredients for a recipe', 'groser-children' ),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>',
                ],
                [
                    'text' => __( 'Best rice for biryani?', 'groser-children' ),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>',
                ],
                [
                    'text' => __( "Today's offers", 'groser-children' ),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>',
                ],
                [
                    'text' => __( 'Track my order', 'groser-children' ),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>',
                ],
            ];
            foreach ( $suggestions as $s ) : ?>
                <button class="sjw-chat__suggestion" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <?php echo $s['icon']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                    </svg>
                    <?php echo esc_html( $s['text'] ); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="sjw-chat__input-area">
            <div class="sjw-chat__input-row">
                <input type="text" placeholder="<?php esc_attr_e( 'Type your question...', 'groser-children' ); ?>" aria-label="<?php esc_attr_e( 'Chat message', 'groser-children' ); ?>">
                <button class="sjw-chat__send" type="button" aria-label="<?php esc_attr_e( 'Send', 'groser-children' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                </button>
            </div>
            <p class="sjw-chat__powered"><?php esc_html_e( 'Powered by Sabjiwala AI', 'groser-children' ); ?></p>
        </div>
    </div>

    <!-- Toggle button -->
    <button class="sjw-chat__toggle" id="sjw-chat-toggle" aria-label="<?php esc_attr_e( 'Open shop assistant', 'groser-children' ); ?>">
        <!-- MessageCircle icon (default) -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
        <span class="sjw-chat__badge">1</span>
    </button>
    <p class="sjw-chat__hint"><?php esc_html_e( 'Need help? Ask me!', 'groser-children' ); ?></p>
</div>
