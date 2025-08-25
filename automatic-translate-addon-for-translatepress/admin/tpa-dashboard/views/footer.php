<?php
// Security: Validate text domain
if (!isset($text_domain) || empty($text_domain)) {
    $text_domain = 'TPA'; // Default fallback
}
$text_domain = sanitize_text_field($text_domain);


?>
<div class="tpa-dashboard-info">
    <div class="tpa-dashboard-info-links">
        <p>
            <?php esc_html_e('Made with ❤️ by', $text_domain); ?>
            <span class="logo">
                <a href="<?php echo esc_url('https://coolplugins.net/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=author_page&utm_content=dashboard_footer'); ?>" target="_blank">
                    <img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/cool-plugins-logo-black.svg'); ?>" alt="<?php esc_attr_e('Cool Plugins Logo', $text_domain); ?>">
                </a>
            </span>
        </p>
        <a href="<?php echo esc_url('https://coolplugins.net/support/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=support&utm_content=dashboard_footer'); ?>" target="_blank"><?php esc_html_e('Support', $text_domain); ?></a> |
        <a href="<?php echo esc_url('https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_footer'); ?>" target="_blank"><?php esc_html_e('Docs', $text_domain); ?></a>
        <div class="tpa-dashboard-social-icons">
            <?php
            $social_links = [
                ['https://www.facebook.com/coolplugins/', 'facebook.svg', esc_html__('Facebook', $text_domain)],
                ['https://linkedin.com/company/coolplugins', 'linkedin.svg', esc_html__('Linkedin', $text_domain)],
                ['https://x.com/cool_plugins', 'twitter.svg', esc_html__('Twitter', $text_domain)],
                ['https://www.youtube.com/@cool_plugins', 'youtube.svg', esc_html__('YouTube Channel', $text_domain)]
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
