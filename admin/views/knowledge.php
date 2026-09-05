<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
$br_is_pro  = function_exists( 'boreal_relay_has_active_pro' ) && boreal_relay_has_active_pro();
$br_pro_url = function_exists( 'boreal_relay_get_pro_url' )
    ? boreal_relay_get_pro_url( 'knowledge-base' )
    : 'https://borealform.com/boreal-relay';
?>
<div class="wrap br-wrap">
    <h1 class="br-page-title">
        <span class="dashicons dashicons-book-alt"></span>
        <?php esc_html_e( 'Knowledge Base', 'boreal-relay' ); ?>
        <?php if ( $br_is_pro ): ?>
            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=boreal-relay-knowledge&add=1' ), 'boreal_relay_knowledge_view' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New Entry', 'boreal-relay' ); ?></a>
        <?php else: ?>
            <a href="<?php echo esc_url( $br_pro_url ); ?>" class="page-title-action" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Compare Pro', 'boreal-relay' ); ?></a>
        <?php endif; ?>
    </h1>

    <?php if ( ! $br_is_pro ): ?>
    <div class="notice notice-info" style="margin-left:0">
        <p>
            <strong><?php esc_html_e( 'Included answers are read-only in Free.', 'boreal-relay' ); ?></strong>
            <?php
            /* translators: %s: Boreal Relay Pro product URL. */
            $br_upgrade_message = __( 'The assistant can use every approved answer below. Pro adds custom answers, editing, approvals, and a feedback review queue. <a href="%s" target="_blank" rel="noopener noreferrer">Compare Free and Pro</a>.', 'boreal-relay' );
            printf(
                wp_kses(
                    $br_upgrade_message,
                    array( 'a' => array( 'href' => array() ) )
                ),
                esc_url( $br_pro_url )
            );
            ?>
        </p>
    </div>
    <?php endif; ?>

    <?php if ( isset( $_GET['saved'], $_GET['_wpnonce'] ) && is_string( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'boreal_relay_kb_saved' ) ): ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Entry saved successfully.', 'boreal-relay' ); ?></p></div>
    <?php endif; ?>
    <?php if ( isset( $_GET['deleted'], $_GET['_wpnonce'] ) && is_string( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'boreal_relay_kb_deleted' ) ): ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Entry deleted.', 'boreal-relay' ); ?></p></div>
    <?php endif; ?>

    <?php
    $br_has_view_nonce = isset( $_GET['_wpnonce'] ) && is_string( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'boreal_relay_knowledge_view' );
    $br_show_form      = $br_is_pro && ( ( $br_has_view_nonce && isset( $_GET['add'] ) ) || $edit_id > 0 );
    $br_edit_entry = null;
    if ( $edit_id > 0 ) {
        foreach ( $entries as $br_entry ) {
            if ( (int) $br_entry->id === $edit_id ) { $br_edit_entry = $br_entry; break; }
        }
    }
    $br_prefill_question = isset( $_GET['q'] ) && is_string( $_GET['q'] ) && $br_has_view_nonce ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
    ?>

    <?php if ( $br_show_form ): ?>
    <div class="br-panel br-form-panel">
        <h2><?php echo $br_edit_entry ? esc_html__( 'Edit Entry', 'boreal-relay' ) : esc_html__( 'Add New Entry', 'boreal-relay' ); ?></h2>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'boreal_relay_kb_save' ); ?>
            <input type="hidden" name="action" value="boreal_relay_save_kb">
            <?php if ( $br_edit_entry ): ?>
                <input type="hidden" name="kb_id" value="<?php echo esc_attr( $br_edit_entry->id ); ?>">
            <?php endif; ?>
            <table class="form-table">
                <tr>
                    <th><label for="question"><?php esc_html_e( 'Question / Trigger', 'boreal-relay' ); ?></label></th>
                    <td>
                        <input type="text" id="question" name="question" class="regular-text"
                               value="<?php echo esc_attr( $br_edit_entry ? $br_edit_entry->question : $br_prefill_question ); ?>" required>
                        <p class="description"><?php esc_html_e( 'The question or topic this entry answers.', 'boreal-relay' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="answer"><?php esc_html_e( 'Answer', 'boreal-relay' ); ?></label></th>
                    <td>
                        <textarea id="answer" name="answer" rows="5" class="large-text" required><?php echo esc_textarea( $br_edit_entry ? $br_edit_entry->answer : '' ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'The response the AI will use when this topic comes up.', 'boreal-relay' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="category"><?php esc_html_e( 'Category', 'boreal-relay' ); ?></label></th>
                    <td>
                        <select id="category" name="category">
                            <?php
                            $br_categories = array(
                                'general', 'overview', 'location', 'products', 'ordering', 'quotes',
                                'billing', 'payments', 'delivery', 'returns', 'accounts', 'privacy',
                                'accessibility', 'technical', 'contact', 'hours',
                            );
                            $br_selected_category = $br_edit_entry ? $br_edit_entry->category : 'general';
                            foreach ( $br_categories as $br_category ):
                            ?>
                                <option value="<?php echo esc_attr( $br_category ); ?>" <?php selected( $br_selected_category, $br_category ); ?>><?php echo esc_html( ucfirst( $br_category ) ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Approved / Active', 'boreal-relay' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="approved" value="1" <?php checked( $br_edit_entry ? $br_edit_entry->approved : 1 ); ?>>
                            <?php esc_html_e( 'Active (AI will use this entry)', 'boreal-relay' ); ?>
                        </label>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary"><?php esc_html_e( 'Save Entry', 'boreal-relay' ); ?></button>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=boreal-relay-knowledge' ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'boreal-relay' ); ?></a>
            </p>
        </form>
    </div>
    <?php endif; ?>

    <div class="br-table-wrap">
    <table class="wp-list-table widefat striped br-table">
        <thead>
            <tr>
                <th style="min-width:180px"><?php esc_html_e( 'Question', 'boreal-relay' ); ?></th>
                <th style="min-width:180px" class="br-col-hide-mobile"><?php esc_html_e( 'Answer', 'boreal-relay' ); ?></th>
                <th style="min-width:90px"><?php esc_html_e( 'Category', 'boreal-relay' ); ?></th>
                <th style="min-width:70px" class="br-col-hide-mobile"><?php esc_html_e( 'Source', 'boreal-relay' ); ?></th>
                <th style="min-width:50px" class="br-col-hide-mobile"><?php esc_html_e( 'Uses', 'boreal-relay' ); ?></th>
                <th style="min-width:70px"><?php esc_html_e( 'Status', 'boreal-relay' ); ?></th>
                <th style="min-width:110px"><?php esc_html_e( 'Actions', 'boreal-relay' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $entries ) ): ?>
            <tr><td colspan="7" class="br-empty-row"><?php esc_html_e( 'No entries yet.', 'boreal-relay' ); ?></td></tr>
            <?php endif; ?>
            <?php foreach ( $entries as $br_entry ): ?>
            <tr>
                <td style="word-break:break-word;white-space:normal"><?php echo esc_html( $br_entry->question ); ?></td>
                <td class="br-answer-preview br-col-hide-mobile"><?php echo esc_html( wp_trim_words( $br_entry->answer, 20 ) ); ?></td>
                <td><span class="br-cat-badge"><?php echo esc_html( $br_entry->category ); ?></span></td>
                <td class="br-col-hide-mobile"><span class="br-source-<?php echo esc_attr( $br_entry->source ); ?>"><?php echo esc_html( ucfirst( $br_entry->source ) ); ?></span></td>
                <td class="br-col-hide-mobile"><?php echo esc_html( $br_entry->use_count ); ?></td>
                <td>
                    <?php if ( $br_entry->approved ): ?>
                        <span class="br-badge-ok"><?php esc_html_e( 'Active', 'boreal-relay' ); ?></span>
                    <?php else: ?>
                        <span class="br-badge-escalated"><?php esc_html_e( 'Pending', 'boreal-relay' ); ?></span>
                    <?php endif; ?>
                </td>
                <td style="white-space:nowrap">
                    <?php if ( $br_is_pro ): ?>
                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=boreal-relay-knowledge&edit=' . absint( $br_entry->id ) ), 'boreal_relay_knowledge_view' ) ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'boreal-relay' ); ?></a>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('<?php esc_attr_e( 'Delete this entry?', 'boreal-relay' ); ?>')">
                            <?php wp_nonce_field( 'boreal_relay_kb_delete' ); ?>
                            <input type="hidden" name="action" value="boreal_relay_delete_kb">
                            <input type="hidden" name="kb_id" value="<?php echo esc_attr( $br_entry->id ); ?>">
                            <button type="submit" class="button button-small" style="color:#dc2626;"><?php esc_html_e( 'Del', 'boreal-relay' ); ?></button>
                        </form>
                    <?php else: ?>
                        <a href="<?php echo esc_url( $br_pro_url ); ?>" class="button button-small" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e( 'Available in Boreal Relay Pro', 'boreal-relay' ); ?>"><?php esc_html_e( 'Pro: Edit', 'boreal-relay' ); ?></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <p class="br-hint" style="margin-top:12px">
        <strong><?php esc_html_e( 'Tip:', 'boreal-relay' ); ?></strong>
        <?php esc_html_e( 'Pro turns unhelpful responses into pending drafts. Review and correct each draft before approving it for future answers.', 'boreal-relay' ); ?>
    </p>
</div>
