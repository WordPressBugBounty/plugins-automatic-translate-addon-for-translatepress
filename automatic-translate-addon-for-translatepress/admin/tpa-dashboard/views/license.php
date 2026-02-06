<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tpa-dashboard-license">
    <div class="tpa-dashboard-license-container">
    <div class="header">
        <h1><?php esc_html_e('License Key', 'automatic-translate-addon-for-translatepress'); ?></h1>
        <div class="tpa-dashboard-status">
            <span><?php esc_html_e('Free', 'automatic-translate-addon-for-translatepress'); ?></span>
            <a href="https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=license#pricing" class='tpa-dashboard-btn' target="_blank">
              <img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/upgrade-now.svg'); ?>" alt="<?php esc_html_e('Upgrade Now', 'automatic-translate-addon-for-translatepress'); ?>">
                <?php esc_html_e('Upgrade Now', 'automatic-translate-addon-for-translatepress'); ?>
            </a>
        </div>
    </div>
    <p><?php esc_html_e('Your license key provides access to pro version updates and support.', 'automatic-translate-addon-for-translatepress'); ?></p>
    
    <p>
    <?php 
    printf(
        // translators: %1$s: Opening strong tag, %2$s: Closing strong tag
        esc_html__( "You're using %1\$sAI Translation For TranslatePress (free)%2\$s - no license needed. Enjoy! 😊", 'automatic-translate-addon-for-translatepress' ),
        '<strong>',
        '</strong>'
    ); 
    ?>
    </p>

    <div class="tpa-dashboard-upgrade-box">
        <p>
            <?php esc_html_e('To unlock more features, consider', 'automatic-translate-addon-for-translatepress'); ?>
            <a href="https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=license#pricing" target="_blank"><?php esc_html_e('upgrading to Pro', 'automatic-translate-addon-for-translatepress'); ?></a>.
        </p>
        <em><?php esc_html_e('As a valued user, you automatically receive an exclusive discount on the Annual License and an even greater discount on the POPULAR Lifetime License at checkout!', 'automatic-translate-addon-for-translatepress'); ?></em>
    </div>
    </div>
</div>
