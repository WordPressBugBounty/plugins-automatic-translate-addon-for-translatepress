<div class="tpa-dashboard-ai-translations">
    <div class="tpa-dashboard-ai-translations-container">
        <div class="header">
            <h1><?php _e('AI Translations', $text_domain); ?></h1>
            <div class="tpa-dashboard-status">
                <span><?php _e('Inactive', $text_domain); ?></span>
                <a href="<?php echo esc_url('https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=ai_translations#pricing'); ?>" class='tpa-dashboard-btn' target="_blank">
                    <img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/upgrade-now.svg'); ?>" alt="<?php _e('Upgrade Now', $text_domain); ?>">
                    <?php _e('Upgrade Now', $text_domain); ?>
                </a>
            </div>
        </div>
        <p class="description">
            <?php _e('Experience the power of AI for faster, more accurate translations. Choose from multiple AI providers to translate your content efficiently.', $text_domain); ?>
        </p>
        <div class="tpa-dashboard-translations">
            <?php
            $ai_translations = [
                [
                    'logo' => 'powered-by-chrome-api.png',
                    'alt' => 'Chrome Built-in AI',
                    'title' => __('Chrome Built-in AI', $text_domain),
                    'description' => __('Utilize Chrome\'s built-in AI for seamless translation experience.', $text_domain),
                    'icon' => 'chrome-ai-translate.png',
                    'url' => 'https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-chrome-ai/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=chrome_ai_translations'
                ]
            ];

            foreach ($ai_translations as $translation) {
                ?>
                <div class="tpa-dashboard-translation-card">
                    <div class="logo">
                        <img src="<?php echo esc_url(TPA_URL . 'assets/images/' . $translation['logo']); ?>" 
                            alt="<?php echo esc_attr($translation['alt']); ?>">
                    </div>
                    <h3><?php echo esc_html($translation['title']); ?></h3>
                    <p><?php echo esc_html($translation['description']); ?></p>
                    <div class="play-btn-container">
                        <a href="<?php echo esc_url($translation['url']); ?>" target="_blank">
                            <img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/' . $translation['icon']); ?>" alt="<?php echo esc_attr($translation['alt']); ?>">
                        </a>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>