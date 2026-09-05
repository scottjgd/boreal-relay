<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap br-wrap">
    <h1 class="br-page-title">
        <span class="dashicons dashicons-phone"></span>
        <?php esc_html_e( 'Customer Escalations', 'boreal-relay' ); ?>
    </h1>

    <?php if ( isset( $_GET['updated'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'boreal_relay_escalation_updated' ) ): ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Escalation updated.', 'boreal-relay' ); ?></p></div>
    <?php endif; ?>

    <div class="br-filter-bar">
        <?php $br_current_status = $status_filter ? $status_filter : ''; ?>
        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=boreal-relay-escalations' ), 'boreal_relay_escalation_filter' ) ); ?>" class="button <?php echo '' === $br_current_status ? 'button-primary' : ''; ?>"><?php esc_html_e( 'All', 'boreal-relay' ); ?></a>
        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=boreal-relay-escalations&status=open' ), 'boreal_relay_escalation_filter' ) ); ?>" class="button <?php echo 'open' === $br_current_status ? 'button-primary' : ''; ?>"><?php esc_html_e( 'Open', 'boreal-relay' ); ?></a>
        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=boreal-relay-escalations&status=in_progress' ), 'boreal_relay_escalation_filter' ) ); ?>" class="button <?php echo 'in_progress' === $br_current_status ? 'button-primary' : ''; ?>"><?php esc_html_e( 'In Progress', 'boreal-relay' ); ?></a>
        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=boreal-relay-escalations&status=resolved' ), 'boreal_relay_escalation_filter' ) ); ?>" class="button <?php echo 'resolved' === $br_current_status ? 'button-primary' : ''; ?>"><?php esc_html_e( 'Resolved', 'boreal-relay' ); ?></a>
    </div>

    <?php if ( empty( $escalations ) ): ?>
        <div class="br-empty">
            <span class="dashicons dashicons-yes-alt" style="font-size:48px;color:#16a34a;"></span>
            <p><?php esc_html_e( 'No escalations to show. Your AI is handling everything!', 'boreal-relay' ); ?></p>
        </div>
    <?php else: ?>
    <?php foreach ( $escalations as $br_escalation ): ?>
    <div class="br-escalation-card <?php echo esc_attr( $br_escalation->status ); ?>">
        <div class="br-esc-header">
            <div>
                <span class="br-esc-status br-esc-status-<?php echo esc_attr( $br_escalation->status ); ?>">
                    <?php echo esc_html( ucwords( str_replace( '_', ' ', $br_escalation->status ) ) ); ?>
                </span>
                <span class="br-hint" style="margin-left:10px"><?php echo esc_html( date_i18n( 'M j, Y g:i a', strtotime( $br_escalation->created_at ) ) ); ?></span>
            </div>
            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=boreal-relay-conversations&session=' . rawurlencode( $br_escalation->session_id ) ), 'boreal_relay_view_conversation' ) ); ?>" class="button button-small">
                <?php esc_html_e( 'View Full Chat', 'boreal-relay' ); ?>
            </a>
        </div>
        <?php if ( $br_escalation->customer_name || $br_escalation->customer_email || $br_escalation->customer_phone ): ?>
        <div class="br-esc-contact">
            <strong>📞 <?php esc_html_e( 'Customer Contact Info', 'boreal-relay' ); ?></strong>
            <div class="br-esc-contact-details">
                <?php if ( $br_escalation->customer_name ):  ?><span><strong><?php esc_html_e( 'Name:', 'boreal-relay' ); ?></strong>  <?php echo esc_html( $br_escalation->customer_name ); ?></span><?php endif; ?>
                <?php if ( $br_escalation->customer_email ): ?><span><strong><?php esc_html_e( 'Email:', 'boreal-relay' ); ?></strong> <a href="mailto:<?php echo esc_attr( $br_escalation->customer_email ); ?>"><?php echo esc_html( $br_escalation->customer_email ); ?></a></span><?php endif; ?>
                <?php if ( $br_escalation->customer_phone ): ?><span><strong><?php esc_html_e( 'Phone:', 'boreal-relay' ); ?></strong> <a href="tel:<?php echo esc_attr( $br_escalation->customer_phone ); ?>"><?php echo esc_html( $br_escalation->customer_phone ); ?></a></span><?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="br-esc-no-contact">
            <em><?php esc_html_e( 'No contact info provided yet — customer was asked in the chat widget.', 'boreal-relay' ); ?></em>
        </div>
        <?php endif; ?>
        <div class="br-esc-body">
            <div class="br-esc-col">
                <strong><?php esc_html_e( 'Customer asked:', 'boreal-relay' ); ?></strong>
                <p><?php echo esc_html( $br_escalation->trigger_message ); ?></p>
            </div>
            <div class="br-esc-col">
                <strong><?php esc_html_e( 'AI responded:', 'boreal-relay' ); ?></strong>
                <p class="br-hint"><?php echo esc_html( $br_escalation->ai_reply ); ?></p>
            </div>
        </div>
        <?php if ( $br_escalation->notes ): ?>
        <div class="br-esc-notes">
            <strong><?php esc_html_e( 'Notes:', 'boreal-relay' ); ?></strong> <?php echo esc_html( $br_escalation->notes ); ?>
            <?php if ( $br_escalation->resolved_by ): ?> — <?php esc_html_e( 'by', 'boreal-relay' ); ?> <?php echo esc_html( $br_escalation->resolved_by ); ?><?php endif; ?>
        </div>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="br-esc-form">
            <?php wp_nonce_field( 'boreal_relay_escalation_update' ); ?>
            <input type="hidden" name="action" value="boreal_relay_update_escalation">
            <input type="hidden" name="escalation_id" value="<?php echo esc_attr( $br_escalation->id ); ?>">
            <div class="br-esc-form-row">
                <div class="br-esc-form-status">
                    <label class="br-esc-form-label"><?php esc_html_e( 'Status', 'boreal-relay' ); ?></label>
                    <select name="status" style="width:100%">
                        <option value="open"        <?php selected( $br_escalation->status, 'open' ); ?>><?php esc_html_e( 'Open', 'boreal-relay' ); ?></option>
                        <option value="in_progress" <?php selected( $br_escalation->status, 'in_progress' ); ?>><?php esc_html_e( 'In Progress', 'boreal-relay' ); ?></option>
                        <option value="resolved"    <?php selected( $br_escalation->status, 'resolved' ); ?>><?php esc_html_e( 'Resolved', 'boreal-relay' ); ?></option>
                    </select>
                </div>
                <div class="br-esc-form-notes">
                    <label class="br-esc-form-label"><?php esc_html_e( 'Internal Notes', 'boreal-relay' ); ?></label>
                    <input type="text" name="notes"
                           value="<?php echo esc_attr( isset( $br_escalation->notes ) ? $br_escalation->notes : '' ); ?>"
                           style="width:100%;box-sizing:border-box;"
                           placeholder="<?php esc_attr_e( 'e.g. Replied via email', 'boreal-relay' ); ?>">
                </div>
                <div class="br-esc-form-btn">
                    <button type="submit" class="button button-primary" style="width:100%"><?php esc_html_e( 'Update', 'boreal-relay' ); ?></button>
                </div>
            </div>
        </form>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
