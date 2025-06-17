<?php

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tpa_optin_nonce']) && wp_verify_nonce($_POST['tpa_optin_nonce'], 'tpa_save_optin_settings')) {

            // Handle feedback checkbox
            if (get_option('cpfm_opt_in_choice_cool_translations')) {
                $feedback_opt_in = isset($_POST['tpa-dashboard-feedback-checkbox']) ? 'yes' : 'no';
                update_option('tpa_feedback_opt_in', $feedback_opt_in);
            }

        // If user opted out, remove the cron job
        if ($feedback_opt_in === 'no' && wp_next_scheduled('tpa_extra_data_update') ){
                
            wp_clear_scheduled_hook('tpa_extra_data_update');
        
        }

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
            <h1><?php _e('Settings', $text_domain); ?></h1>
            <div class="tpa-dashboard-status">
                <span class="license-type"><?php _e('Free', $text_domain); ?></span>
                <a href="https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=license#pricing" 
                   class='tpa-dashboard-btn upgrade-btn' 
                   target="_blank"
                   rel="noopener noreferrer">
                    <img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/upgrade-now.svg'); ?>" 
                         alt="<?php _e('Upgrade Now', $text_domain); ?>">
                    <?php _e('Upgrade Now', $text_domain); ?>
                </a>
            </div>
        </div>
        
        <p><?php _e('Configure your AI translation settings to customize how your content is translated.', $text_domain); ?></p>

        <form method="post">
            <?php wp_nonce_field('tpa_save_optin_settings', 'tpa_optin_nonce'); ?>
            <?php if (get_option('cpfm_opt_in_choice_cool_translations')) : ?>
                <div class="tpa-dashboard-feedback-container">
                    <div class="feedback-row">
                        <input type="checkbox" 
                            id="tpa-dashboard-feedback-checkbox" 
                            name="tpa-dashboard-feedback-checkbox"
                            <?php checked(get_option('tpa_feedback_opt_in'), 'yes'); ?>>
                        <p><?php _e('Help us make this plugin more compatible with your site by sharing non-sensitive site data.', $text_domain); ?></p>
                        <a href="#" class="tpa-see-terms">[See terms]</a>
                    </div>
                    <div id="termsBox" style="display: none;padding-left: 20px; margin-top: 10px; font-size: 12px; color: #999;">
                        <p><?php _e("Opt in to receive email updates about security improvements, new features, helpful tutorials, and occasional special offers. We'll collect:", $text_domain); ?></p>
                        <ul style="list-style-type:auto;">
                            <li><?php esc_html_e('Your website home URL and WordPress admin email.', $text_domain); ?></li>
                            <li><?php esc_html_e('To check plugin compatibility, we will collect the following: list of active plugins and themes, server type, MySQL version, WordPress version, memory limit, site language and database prefix.', $text_domain); ?></li>
                        </ul>
                    </div>
                    <div class="tpa-dashboard-save-settings">
                        <button type="submit" class="tpa-dashboard-btn primary save-settings-btn">
                            <?php _e('Save', $text_domain); ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>