<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- Right Sidebar -->
<div class="tpa-dashboard-sidebar">
    <div class="tpa-dashboard-status">
        <h3><?php esc_html_e('Auto Translation status', 'automatic-translate-addon-for-translatepress'); ?></h3>
        <div class="tpa-dashboard-sts-top">
            <?php

            $all_data = get_option('cpt_dashboard_data', array());

            if (!is_array($all_data) || !isset($all_data['tpa'])) {

                $all_data['tpa'] = []; // Ensure $all_data['tpa'] is an array

            }

            $totals = array_reduce($all_data['tpa'] ?? [], function($carry, $translation) {
                // Ensure all values are properly handled
                $carry['string_count'] += intval($translation['string_count'] ?? 0);
                $carry['character_count'] += intval($translation['character_count'] ?? 0);
                $carry['time_taken'] += intval($translation['time_taken'] ?? 0);
                
                // Track unique post IDs
                if (!empty($translation['post_id'])) {
                    $carry['plugins_themes'][$translation['post_id']] = 1;
                }
                return $carry;
            }, ['string_count' => 0, 'character_count' => 0, 'time_taken' => 0, 'plugins_themes' => []]);
            // Update the time taken string using the new function
            $time_taken_str = tpa_format_time_taken($totals['time_taken']);
            ?>
            <span><?php echo esc_html(tpa_format_number($totals['string_count'], 'automatic-translate-addon-for-translatepress')); ?></span>
            <span><?php esc_html_e('Total Strings Translated!', 'automatic-translate-addon-for-translatepress'); ?></span>
        </div>
        <ul class="tpa-dashboard-sts-btm">
            <li><span><?php esc_html_e('Total Characters', 'automatic-translate-addon-for-translatepress'); ?></span> <span><?php echo esc_html(tpa_format_number($totals['character_count'], 'automatic-translate-addon-for-translatepress')); ?></span></li>
            <li><span><?php esc_html_e('Total Pages / Posts', 'automatic-translate-addon-for-translatepress'); ?></span> <span><?php echo esc_html(count($totals['plugins_themes'])); ?></span></li>
            <li><span><?php esc_html_e('Time Taken', 'automatic-translate-addon-for-translatepress'); ?></span> <span><?php echo esc_html($time_taken_str); ?></span></li>
        </ul>
    </div>
    <div class="tpa-dashboard-translate-full">
        <h3><?php esc_html_e('Automatically Translate Plugins & Themes', 'automatic-translate-addon-for-translatepress'); ?></h3>
        <div class="tpa-dashboard-addon first">
            <div class="tpa-dashboard-addon-l">
                <strong><?php echo esc_html( tpa_get_plugin_display_name( 'automatic-translator-addon-for-loco-translate' ) ); ?></strong>
                <span class="addon-desc"><?php esc_html_e('LocoAI to translate plugins and themes.', 'automatic-translate-addon-for-translatepress'); ?></span>
                <?php
                    if ( ! function_exists( 'is_plugin_active' ) ) {
                        require_once ABSPATH . 'wp-admin/includes/plugin.php';
                    }
                    $tw_plugin_file = 'automatic-translator-addon-for-loco-translate/automatic-translator-addon-for-loco-translate.php';
                    $tw_pro_plugin_file = 'loco-automatic-translate-addon-pro/loco-automatic-translate-addon-pro.php';
                    $tw_installed   = tpa_is_plugin_installed( 'automatic-translator-addon-for-loco-translate' );
                    $tw_active      = false;
                    if ( function_exists( 'is_plugin_active' ) ) {
                        $tw_active = is_plugin_active( $tw_plugin_file ) || is_plugin_active( $tw_pro_plugin_file );
                    }
                ?>

                <?php if ( $tw_installed && $tw_active ): ?>
                    <span class="installed"><?php esc_html_e('Activated', 'automatic-translate-addon-for-translatepress'); ?></span>
                <?php else: ?>
                    <button
                        type="button"
                        class="tpa-dashboard-btn tpa-install-plugin"
                        data-slug="automatic-translator-addon-for-loco-translate"
                        data-nonce="<?php echo esc_attr( wp_create_nonce( 'tpa_install_nonce' ) ); ?>"
                    >
                        <?php echo esc_html( $tw_installed ? __( 'Activate', 'automatic-translate-addon-for-translatepress' ) : __( 'Install', 'automatic-translate-addon-for-translatepress' ) ); ?>
                    </button>
                    <div class="tpa-install-message" aria-live="polite" style="margin-top:8px;"></div>
                <?php endif; ?>
            </div>
            <div class="tpa-dashboard-addon-r">
                <img src="<?php echo esc_url( TPA_URL . 'admin/tpa-dashboard/images/atlt-logo.png' ); ?>" alt="<?php esc_attr_e('TranslatePress Addon', 'automatic-translate-addon-for-translatepress'); ?>">
            </div>
        </div>
    </div>
    <div class="tpa-dashboard-rate-us">
        <h3><?php esc_html_e('Rate Us ⭐⭐⭐⭐⭐', 'automatic-translate-addon-for-translatepress'); ?></h3>
        <p><?php esc_html_e('We\'d love your feedback! Hope this addon made auto-translations easier for you.', 'automatic-translate-addon-for-translatepress'); ?></p>
        <a href="https://wordpress.org/support/plugin/automatic-translate-addon-for-translatepress/reviews/#new-post" class="review-link" target="_blank"><?php esc_html_e('Submit a Review →', 'automatic-translate-addon-for-translatepress'); ?></a>
    </div>
</div>

<?php

function tpa_format_time_taken($time_taken) {
    if ($time_taken === 0) return esc_html__('0', 'automatic-translate-addon-for-translatepress');
    if ($time_taken < 60) return sprintf(
        // translators: %d: Number of seconds
        esc_html__('%d sec', 'automatic-translate-addon-for-translatepress'), $time_taken);
    if ($time_taken < 3600) {
        $min = floor($time_taken / 60);
        $sec = $time_taken % 60;
        return sprintf(
            // translators: %1$d: Number of minutes, %2$d: Number of seconds
            esc_html__('%1$d min %2$d sec', 'automatic-translate-addon-for-translatepress'), $min, $sec
        );
    }
    $hours = floor($time_taken / 3600);
    $min = floor(($time_taken % 3600) / 60);
    return sprintf(
        // translators: %1$d: Number of hours, %2$d: Number of minutes
        esc_html__('%1$d hours %2$d min', 'automatic-translate-addon-for-translatepress'), $hours, $min
    );
}

function tpa_is_plugin_installed($plugin_slug) {
    $plugins = get_plugins();
    // Check if the plugin is installed
    if ($plugin_slug === 'automatic-translator-addon-for-loco-translate') {
        return isset($plugins['automatic-translator-addon-for-loco-translate/automatic-translator-addon-for-loco-translate.php']) || isset($plugins['loco-automatic-translate-addon-pro/loco-automatic-translate-addon-pro.php']);
    }
    return false; // Return false if no match found
}

function tpa_get_plugin_display_name($plugin_slug) {
    if ( ! function_exists( 'is_plugin_active' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $plugins = get_plugins();

    // Define free and pro plugin paths
    $plugin_paths = [
        'automatic-translator-addon-for-loco-translate' => [
            'free' => 'automatic-translator-addon-for-loco-translate/automatic-translator-addon-for-loco-translate.php',
            'pro'  => 'loco-automatic-translate-addon-pro/loco-automatic-translate-addon-pro.php',
            'free_name' => esc_html__('LocoAI – Auto Translate for Loco Translate', 'automatic-translate-addon-for-translatepress'),
            'pro_name'  => esc_html__('LocoAI – Auto Translate for Loco Translate (Pro)', 'automatic-translate-addon-for-translatepress'),
        ]
    ];

    // Check if the provided plugin slug exists
    if (!isset($plugin_paths[$plugin_slug])) {
        return $plugin_slug;
    }

    $path_info = $plugin_paths[$plugin_slug];

    // 1. Check if Pro is active
    if (isset($path_info['pro']) && is_plugin_active($path_info['pro'])) {
        return $path_info['pro_name'];
    }

    // 2. Check if Free is active
    if (isset($path_info['free']) && is_plugin_active($path_info['free'])) {
        return $path_info['free_name'];
    }

    // 3. Fallback to installed check if neither is active
    $pro_installed = isset($path_info['pro']) && isset($plugins[$path_info['pro']]);
    
    if ($pro_installed) {
        return $path_info['pro_name'];
    }

    return $path_info['free_name'] ?? $plugin_slug;
}

function tpa_format_number($number) {
    if ($number >= 1000000000) {
        return round($number / 1000000000, 1) . esc_html__('B', 'automatic-translate-addon-for-translatepress');
    } elseif ($number >= 1000000) {
        return round($number / 1000000, 1) . esc_html__('M', 'automatic-translate-addon-for-translatepress');
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . esc_html__('K', 'automatic-translate-addon-for-translatepress');
    }
    return $number;
}

