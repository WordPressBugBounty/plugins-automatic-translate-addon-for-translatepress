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

        <?php if(get_option('tpa_provider_chrome_enabled') == '1') : ?>
            <h2 class="tpa-section-title tpa-section-title-with-icon">
                <span class="tpa-section-icon tpa-icon-sparkle" aria-hidden="true">
                    <img
                        src="<?php echo esc_url( TPA_URL . 'assets/images/single-page-chrome-translation.svg' ); ?>"
                        alt=""
                        width="20"
                        height="20"
                        loading="lazy"
                        decoding="async"
                    />
                </span>
                <?php esc_html_e('Chrome AI Configuration', 'automatic-translate-addon-for-translatepress'); ?>
            </h2>
            <p class="tpa-section-description">
                <?php esc_html_e('Use Chrome’s built-in AI to translate strings. Configure and test it here.', 'automatic-translate-addon-for-translatepress'); ?>
            </p>
            <div class="tpa-dashboard-chrome-ai-settings">
                <!-- Chrome Local AI Notice -->
                <div id="tpa-chrome-local-ai-notice" class="tpa-chrome-local-ai-notice">
                    <?php if ( ! $tpa_has_translation_langs ) : ?>
                        <span class="tpa-chrome-no-languages-content"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" id="error"><g><rect fill="none"/></g><g><path d="M12 7c.55 0 1 .45 1 1v4c0 .55-.45 1-1 1s-1-.45-1-1V8c0-.55.45-1 1-1zm-.01-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm1-3h-2v-2h2v2z"></path></g></svg><?php
                            echo wp_kses(
                                sprintf(
                                    /* translators: %s: link to the TranslatePress settings page. */
                                    __( 'Add at least %s to use the Chrome AI translation test', 'automatic-translate-addon-for-translatepress' ),
                                    sprintf(
                                        '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                                        esc_url( admin_url( 'options-general.php?page=translate-press' ) ),
                                        esc_html__( 'one language in TranslatePress', 'automatic-translate-addon-for-translatepress' )
                                    )
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
                    <?php else : ?>
                        <div class="tpa-chrome-local-ai-notice-content">
                            <h3 id="tpa-chrome-notice-heading" class="tpa-chrome-notice-heading"></h3>
                            <div id="tpa-chrome-notice-message" class="tpa-chrome-notice-message"></div>
                        </div>

                        <!-- Test Translation Section -->
                        <div id="tpa-chrome-test-translation" class="tpa-chrome-test-translation">
                            <h3 class="tpa-chrome-test-translation-heading"><?php esc_html_e('Chrome AI Translation Test', 'automatic-translate-addon-for-translatepress'); ?></h3>
                            <p class="tpa-chrome-test-translation-description"><?php esc_html_e('Check whether Chrome AI Translation is properly configured by translating a sample text.', 'automatic-translate-addon-for-translatepress'); ?></p>

                            <div class="tpa-chrome-test-translation-language-pair">
                                <label class="tpa-chrome-test-translation-label"><?php esc_html_e('Language Pair:', 'automatic-translate-addon-for-translatepress'); ?></label>
                                <select id="tpa-test-translation-source" class="tpa-chrome-test-translation-source"></select>
                                <span class="tpa-chrome-test-translation-arrow">→</span>
                                <select id="tpa-test-translation-target" class="tpa-chrome-test-translation-target"></select>
                            </div>

                            <button id="tpa-test-translation-btn" class="tpa-dashboard-btn primary tpa-chrome-test-translation-btn">
                                <?php esc_html_e('Test Translation', 'automatic-translate-addon-for-translatepress'); ?>
                            </button>

                            <div id="tpa-test-translation-result" class="tpa-chrome-test-translation-result"></div>
                            <div id="tpa-test-translation-error" class="tpa-chrome-test-translation-error"></div>
                        </div>
                    <?php endif; ?>
                </div>
                
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