<div class="tpa-dashboard-license">
    <div class="tpa-dashboard-license-container">
    <div class="header">
        <h1><?php esc_html_e('License Key', $text_domain); ?></h1>
        <div class="tpa-dashboard-status">
            <span><?php esc_html_e('Free', $text_domain); ?></span>
            <a href="https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=license#pricing" class='tpa-dashboard-btn' target="_blank">
              <img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/upgrade-now.svg'); ?>" alt="<?php esc_html_e('Upgrade Now', $text_domain); ?>">
                <?php esc_html_e('Upgrade Now', $text_domain); ?>
            </a>
        </div>
    </div>
    <p><?php esc_html_e('Your license key provides access to pro version updates and support.', $text_domain); ?></p>
    
    <p>
    <?php 
    printf(
        esc_html__( "You're using %sAI Translation For TranslatePress (free)%s - no license needed. Enjoy! 😊", $text_domain ),
        '<strong>',
        '</strong>'
    ); 
    ?>
    </p>

    <div class="tpa-dashboard-upgrade-box">
        <p>
            <?php esc_html_e('To unlock more features, consider', $text_domain); ?>
            <a href="https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=license#pricing" target="_blank"><?php esc_html_e('upgrading to Pro', $text_domain); ?></a>.
        </p>
        <em><?php esc_html_e('As a valued user, you automatically receive an exclusive discount on the Annual License and an even greater discount on the POPULAR Lifetime License at checkout!', $text_domain); ?></em>
    </div>
    </div>
</div>
