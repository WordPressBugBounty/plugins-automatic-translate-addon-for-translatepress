<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


?>
<div class="tpa-dashboard-info">
    <div class="tpa-dashboard-info-links">
        <p>
            <?php esc_html_e('Made with ❤️ by', 'automatic-translate-addon-for-translatepress'); ?>
            <span class="logo">
                <a href="<?php echo esc_url('https://coolplugins.net/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=author_page&utm_content=dashboard_footer'); ?>" target="_blank">
                    <img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/cool-plugins-logo-black.svg'); ?>" alt="<?php esc_attr_e('Cool Plugins Logo', 'automatic-translate-addon-for-translatepress'); ?>">
                </a>
            </span>
        </p>
        <a href="<?php echo esc_url('https://coolplugins.net/support/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=support&utm_content=dashboard_footer'); ?>" target="_blank"><?php esc_html_e('Support', 'automatic-translate-addon-for-translatepress'); ?></a> |
        <a href="<?php echo esc_url('https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_footer'); ?>" target="_blank"><?php esc_html_e('Docs', 'automatic-translate-addon-for-translatepress'); ?></a>
        <div class="tpa-dashboard-social-icons">
            <?php
            $social_links = [
                ['https://www.facebook.com/coolplugins/', 'facebook.svg', esc_html__('Facebook', 'automatic-translate-addon-for-translatepress')],
                ['https://linkedin.com/company/coolplugins', 'linkedin.svg', esc_html__('Linkedin', 'automatic-translate-addon-for-translatepress')],
                ['https://x.com/cool_plugins', 'twitter.svg', esc_html__('Twitter', 'automatic-translate-addon-for-translatepress')],
                ['https://www.youtube.com/@cool_plugins', 'youtube.svg', esc_html__('YouTube Channel', 'automatic-translate-addon-for-translatepress')]
            ];

            foreach ($social_links as $link) {
                echo '<a href="' . esc_url($link[0]) . '" target="_blank">
                        <img src="' . esc_url(TPA_URL . 'admin/tpa-dashboard/images/' . $link[1]) . '" alt="' . esc_attr($link[2]) . '">
                      </a>';
            }
            ?>
        </div>
    </div>
</div>
