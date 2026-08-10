<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
    <div class="tpa-dashboard-left-section">
        
        <!-- Welcome Section -->
        <div class="tpa-dashboard-welcome">
            <div class="tpa-dashboard-welcome-text">
                <h2><?php echo esc_html__('Welcome To TranslatePress Addon', 'automatic-translate-addon-for-translatepress'); ?></h2>
                <p><?php echo esc_html__('Translate WordPress Full Webpage instantly with TranslatePress Addon. One-click, thousands of strings - no extra cost!', 'automatic-translate-addon-for-translatepress'); ?></p>
                <div class="tpa-dashboard-btns-row">
                    <a href="<?php echo esc_url(admin_url('options-general.php?page=translate-press')); ?>" target="_blank" class="tpa-dashboard-btn primary"><?php echo esc_html__('Website Languages', 'automatic-translate-addon-for-translatepress'); ?></a>
                    <a href="<?php echo esc_url(site_url('/?trp-edit-translation=true')); ?>" target="_blank" class="tpa-dashboard-btn"><?php echo esc_html__('Translate Site', 'automatic-translate-addon-for-translatepress'); ?></a>
                </div>
                <a class="tpa-dashboard-docs" href="<?php echo esc_url('https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard'); ?>" target="_blank"><img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/document.svg'); ?>" alt="document"> <?php echo esc_html__('Read Plugin Docs', 'automatic-translate-addon-for-translatepress'); ?></a>
            </div>
            <div class="tpa-dashboard-welcome-video">
                <a href="https://docs.coolplugins.net/doc/ai-translation-translatepress-video-tutorials/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_video" target="_blank" class="tpa-dashboard-video-link">
                    <img decoding="async" src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/video.svg'); ?>" class="play-icon" alt="play-icon">
                    <picture>
                        <source srcset="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/loco-addon-video.png'); ?>" type="image/avif">
                        <img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/loco-addon-video.jpg'); ?>" class="translatepress-addon-video" alt="translatepress addon preview">
                    </picture>
                </a>
            </div>
        </div>

        <!-- Translation Providers -->  
        <div class="tpa-dashboard-translation-providers">
            <h3><?php esc_html_e('Translation Providers', 'automatic-translate-addon-for-translatepress'); ?></h3>
            <div class="tpa-dashboard-providers-grid">
                
                <?php
                // Get saved provider states from database (default to enabled for Free providers)
                $yandex_enabled = get_option( 'tpa_provider_yandex_enabled', '1' );
                $chrome_enabled = get_option( 'tpa_provider_chrome_enabled', '1' );
                $edge_enabled   = get_option( 'tpa_provider_edge_enabled', '1' );

                // Built-in AI cards: Chrome-only on Chrome, Edge-only on Edge, both on every other browser.
                if ( ! function_exists( 'tpa_get_visible_builtin_ai_provider_keys' ) ) {
                    require_once TPA_PATH . 'includes/helpers.php';
                }
                $tpa_visible_builtin_providers = tpa_get_visible_builtin_ai_provider_keys();

                $tpa_chrome_provider = array(
                    'name' => 'Chrome Built-in AI',
                    'image' => 'powered-by-chrome-api.png',
                    'type' => 'Free',
                    'features' => array(
                        esc_html__( 'Fast AI Translations in Browser', 'automatic-translate-addon-for-translatepress' ),
                        esc_html__( 'Unlimited Free Translations', 'automatic-translate-addon-for-translatepress' ),
                        esc_html__( 'Bulk Translation', 'automatic-translate-addon-for-translatepress' ),
                    ),
                    'url' => esc_url( 'https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-chrome-ai/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_chrome' ),
                    'enabled' => $chrome_enabled,
                    'provider_type' => 'chrome',
                );

                $tpa_edge_provider = array(
                    'name' => 'Edge Built-in AI',
                    'image' => 'powered-by-edge-api.png',
                    'type' => 'Free',
                    'features' => array(
                        esc_html__( 'Fast AI Translations in Browser', 'automatic-translate-addon-for-translatepress' ),
                        esc_html__( 'Unlimited Free Translations', 'automatic-translate-addon-for-translatepress' ),
                        esc_html__( 'Bulk Translation', 'automatic-translate-addon-for-translatepress' ),
                    ),
                    'url' => esc_url( 'https://docs.coolplugins.net/doc/microsoft-edge-ai-for-translatepress/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_edge' ),
                    'enabled' => $edge_enabled,
                    'provider_type' => 'edge',
                );

                // Keep Chrome and Edge toggles independent — disabling one must not affect the other.
                $tpa_builtin_providers = array();
                foreach ( $tpa_visible_builtin_providers as $tpa_visible_provider_key ) {
                    if ( 'edge' === $tpa_visible_provider_key ) {
                        $tpa_builtin_providers[] = $tpa_edge_provider;
                    } elseif ( 'chrome' === $tpa_visible_provider_key ) {
                        $tpa_builtin_providers[] = $tpa_chrome_provider;
                    }
                }

                // Note: the 3rd item is a "Free/Pro" flag used by the UI to disable Pro toggles.
                $providers = array_merge(
                    array(
                        array(
                            'name' => "Yandex Translate",
                            'image' => "powered-by-yandex.png",
                            'type' => "Free",
                            'features' => array(
                                esc_html__( 'Unlimited Free Translations', 'automatic-translate-addon-for-translatepress' ),
                                esc_html__( 'No API & No Extra Cost', 'automatic-translate-addon-for-translatepress' ),
                            ),
                            'url' => esc_url( 'https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-yandex/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_yandex' ),
                            'enabled' => $yandex_enabled,
                            'provider_type' => 'yandex',
                        ),
                    ),
                    $tpa_builtin_providers,
                    array(
                        array(
                            'name' => "Google Translate",
                            'image' => "powered-by-google.png",
                            'type' => "Pro",
                            'features' => array(
                                esc_html__( 'Unlimited Free Translations', 'automatic-translate-addon-for-translatepress' ),
                                esc_html__( 'Fast & No API Key Required', 'automatic-translate-addon-for-translatepress' ),
                            ),
                            'url' => esc_url( 'https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-google/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_google' ),
                            'enabled' => false,
                            'provider_type' => 'google',
                        ),
                        array(
                            'name' => "Anthropic Claude",
                            'image' => "powered-by-anthropic.png",
                            'type' => "Pro",
                            'features' => array(
                                esc_html__( 'Unlimited Free Translations', 'automatic-translate-addon-for-translatepress' ),
                                esc_html__( 'Use Translation Modals', 'automatic-translate-addon-for-translatepress' ),
                                esc_html__( 'Bulk Translation', 'automatic-translate-addon-for-translatepress' ),
                            ),
                            'url' => esc_url( 'https://docs.coolplugins.net/doc/generate-anthropic-ai-api-key-translatepress/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_anthropic' ),
                            'enabled' => false,
                            'provider_type' => 'anthropic',
                        ),
                        array(
                            'name' => "Gemini",
                            'image' => "powered-by-google-gemini.png",
                            'type' => "Pro",
                            'features' => array(
                                esc_html__( 'Unlimited Free Translations', 'automatic-translate-addon-for-translatepress' ),
                                esc_html__( 'Use Translation Modals', 'automatic-translate-addon-for-translatepress' ),
                                esc_html__( 'Bulk Translation', 'automatic-translate-addon-for-translatepress' ),
                            ),
                            'url' => esc_url( 'https://docs.coolplugins.net/doc/generate-google-gemini-ai-api-key-translatepress/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_gemini' ),
                            'enabled' => false,
                            'provider_type' => 'gemini',
                        ),
                        array(
                            'name' => "OpenAI",
                            'image' => "powered-by-openai.png",
                            'type' => "Pro",
                            'features' => array(
                                esc_html__( 'Unlimited Free Translations', 'automatic-translate-addon-for-translatepress' ),
                                esc_html__( 'Use Translation Modals', 'automatic-translate-addon-for-translatepress' ),
                                esc_html__( 'Bulk Translation', 'automatic-translate-addon-for-translatepress' ),
                            ),
                            'url' => esc_url( 'https://docs.coolplugins.net/doc/generate-open-ai-api-key-translatepress/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_openai' ),
                            'enabled' => false,
                            'provider_type' => 'openai',
                        ),
                    )
                );

                foreach ($providers as $index => $provider) {
                    $provider_slug = strtolower(str_replace(' ', '-', $provider['name']));
                    $is_enabled = isset( $provider['enabled'] ) ? $provider['enabled'] : '0';
                    ?>
                    <div class="tpa-dashboard-provider-card">
                        <div class="tpa-dashboard-provider-header">
                            <a href="<?php echo esc_url($provider['url']); ?>" target="_blank"><img src="<?php echo esc_url(TPA_URL . 'assets/images/' . $provider['image']); ?>" alt="<?php echo esc_attr($provider['name']); ?>"></a>
                            <div class="tpa-provider-switch-container">
                                <label class="tpa-provider-switch <?php echo esc_attr( ( $provider['type'] === 'Pro' ) ? 'tpa-pro-provider' : '' ); ?>">
                                    <input type="checkbox" 
                                           class="tpa-provider-toggle" 
                                           data-provider="<?php echo esc_attr($provider_slug); ?>"
                                           <?php disabled( 'Pro', $provider['type'] ); ?>
                                           <?php
                                           if ( 'Pro' !== $provider['type'] ) {
                                               checked( '1', (string) $is_enabled );
                                           }
                                           ?>>
                                    <span class="tpa-switch-slider"></span>
                                </label>
                            </div>
                        </div>
                        <h4><?php echo esc_html($provider['name']); ?></h4>
                        <ul>
                            <?php foreach ($provider['features'] as $feature) { ?>
                                <li>✅ <?php echo esc_html($feature); ?></li>
                            <?php } ?>
                        </ul>
                        <div class="tpa-dashboard-provider-buttons">
                            <a href="<?php echo esc_url($provider['url']); ?>" class="tpa-dashboard-btn" rel="noopener noreferrer" target="_blank">Docs</a>
                            <?php if ( in_array( $provider['provider_type'], array( 'chrome', 'edge' ), true ) ) { ?>
                                <a href="<?php echo esc_url( admin_url( 'options-general.php?page=translatepress-tpap-dashboard&tab=settings' ) ); ?>" class="tpa-dashboard-btn primary tpa-builtin-ai-configure-btn" data-provider="<?php echo esc_attr( $provider['provider_type'] ); ?>" style="display:none;"><?php esc_html_e( 'Configure', 'automatic-translate-addon-for-translatepress' ); ?></a>
                            <?php } ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

