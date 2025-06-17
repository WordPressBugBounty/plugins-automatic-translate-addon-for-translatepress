
    <div class="tpa-dashboard-left-section">
        
        <!-- Welcome Section -->
        <div class="tpa-dashboard-welcome">
            <div class="tpa-dashboard-welcome-text">
                <h2><?php echo esc_html__('Welcome To TranslatePress Addon', $text_domain); ?></h2>
                <p><?php echo esc_html__('Translate WordPress Full Webpage instantly with TranslatePress Addon. One-click, thousands of strings - no extra cost!', $text_domain); ?></p>
                <div class="tpa-dashboard-btns-row">
                    <a href="<?php echo esc_url(admin_url('options-general.php?page=translate-press')); ?>" target="_blank" class="tpa-dashboard-btn primary"><?php echo esc_html__('Website Languages', $text_domain); ?></a>
                    <a href="<?php echo esc_url(site_url('/?trp-edit-translation=true')); ?>" target="_blank" class="tpa-dashboard-btn"><?php echo esc_html__('Translate Site', $text_domain); ?></a>
                </div>
                <a class="tpa-dashboard-docs" href="<?php echo esc_url('https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard'); ?>" target="_blank"><img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/document.svg'); ?>" alt="document"> <?php echo esc_html__('Read Plugin Docs', $text_domain); ?></a>
            </div>
            <div class="tpa-dashboard-welcome-video">
                <a href="https://docs.coolplugins.net/doc/ai-translation-translatepress-video-tutorials/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_video" target="_blank" class="tpa-dashboard-video-link">
                    <img decoding="async" src="<?php echo TPA_URL . 'admin/tpa-dashboard/images/video.svg'; ?>" class="play-icon" alt="play-icon">
                    <picture>
                        <source srcset="<?php echo TPA_URL . 'admin/tpa-dashboard/images/loco-addon-video.png'; ?>" type="image/avif">
                        <img src="<?php echo TPA_URL . 'admin/tpa-dashboard/images/loco-addon-video.jpg'; ?>" class="translatepress-addon-video" alt="translatepress addon preview">
                    </picture>
                </a>
            </div>
        </div>

        <!-- Translation Providers -->  
        <div class="tpa-dashboard-translation-providers">
            <h3><?php _e('Translation Providers', $text_domain); ?></h3>
            <div class="tpa-dashboard-providers-grid">
                
                <?php

                $providers = [
                    ["Chrome Built-in AI", "powered-by-chrome-api.png", "Pro", ["Fast AI Translations in Browser", "Unlimited Free Translations", "Use Translation Modals"], esc_url('https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-chrome-ai/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_chrome')],
                    ["Google Translate", "powered-by-google.png", "Pro", ["Unlimited Free Translations", "Fast & No API Key Required"], esc_url('https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-google/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_google')],
                    ["Yandex Translate", "powered-by-yandex.png", "Free", ["Unlimited Free Translations", "No API & No Extra Cost"], esc_url('https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-yandex/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_yandex')],
                ];

                foreach ($providers as $index => $provider) {
                    ?>
                    <div class="tpa-dashboard-provider-card">
                        <div class="tpa-dashboard-provider-header">
                            <a href="<?php echo esc_url($provider[4]); ?>" target="_blank"><img src="<?php echo esc_url(TPA_URL . 'assets/images/' . $provider[1]); ?>" alt="<?php echo esc_html($provider[0]); ?>"></a>
                            <span class="tpa-dashboard-badge <?php echo strtolower($provider[2]); ?>"><?php echo $provider[2]; ?></span>
                        </div>
                        <h4><?php echo $provider[0]; ?></h4>
                        <ul>
                            <?php foreach ($provider[3] as $feature) { ?>
                                <li>✅ <?php echo $feature; ?></li>
                            <?php } ?>
                        </ul>
                        <div class="tpa-dashboard-provider-buttons">
                            <a href="<?php echo esc_url($provider[4]); ?>" class="tpa-dashboard-btn" target="_blank">Docs</a>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

