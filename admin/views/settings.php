<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
// Detect whether secrets are stored so we can show a masked indicator.
$br_has_openai_key = (bool) get_option( 'boreal_relay_openai_api_key', '' );
?>
<div class="wrap br-wrap">
    <h1 class="br-page-title">
        <span class="dashicons dashicons-admin-settings"></span>
        <?php esc_html_e( 'Boreal Relay — Settings', 'boreal-relay' ); ?>
    </h1>

    <form method="post" action="options.php">
        <?php settings_fields( 'boreal_relay_settings' ); ?>

        <div class="br-panel">
            <h2><?php esc_html_e( 'AI Configuration', 'boreal-relay' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><label for="boreal_relay_openai_api_key"><?php esc_html_e( 'OpenAI API Key', 'boreal-relay' ); ?> <span style="color:#dc2626">*</span></label></th>
                    <td>
                        <?php if ( $br_has_openai_key ): ?>
                            <p style="margin:0 0 6px">
                                <span style="color:#16a34a">&#10003; <?php esc_html_e( 'Key saved.', 'boreal-relay' ); ?></span>
                                <?php esc_html_e( 'Enter a new key below only if you want to replace it.', 'boreal-relay' ); ?>
                            </p>
                        <?php endif; ?>
                        <input type="password"
                               id="boreal_relay_openai_api_key"
                               name="boreal_relay_openai_api_key"
                               value=""
                               class="regular-text"
                               autocomplete="new-password"
                               placeholder="<?php echo $br_has_openai_key ? esc_attr__( 'Leave blank to keep current key', 'boreal-relay' ) : 'sk-...'; ?>">
                        <p class="description">
                            <?php
                            /* translators: %s: OpenAI API keys URL. */
                             $br_api_key_message = __( 'Get your key from <a href="%s" target="_blank">platform.openai.com/api-keys</a>. Required for the AI assistant to work.', 'boreal-relay' );
                            printf(
                                wp_kses(
                                     $br_api_key_message,
                                    array( 'a' => array( 'href' => array(), 'target' => array() ) )
                                ),
                                'https://platform.openai.com/api-keys'
                            );
                            ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th><label for="boreal_relay_model"><?php esc_html_e( 'AI Model', 'boreal-relay' ); ?></label></th>
                    <td>
                        <select id="boreal_relay_model" name="boreal_relay_model">
                            <option value="gpt-4o-mini"    <?php selected( get_option( 'boreal_relay_model', 'gpt-4o-mini' ), 'gpt-4o-mini' ); ?>><?php esc_html_e( 'GPT-4o Mini (Recommended — fast & affordable)', 'boreal-relay' ); ?></option>
                            <option value="gpt-4o"         <?php selected( get_option( 'boreal_relay_model', 'gpt-4o-mini' ), 'gpt-4o' ); ?>><?php esc_html_e( 'GPT-4o (More capable, higher cost)', 'boreal-relay' ); ?></option>
                            <option value="gpt-3.5-turbo"  <?php selected( get_option( 'boreal_relay_model', 'gpt-4o-mini' ), 'gpt-3.5-turbo' ); ?>><?php esc_html_e( 'GPT-3.5 Turbo (Budget option)', 'boreal-relay' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="boreal_relay_tone"><?php esc_html_e( 'AI Tone / Personality', 'boreal-relay' ); ?></label></th>
                    <td>
                        <input type="text" id="boreal_relay_tone" name="boreal_relay_tone"
                               value="<?php echo esc_attr( get_option( 'boreal_relay_tone', 'friendly and professional' ) ); ?>"
                               class="regular-text"
                               placeholder="<?php esc_attr_e( 'e.g. friendly and professional', 'boreal-relay' ); ?>">
                        <p class="description"><?php esc_html_e( 'Describe how the AI should communicate. Examples: "warm and helpful", "professional and concise".', 'boreal-relay' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="br-panel">
            <h2><?php esc_html_e( 'Chat Widget', 'boreal-relay' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php esc_html_e( 'Widget Enabled', 'boreal-relay' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="boreal_relay_enabled" value="1" <?php checked( get_option( 'boreal_relay_enabled', '1' ), '1' ); ?>>
                            <?php esc_html_e( 'Show chat widget on the website', 'boreal-relay' ); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th><label for="boreal_relay_bot_name"><?php esc_html_e( 'Bot Name', 'boreal-relay' ); ?></label></th>
                    <td>
                        <input type="text" id="boreal_relay_bot_name" name="boreal_relay_bot_name"
                               value="<?php echo esc_attr( get_option( 'boreal_relay_bot_name', 'Relay' ) ); ?>"
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="boreal_relay_greeting"><?php esc_html_e( 'Opening Greeting', 'boreal-relay' ); ?></label></th>
                    <td>
                        <textarea id="boreal_relay_greeting" name="boreal_relay_greeting" rows="3" class="large-text"><?php echo esc_textarea( get_option( 'boreal_relay_greeting', "Hi there! 👋 I'm your Boreal Relay assistant. How can I help you today?" ) ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'The first message customers see when they open the chat.', 'boreal-relay' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="boreal_relay_theme_color"><?php esc_html_e( 'Widget Color', 'boreal-relay' ); ?></label></th>
                    <td>
                        <input type="color" id="boreal_relay_theme_color" name="boreal_relay_theme_color"
                               value="<?php echo esc_attr( get_option( 'boreal_relay_theme_color', '#2563eb' ) ); ?>">
                        <p class="description"><?php esc_html_e( 'Choose a color that matches your brand.', 'boreal-relay' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="boreal_relay_business_name"><?php esc_html_e( 'Business Name', 'boreal-relay' ); ?></label></th>
                    <td>
                        <input type="text" id="boreal_relay_business_name" name="boreal_relay_business_name"
                               value="<?php echo esc_attr( get_option( 'boreal_relay_business_name', '' ) ); ?>"
                               class="regular-text"
                               placeholder="<?php esc_attr_e( 'Your business name', 'boreal-relay' ); ?>">
                        <p class="description"><?php esc_html_e( 'Used in the AI system prompt to give the assistant context about who it represents.', 'boreal-relay' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="br-panel">
            <h2><?php esc_html_e( 'Escalation & Support', 'boreal-relay' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th><label for="boreal_relay_support_email"><?php esc_html_e( 'Support Email', 'boreal-relay' ); ?></label></th>
                    <td>
                        <input type="email" id="boreal_relay_support_email" name="boreal_relay_support_email"
                               value="<?php echo esc_attr( get_option( 'boreal_relay_support_email', get_option( 'admin_email' ) ) ); ?>"
                               class="regular-text">
                        <p class="description"><?php esc_html_e( 'Email address that receives escalation notifications when the AI cannot answer a question.', 'boreal-relay' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><label for="boreal_relay_escalation_cc"><?php esc_html_e( 'CC Email (optional)', 'boreal-relay' ); ?></label></th>
                    <td>
                        <input type="email" id="boreal_relay_escalation_cc" name="boreal_relay_escalation_cc"
                               value="<?php echo esc_attr( get_option( 'boreal_relay_escalation_cc', '' ) ); ?>"
                               class="regular-text"
                               placeholder="team@example.com">
                        <p class="description"><?php esc_html_e( 'Optional second email to CC on escalation notifications.', 'boreal-relay' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit">
            <?php submit_button( esc_html__( 'Save Settings', 'boreal-relay' ), 'primary', 'submit', false ); ?>
        </p>
    </form>
</div>
