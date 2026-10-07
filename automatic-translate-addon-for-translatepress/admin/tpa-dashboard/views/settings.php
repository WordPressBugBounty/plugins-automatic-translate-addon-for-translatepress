<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
    if ( ! current_user_can('manage_options') ) { 
        return; 
    }
    // Initialize variables
    $feedback_opt_in = 'no';
    $form_success = false;

    // Process form submission with complete validation
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tpa_optin_nonce'])) {
        
        // Verify nonce for security
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tpa_optin_nonce'])), 'tpa_save_optin_settings')) {
            wp_die(esc_html__('Security check failed. Please try again.', 'automatic-translate-addon-for-translatepress'));
        }
        
        // Check user capabilities
        // Handle feedback checkbox with proper validation
        if (get_option('cpfm_opt_in_choice_cool_translations')) {
            // Sanitize and validate checkbox input  
            $feedback_opt_in = isset($_POST['tpa-dashboard-feedback-checkbox']) && 
                              sanitize_text_field(wp_unslash($_POST['tpa-dashboard-feedback-checkbox'])) === '1' ? 'yes' : 'no';
            
            update_option('tpa_feedback_opt_in', sanitize_text_field($feedback_opt_in));
            $form_success = true;
        }

        // If user opted out, remove the cron job
        if ($feedback_opt_in === 'no' && wp_next_scheduled('tpa_extra_data_update')) {
            wp_clear_scheduled_hook('tpa_extra_data_update');
        }

        // If user opted in, schedule the cron job
        if ($feedback_opt_in === 'yes' && !wp_next_scheduled('tpa_extra_data_update')) {
            wp_schedule_event(time(), 'every_30_days', 'tpa_extra_data_update');   

            if (class_exists('TPA_cronjob')) {
                TPA_cronjob::tpa_send_data();
            } 
        }
    }
?>
<div class="tpa-dashboard-settings">
    <div class="tpa-dashboard-settings-container">
        <div class="header">
            <h1><?php esc_html_e('Settings', 'automatic-translate-addon-for-translatepress'); ?></h1>
            <div class="tpa-dashboard-status">
                <span class="license-type"><?php esc_html_e('Free', 'automatic-translate-addon-for-translatepress'); ?></span>
                <a href="https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=settings#pricing" 
                   class='tpa-dashboard-btn upgrade-btn' 
                   target="_blank"
                   rel="noopener noreferrer">
                    <img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/upgrade-now.svg'); ?>" 
                         alt="<?php esc_html_e('Upgrade Now', 'automatic-translate-addon-for-translatepress'); ?>">
                    <?php esc_html_e('Upgrade Now', 'automatic-translate-addon-for-translatepress'); ?>
                </a>
            </div>
        </div>

        <?php
        // Check if TranslatePress has at least one translation language (for Chrome AI test section).
        $tpa_trp_settings          = get_option( 'trp_settings', array() );
        $tpa_default_lang          = isset( $tpa_trp_settings['default-language'] ) ? $tpa_trp_settings['default-language'] : '';
        $tpa_publish_languages     = isset( $tpa_trp_settings['publish-languages'] ) && is_array( $tpa_trp_settings['publish-languages'] ) ? $tpa_trp_settings['publish-languages'] : array();
        $tpa_translation_languages = isset( $tpa_trp_settings['translation-languages'] ) && is_array( $tpa_trp_settings['translation-languages'] ) ? $tpa_trp_settings['translation-languages'] : array();
        $tpa_publish_languages     = count( array_diff( $tpa_publish_languages, array( $tpa_default_lang ) ) ) > 0 ? $tpa_publish_languages : $tpa_translation_languages;
        $tpa_has_translation_langs = ! empty( $tpa_publish_languages ) && count( array_diff( $tpa_publish_languages, array( $tpa_default_lang ) ) ) > 0;
        ?>

        <?php
        $user_agent_info = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
        $chrome_enabled = get_option('tpa_provider_chrome_enabled') == '1';
        $edge_enabled   = get_option('tpa_provider_edge_enabled') == '1';

        $is_edge   = strpos($user_agent_info, 'Edg') !== false;
        $is_chrome = strpos($user_agent_info, 'Chrome') !== false && !$is_edge;

        if ($is_edge) {
            $browserType   = 'edge';
            $browser_title = 'Edge';
        } elseif ($is_chrome) {
            $browserType   = 'chrome';
            $browser_title = 'Chrome';
        } else {
            // Other browsers or fallback
            if ($chrome_enabled) {
                $browserType   = 'chrome';
                $browser_title = 'Chrome';
            } elseif ($edge_enabled) {
                $browserType   = 'edge';
                $browser_title = 'Edge';
            } else {
                $browserType   = 'chrome';
                $browser_title = 'Chrome';
            }
        }

        if ( ($browserType === 'chrome' && $chrome_enabled) || ($browserType === 'edge' && $edge_enabled) ) :
        ?>
            <div class="tpa-dashboard-chrome-ai-settings">
                <?php if ( ! $tpa_has_translation_langs ) : ?>
                    <div class="tpa-dashboard-settings-card">
                        <span class="tpa-chrome-no-languages-content"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" id="error"><g><rect fill="none"/></g><g><path d="M12 7c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V8c0-.55.45-1 1-1zm-.01-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-3h-2v-2h2v2z"></path></g></svg><?php
                            echo wp_kses(
                                sprintf(
                                    /* translators: %s: link to the TranslatePress settings page. */
                                    __( 'Add at least %1$s to use the %2$s AI translation test', 'automatic-translate-addon-for-translatepress' ),
                                    sprintf(
                                        '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                                        esc_url( admin_url( 'options-general.php?page=translate-press' ) ),
                                        esc_html__( 'one language in TranslatePress', 'automatic-translate-addon-for-translatepress' )
                                    ),
                                    esc_html( $browser_title )
                                ),
                                array(
                                    'a' => array(
                                        'href'   => array(),
                                        'target' => array(),
                                        'rel'    => array(),
                                    ),
                                )
                            );
                        ?></span>
                    </div>
                <?php else : ?>
                    <!-- Chrome AI Setup Framework Container -->
                    <div id="cais-chrome-setup-container"></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('tpa_save_optin_settings', 'tpa_optin_nonce'); ?>
            <?php if (get_option('cpfm_opt_in_choice_cool_translations')) : ?>
                <div class="tpa-dashboard-feedback-container">
                    <h3 class="tpa-section-title">
                        <?php esc_html_e( 'Usage Data Sharing', 'automatic-translate-addon-for-translatepress' ); ?>
                    </h3>
                    <div class="feedback-row">
                        <input type="checkbox" 
                            id="tpa-dashboard-feedback-checkbox" 
                            name="tpa-dashboard-feedback-checkbox"
                            value="1"
                            <?php checked(get_option('tpa_feedback_opt_in'), 'yes'); ?>>
                        <p><?php esc_html_e('Help us make this plugin more compatible with your site by sharing non-sensitive site data.', 'automatic-translate-addon-for-translatepress'); ?></p>
                        <a href="#" class="tpa-see-terms">[See terms]</a>
                    </div>
                    <div id="termsBox" style="display: none;padding-left: 20px; margin-top: 10px; font-size: 12px; color: #999;">
                        <p><?php esc_html_e("Opt in to receive email updates about security improvements, new features, helpful tutorials, and occasional special offers. We'll collect:", 'automatic-translate-addon-for-translatepress'); ?><a href="https://my.coolplugins.net/terms/usage-tracking/" rel="noopener noreferrer" target="_blank"><?php esc_html_e('Click here', 'automatic-translate-addon-for-translatepress'); ?></a></p>
                        <ul style="list-style-type:auto;">
                            <li><?php esc_html_e('Your website home URL and WordPress admin email.', 'automatic-translate-addon-for-translatepress'); ?></li>
                            <li><?php esc_html_e('To check plugin compatibility, we will collect the following: list of active plugins and themes, server type, MySQL version, WordPress version, memory limit, site language and database prefix.', 'automatic-translate-addon-for-translatepress'); ?></li>
                        </ul>
                    </div>
                    <div class="tpa-dashboard-save-settings">
                        <button type="submit" class="tpa-dashboard-btn primary save-settings-btn">
                            <?php esc_html_e('Save', 'automatic-translate-addon-for-translatepress'); ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>