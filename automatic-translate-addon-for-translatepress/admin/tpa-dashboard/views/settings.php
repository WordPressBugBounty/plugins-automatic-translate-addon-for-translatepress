<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
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
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'automatic-translate-addon-for-translatepress'));
        }

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
        
        <p><?php esc_html_e('Configure your AI translation settings to customize how your content is translated.', 'automatic-translate-addon-for-translatepress'); ?></p>

        <?php if(get_option('tpa_provider_chrome_enabled') == '1') : ?>
            <div class="tpa-dashboard-chrome-ai-settings">
                <!-- Chrome Local AI Notice -->
                <div id="tpa-chrome-local-ai-notice" style="display: none; border: 1px solid #e5e7eb; background: #fff5f5; padding: 24px; border-radius: 8px; margin: 20px 0;">
                    <div style="color: #dc2626; font-size: 14px; line-height: 1.5;">
                        <h3 id="tpa-chrome-notice-heading" style="font-weight: 600; margin: 0 0 12px 0; font-size: 16px;"></h3>
                        <div id="tpa-chrome-notice-message"></div>
                    </div>
                </div>
                
                <!-- Test Translation Section -->
                <div id="tpa-chrome-test-translation" style="display: none; border: 1px solid #e5e7eb; background: #ffffff; padding: 24px; border-radius: 8px; margin: 20px 0;">
                    <h3 style="font-weight: 600; margin: 0 0 16px 0; font-size: 16px;"><?php esc_html_e('Chrome AI Translation Test', 'automatic-translate-addon-for-translatepress'); ?></h3>
                    <p style="margin-bottom: 16px; color: #666; font-size: 14px;"><?php esc_html_e('Check whether Chrome AI Translation is properly configured by translating a sample text.', 'automatic-translate-addon-for-translatepress'); ?></p>
                    
                    <div style="margin-bottom: 16px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px;"><?php esc_html_e('Language Pair:', 'automatic-translate-addon-for-translatepress'); ?></label>
                        <select id="tpa-test-translation-source" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px; margin-right: 8px; width: 10rem;"></select>
                        <span style="margin: 0 8px;">→</span>
                        <select id="tpa-test-translation-target" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px; width: 10rem;"></select>
                    </div>
                    
                    <button id="tpa-test-translation-btn" class="tpa-dashboard-btn primary" style="margin-bottom: 16px;">
                        <?php esc_html_e('Test Translation', 'automatic-translate-addon-for-translatepress'); ?>
                    </button>
                    
                    <div id="tpa-test-translation-result" style="display: none; margin-top: 16px; padding: 12px; border-radius: 4px; font-size: 14px;"></div>
                    <div id="tpa-test-translation-error" style="display: none; margin-top: 16px; padding: 12px; background: #fee; border: 1px solid #fcc; border-radius: 4px; color: #c33; font-size: 14px;"></div>
                </div>
            </div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('tpa_save_optin_settings', 'tpa_optin_nonce'); ?>
            <?php if (get_option('cpfm_opt_in_choice_cool_translations')) : ?>
                <div class="tpa-dashboard-feedback-container">
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
                        <p><?php esc_html_e("Opt in to receive email updates about security improvements, new features, helpful tutorials, and occasional special offers. We'll collect:", 'automatic-translate-addon-for-translatepress'); ?><a href="https://my.coolplugins.net/terms/usage-tracking/" target="_blank"><?php esc_html_e('Click here', 'automatic-translate-addon-for-translatepress'); ?></a></p>
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