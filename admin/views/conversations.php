<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap br-wrap">
    <h1 class="br-page-title">
        <span class="dashicons dashicons-format-chat"></span>
        <?php esc_html_e( 'Conversations', 'boreal-relay' ); ?>
    </h1>

    <?php if ( empty( $sessions ) ): ?>
        <div class="br-empty">
            <span class="dashicons dashicons-format-chat" style="font-size:48px;color:#cbd5e1;"></span>
            <p><?php esc_html_e( "No conversations yet. Once customers start chatting, they'll appear here.", 'boreal-relay' ); ?></p>
        </div>
    <?php else: ?>
    <div class="br-table-wrap">
    <table class="wp-list-table widefat striped br-table">
        <thead>
            <tr>
                <th style="min-width:140px"><?php esc_html_e( 'Session', 'boreal-relay' ); ?></th>
                <th style="min-width:70px"><?php esc_html_e( 'Messages', 'boreal-relay' ); ?></th>
                <th style="min-width:110px" class="br-col-hide-mobile"><?php esc_html_e( 'Avg Confidence', 'boreal-relay' ); ?></th>
                <th style="min-width:90px"><?php esc_html_e( 'Escalated?', 'boreal-relay' ); ?></th>
                <th style="min-width:110px" class="br-col-hide-mobile"><?php esc_html_e( 'Started', 'boreal-relay' ); ?></th>
                <th style="min-width:110px"><?php esc_html_e( 'Last Activity', 'boreal-relay' ); ?></th>
                <th style="min-width:70px"><?php esc_html_e( 'Actions', 'boreal-relay' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $sessions as $br_session ): ?>
            <tr>
                <td><code style="font-size:11px"><?php echo esc_html( substr( $br_session->session_id, 0, 16 ) ); ?>&hellip;</code></td>
                <td><?php echo esc_html( $br_session->message_count ); ?></td>
                <td class="br-col-hide-mobile">
                    <?php if ( $br_session->avg_confidence !== null ): ?>
                        <span class="br-confidence" data-pct="<?php echo esc_attr( round( $br_session->avg_confidence * 100 ) ); ?>">
                            <?php echo esc_html( round( $br_session->avg_confidence * 100 ) ); ?>%
                        </span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td>
                    <?php if ( $br_session->has_escalation ): ?>
                        <span class="br-badge-escalated"><?php esc_html_e( 'Yes', 'boreal-relay' ); ?></span>
                    <?php else: ?>
                        <span class="br-badge-ok"><?php esc_html_e( 'No', 'boreal-relay' ); ?></span>
                    <?php endif; ?>
                </td>
                <td class="br-col-hide-mobile"><?php echo esc_html( date_i18n( 'M j, g:i a', strtotime( $br_session->started_at ) ) ); ?></td>
                <td><?php echo esc_html( date_i18n( 'M j, g:i a', strtotime( $br_session->last_activity ) ) ); ?></td>
                <td>
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=boreal-relay-conversations&session=' . rawurlencode( $br_session->session_id ) ), 'boreal_relay_view_conversation' ) ); ?>" class="button button-small">
                        <?php esc_html_e( 'View', 'boreal-relay' ); ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
