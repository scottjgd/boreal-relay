<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BR_Widget {

    public function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_footer', array( $this, 'render_widget' ) );
    }

    public function enqueue_assets() {
        if ( get_option( 'boreal_relay_enabled', '1' ) !== '1' ) return;

        wp_enqueue_style(
            'boreal-relay-widget',
            BR_PLUGIN_URL . 'public/css/chat-widget.css',
            array(),
            BR_VERSION
        );

        wp_enqueue_script(
            'boreal-relay-widget',
            BR_PLUGIN_URL . 'public/js/chat-widget.js',
            array( 'jquery' ),
            BR_VERSION,
            true
        );

        $page_url = get_permalink();
        if ( ! $page_url ) {
            $request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_UNSAFE_RAW );
            $page_url    = home_url( esc_url_raw( is_string( $request_uri ) ? $request_uri : '/' ) );
        }

        wp_localize_script( 'boreal-relay-widget', 'BorealRelay', array(
            'ajax_url'    => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'boreal_relay_nonce' ),
            'greeting'    => get_option( 'boreal_relay_greeting', 'Hi there! 👋 I\'m your Boreal Relay assistant. How can I help you today?' ),
            'bot_name'    => get_option( 'boreal_relay_bot_name', 'Relay' ),
            'theme_color' => get_option( 'boreal_relay_theme_color', '#2563eb' ),
            'page_url'    => esc_url_raw( $page_url ),
        ) );
    }

    public function render_widget() {
        if ( get_option( 'boreal_relay_enabled', '1' ) !== '1' ) return;
        $bot_name    = esc_attr( get_option( 'boreal_relay_bot_name', 'Relay' ) );
        $theme_color = esc_attr( get_option( 'boreal_relay_theme_color', '#2563eb' ) );
        ?>
        <div id="boreal-relay-widget" style="--br-primary: <?php echo esc_attr( $theme_color ); ?>">

            <div id="boreal-relay-teaser" role="button" tabindex="0" aria-label="Open chat">
                <span id="boreal-relay-teaser-dot"></span>
                We&rsquo;re online &mdash; ask us anything!
            </div>

            <button id="boreal-relay-toggle" aria-label="Open chat assistant" title="Chat with us">
                <svg id="boreal-relay-icon-chat" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <svg id="boreal-relay-icon-close" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                <span id="boreal-relay-badge" style="display:none"></span>
            </button>

            <div id="boreal-relay-panel" role="dialog" aria-label="<?php echo esc_attr( $bot_name ); ?> Chat Assistant">
                <div id="boreal-relay-header">
                    <div id="boreal-relay-avatar">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                        <span id="boreal-relay-avatar-status"></span>
                    </div>
                    <div id="boreal-relay-header-info">
                        <strong><?php echo esc_html( $bot_name ); ?></strong>
                        <span>Online &mdash; here to help</span>
                    </div>
                    <button id="boreal-relay-minimize" aria-label="Minimize chat">&times;</button>
                </div>

                <div id="boreal-relay-messages" role="log" aria-live="polite"></div>

                <div id="boreal-relay-contact-form" style="display:none">
                    <p id="boreal-relay-contact-intro">So we can follow up with you directly, please leave your contact info:</p>
                    <input type="text"  id="boreal-relay-contact-name"  placeholder="Your name" autocomplete="name">
                    <input type="email" id="boreal-relay-contact-email" placeholder="Email address" autocomplete="email">
                    <input type="tel"   id="boreal-relay-contact-phone" placeholder="Phone number (optional)" autocomplete="tel">
                    <button id="boreal-relay-contact-submit">Send My Info</button>
                    <p id="boreal-relay-contact-thanks" style="display:none">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Thanks! We'll be in touch soon.
                    </p>
                </div>

                <div id="boreal-relay-input-area">
                    <textarea id="boreal-relay-input" placeholder="Type your message..." rows="1" aria-label="Chat message input"></textarea>
                    <button id="boreal-relay-send" aria-label="Send message">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}
