<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
$br_is_pro          = function_exists( 'boreal_relay_has_active_pro' ) && boreal_relay_has_active_pro();
$br_pro_product_url = function_exists( 'boreal_relay_get_pro_url' )
    ? boreal_relay_get_pro_url( 'dashboard-quick-action' )
    : 'https://borealform.com/boreal-relay';
?>
<div class="wrap br-wrap">
    <h1 class="br-page-title">
        <span class="dashicons dashicons-format-chat"></span>
        <?php esc_html_e( 'Boreal Relay — Dashboard', 'boreal-relay' ); ?>
    </h1>

    <div class="br-stats-grid">
        <div class="br-stat-card">
            <div class="br-stat-icon" style="background:#dbeafe">
                <span class="dashicons dashicons-groups" style="color:#2563eb"></span>
            </div>
            <div class="br-stat-body">
                <div class="br-stat-number"><?php echo esc_html( $stats['total_sessions'] ); ?></div>
                <div class="br-stat-label"><?php esc_html_e( 'Total Conversations', 'boreal-relay' ); ?></div>
            </div>
        </div>
        <div class="br-stat-card">
            <div class="br-stat-icon" style="background:#dcfce7">
                <span class="dashicons dashicons-format-chat" style="color:#16a34a"></span>
            </div>
            <div class="br-stat-body">
                <div class="br-stat-number"><?php echo esc_html( $stats['total_messages'] ); ?></div>
                <div class="br-stat-label"><?php esc_html_e( 'Messages Handled', 'boreal-relay' ); ?></div>
            </div>
        </div>
        <div class="br-stat-card">
            <div class="br-stat-icon" style="background:#fef3c7">
                <span class="dashicons dashicons-phone" style="color:#d97706"></span>
            </div>
            <div class="br-stat-body">
                <div class="br-stat-number"><?php echo esc_html( $escalation->get_open_count() ); ?></div>
                <div class="br-stat-label"><?php esc_html_e( 'Open Escalations', 'boreal-relay' ); ?></div>
            </div>
        </div>
        <div class="br-stat-card">
            <div class="br-stat-icon" style="background:#f3e8ff">
                <span class="dashicons dashicons-chart-bar" style="color:#9333ea"></span>
            </div>
            <div class="br-stat-body">
                <div class="br-stat-number"><?php echo esc_html( $stats['avg_confidence'] ); ?>%</div>
                <div class="br-stat-label"><?php esc_html_e( 'Avg. AI Confidence', 'boreal-relay' ); ?></div>
            </div>
        </div>
    </div>

    <div class="br-panels">
        <div class="br-panel">
            <h2><?php esc_html_e( 'Quick Actions', 'boreal-relay' ); ?></h2>
            <ul class="br-quick-links">
                <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=boreal-relay-escalations' ) ); ?>" class="br-quick-link escalation">
                    <span class="dashicons dashicons-phone"></span>
                    <?php esc_html_e( 'View Open Escalations', 'boreal-relay' ); ?>
                    <?php if ( $escalation->get_open_count() > 0 ): ?>
                        <span class="br-ql-badge"><?php echo esc_html( $escalation->get_open_count() ); ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=boreal-relay-conversations' ) ); ?>" class="br-quick-link">
                    <span class="dashicons dashicons-format-chat"></span>
                    <?php esc_html_e( 'Browse Conversations', 'boreal-relay' ); ?>
                </a></li>
                <li>
                    <?php if ( $br_is_pro ): ?>
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=boreal-relay-knowledge&add=1' ), 'boreal_relay_knowledge_view' ) ); ?>" class="br-quick-link">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php esc_html_e( 'Add Knowledge Base Entry', 'boreal-relay' ); ?>
                    </a>
                    <?php else: ?>
                    <a href="<?php echo esc_url( $br_pro_product_url ); ?>" class="br-quick-link" target="_blank" rel="noopener noreferrer">
                        <span class="dashicons dashicons-lock"></span>
                        <?php esc_html_e( 'Compare Pro knowledge tools', 'boreal-relay' ); ?>
                    </a>
                    <?php endif; ?>
                </li>
                <li><a href="<?php echo esc_url( admin_url( 'admin.php?page=boreal-relay-settings' ) ); ?>" class="br-quick-link">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php esc_html_e( 'Plugin Settings', 'boreal-relay' ); ?>
                </a></li>
            </ul>
        </div>

        <div class="br-panel">
            <h2><?php esc_html_e( 'Feedback Summary', 'boreal-relay' ); ?></h2>
            <?php $br_total_feedback = $stats['helpful_count'] + $stats['unhelpful_count']; ?>
            <?php if ( $br_total_feedback > 0 ): ?>
                <?php $br_helpful_percentage = round( ( $stats['helpful_count'] / $br_total_feedback ) * 100 ); ?>
                <div class="br-feedback-summary">
                    <div class="br-fb-bar-wrap">
                        <div class="br-fb-bar" style="width:<?php echo esc_attr( $br_helpful_percentage ); ?>%"></div>
                    </div>
                    <p>
                        <strong><?php echo esc_html( $br_helpful_percentage ); ?>%</strong>
                        <?php esc_html_e( 'of rated responses were marked helpful', 'boreal-relay' ); ?>
                        (<?php echo esc_html( $stats['helpful_count'] ); ?> 👍 / <?php echo esc_html( $stats['unhelpful_count'] ); ?> 👎)
                    </p>
                    <p class="br-hint">
                        <?php
                        /* translators: %s: knowledge base URL. */
                        $br_feedback_message = $br_is_pro
                            ? __( 'Review unhelpful responses in your <a href="%s">Knowledge Base</a> and turn them into approved answers.', 'boreal-relay' )
                            : __( 'Feedback is saved in Free. <a href="%s">Boreal Relay Pro</a> turns unhelpful answers into a review queue you can improve and approve.', 'boreal-relay' );
                        printf(
                            wp_kses(
                                $br_feedback_message,
                                array( 'a' => array( 'href' => array() ) )
                            ),
                            esc_url( $br_is_pro ? admin_url( 'admin.php?page=boreal-relay-knowledge' ) : $br_pro_product_url )
                        );
                        ?>
                    </p>
                </div>
            <?php else: ?>
                <p class="br-hint"><?php esc_html_e( 'No customer feedback yet. Feedback appears after customers rate AI responses.', 'boreal-relay' ); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ( ! get_option( 'boreal_relay_openai_api_key' ) ): ?>
    <div class="br-setup-banner">
        <span class="dashicons dashicons-warning" style="color:#d97706;font-size:22px;"></span>
        <div>
            <strong><?php esc_html_e( 'Setup Required:', 'boreal-relay' ); ?></strong>
            <?php esc_html_e( 'Your AI assistant needs an OpenAI API key to start working.', 'boreal-relay' ); ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=boreal-relay-settings' ) ); ?>" class="button button-primary" style="margin-left:10px;">
                <?php esc_html_e( 'Configure Now', 'boreal-relay' ); ?>
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>
