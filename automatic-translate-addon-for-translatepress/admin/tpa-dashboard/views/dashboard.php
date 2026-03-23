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

                // Note: the 3rd item is a "Free/Pro" flag used by the UI to disable Pro toggles.
                $providers = [
                    [
                        "Chrome Built-in AI",
                        "powered-by-chrome-api.png",
                        "Free",
                        [
                            esc_html__( "Fast AI Translations in Browser", 'automatic-translate-addon-for-translatepress' ),
                            esc_html__( "Unlimited Free Translations", 'automatic-translate-addon-for-translatepress' ),
                            esc_html__( "Bulk Translation", 'automatic-translate-addon-for-translatepress' ),
                        ],
                        esc_url( 'https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-chrome-ai/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_chrome' ),
                        $chrome_enabled,
                    ],
                    [
                        "Google Translate",
                        "powered-by-google.png",
                        "Pro",
                        [
                            esc_html__( "Unlimited Free Translations", 'automatic-translate-addon-for-translatepress' ),
                            esc_html__( "Fast & No API Key Required", 'automatic-translate-addon-for-translatepress' ),
                        ],
                        esc_url( 'https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-google/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_google' ),
                        '1',
                    ],
                    [
                        "Yandex Translate",
                        "powered-by-yandex.png",
                        "Free",
                        [
                            esc_html__( "Unlimited Free Translations", 'automatic-translate-addon-for-translatepress' ),
                            esc_html__( "No API & No Extra Cost", 'automatic-translate-addon-for-translatepress' ),
                        ],
                        esc_url( 'https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-yandex/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_yandex' ),
                        $yandex_enabled,
                    ],
                    [
                        "Anthropic Claude",
                        "powered-by-anthropic.png",
                        "Pro",
                        [
                            esc_html__( "Unlimited Free Translations", 'automatic-translate-addon-for-translatepress' ),
                            esc_html__( "Use Translation Modals", 'automatic-translate-addon-for-translatepress' ),
                            esc_html__( "Bulk Translation", 'automatic-translate-addon-for-translatepress' ),
                        ],
                        esc_url( 'https://docs.coolplugins.net/doc/generate-anthropic-ai-api-key-translatepress/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_anthropic' ),
                        '1',
                    ],
                    [
                        "Gemini",
                        "powered-by-google-gemini.png",
                        "Pro",
                        [
                            esc_html__( "Unlimited Free Translations", 'automatic-translate-addon-for-translatepress' ),
                            esc_html__( "Use Translation Modals", 'automatic-translate-addon-for-translatepress' ),
                            esc_html__( "Bulk Translation", 'automatic-translate-addon-for-translatepress' ),
                        ],
                        esc_url( 'https://docs.coolplugins.net/doc/generate-google-gemini-ai-api-key-translatepress/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_gemini' ),
                        '1',
                    ],
                    [
                        "OpenAI",
                        "powered-by-openai.png",
                        "Pro",
                        [
                            esc_html__( "Unlimited Free Translations", 'automatic-translate-addon-for-translatepress' ),
                            esc_html__( "Use Translation Modals", 'automatic-translate-addon-for-translatepress' ),
                            esc_html__( "Bulk Translation", 'automatic-translate-addon-for-translatepress' ),
                        ],
                        esc_url( 'https://docs.coolplugins.net/doc/generate-open-ai-api-key-translatepress/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_openai' ),
                        '1',
                    ],
                ];

                foreach ($providers as $index => $provider) {
                    $provider_slug = strtolower(str_replace(' ', '-', $provider[0]));
                    $is_enabled = isset( $provider[5] ) ? $provider[5] : '1';
                    // Pro providers are disabled in Free; Free providers keep saved state.
                    $is_checked = ( $provider[2] === 'Pro' ) ? '' : ( ( $is_enabled === '1' ) ? 'checked' : '' );
                    ?>
                    <div class="tpa-dashboard-provider-card">
                        <div class="tpa-dashboard-provider-header">
                            <a href="<?php echo esc_url($provider[4]); ?>" target="_blank"><img src="<?php echo esc_url(TPA_URL . 'assets/images/' . $provider[1]); ?>" alt="<?php echo esc_html($provider[0]); ?>"></a>
                            <div class="tpa-provider-switch-container">
                                <label class="tpa-provider-switch <?php echo ($provider[2] === 'Pro') ? 'tpa-pro-provider' : ''; ?>">
                                    <input type="checkbox" 
                                           class="tpa-provider-toggle" 
                                           data-provider="<?php echo esc_attr($provider_slug); ?>"
                                           <?php echo esc_attr( ($provider[2] === 'Pro') ? 'disabled' : '' ); ?>
                                           <?php echo esc_attr( $is_checked ); ?>>
                                    <span class="tpa-switch-slider"></span>
                                </label>
                            </div>
                        </div>
                        <h4><?php echo esc_html($provider[0]); ?></h4>
                        <ul>
                            <?php foreach ($provider[3] as $feature) { ?>
                                <li>✅ <?php echo esc_html($feature); ?></li>
                            <?php } ?>
                        </ul>
                        <div class="tpa-dashboard-provider-buttons">
                            <a href="<?php echo esc_url($provider[4]); ?>" class="tpa-dashboard-btn" target="_blank">Docs</a>
                            <?php if($provider[0] == "Chrome Built-in AI") { ?>
                                <a href="<?php echo esc_url(admin_url('options-general.php?page=translatepress-tpap-dashboard&tab=settings')); ?>" class="tpa-dashboard-btn primary tpa-chrome-configure-btn" style="<?php echo ($chrome_enabled == '1') ? '' : 'display:none;'; ?>">Configure</a>
                            <?php } ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

