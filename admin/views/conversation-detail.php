<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
$br_is_pro  = function_exists( 'boreal_relay_has_active_pro' ) && boreal_relay_has_active_pro();
$br_pro_url = function_exists( 'boreal_relay_get_pro_url' )
    ? boreal_relay_get_pro_url( 'conversation-detail' )
    : 'https://borealform.com/boreal-relay';
?>
<div class="wrap br-wrap">
    <h1 class="br-page-title">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=boreal-relay-conversations' ) ); ?>" style="text-decoration:none;color:inherit;">
            <span class="dashicons dashicons-arrow-left-alt2"></span>
        </a>
        <?php esc_html_e( 'Conversation Detail', 'boreal-relay' ); ?>
    </h1>
    <p class="br-hint"><?php esc_html_e( 'Session:', 'boreal-relay' ); ?> <code><?php echo esc_html( $session_id ); ?></code></p>

    <div class="br-chat-preview">
        <?php if ( empty( $messages ) ): ?>
            <p class="br-hint"><?php esc_html_e( 'No messages found for this session.', 'boreal-relay' ); ?></p>
        <?php else: ?>
            <?php foreach ( $messages as $br_message ): ?>
            <div class="br-chat-msg br-chat-<?php echo esc_attr( $br_message->role ); ?>">
                <div class="br-chat-meta">
                    <strong><?php echo $br_message->role === 'user' ? esc_html__( 'Customer', 'boreal-relay' ) : esc_html__( 'AI Assistant', 'boreal-relay' ); ?></strong>
                    <span><?php echo esc_html( date_i18n( 'M j, g:i:s a', strtotime( $br_message->created_at ) ) ); ?></span>
                    <?php if ( $br_message->role === 'assistant' && $br_message->confidence !== null ): ?>
                        <span class="br-confidence"><?php echo esc_html( round( $br_message->confidence * 100 ) ); ?>% <?php esc_html_e( 'confidence', 'boreal-relay' ); ?></span>
                    <?php endif; ?>
                    <?php if ( $br_message->escalated ): ?>
                        <span class="br-badge-escalated"><?php esc_html_e( 'Escalated', 'boreal-relay' ); ?></span>
                    <?php endif; ?>
                    <?php if ( $br_message->helpful === '1' ): ?>
                        <span style="color:#16a34a">👍 <?php esc_html_e( 'Helpful', 'boreal-relay' ); ?></span>
                    <?php elseif ( $br_message->helpful === '0' ): ?>
                        <span style="color:#dc2626">👎 <?php esc_html_e( 'Not helpful', 'boreal-relay' ); ?></span>
                    <?php endif; ?>
                </div>
                <div class="br-chat-bubble"><?php echo nl2br( esc_html( $br_message->message ) ); ?></div>
                <?php if ( $br_message->role === 'assistant' && $br_message->helpful === '0' && $br_is_pro ): ?>
                <div style="margin-top:8px">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=boreal-relay-knowledge' ) ); ?>" class="button button-small">
                        <?php esc_html_e( 'Review pending knowledge draft', 'boreal-relay' ); ?>
                    </a>
                </div>
                <?php elseif ( $br_message->role === 'assistant' && $br_message->helpful === '0' && ! $br_is_pro ): ?>
                <div style="margin-top:8px">
                    <a href="<?php echo esc_url( $br_pro_url ); ?>" class="button button-small" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e( 'Pro: Improve and approve this answer', 'boreal-relay' ); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
