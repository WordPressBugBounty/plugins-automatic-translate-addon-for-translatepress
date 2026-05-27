<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="tpa-dashboard-ai-translations">
    <div class="tpa-dashboard-ai-translations-container">
        <div class="header">
            <h1><?php esc_html_e('AI Translations', 'automatic-translate-addon-for-translatepress'); ?></h1>

            <a href="https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=ai_translations" target="_blank" class="tpa-dashboard-btn tpa-ai-translation-docs-btn">
                <?php esc_html_e('View Full Documentation', 'automatic-translate-addon-for-translatepress'); ?>
            </a>
        </div>
        <p class="description">
            <?php esc_html_e('Experience the power of AI for faster, more accurate translations. Choose from multiple AI providers to translate your content efficiently.', 'automatic-translate-addon-for-translatepress'); ?>
        </p>
        <div class="tpa-dashboard-translations">
            <?php
            $ai_translations = [
                [
                    'logo' => 'powered-by-chrome-api.png',
                    'alt' => 'Chrome Built-in AI',
                    'title' => esc_html__('Chrome Built-in AI', 'automatic-translate-addon-for-translatepress'),
                    'description' => esc_html__('Utilize Chrome\'s built-in AI for seamless translation experience.', 'automatic-translate-addon-for-translatepress'),
                    'icon' => 'chrome-ai-translate.png',
                    'url' => 'https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-chrome-ai/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=chrome_ai_translations'
                ],
                [
                    'logo' => 'powered-by-anthropic.png',
                    'alt' => 'Anthropic AI',
                    'title' => esc_html__('Anthropic AI', 'automatic-translate-addon-for-translatepress'),
                    'description' => esc_html__('Accurate, context-aware AI translations powered by Anthropic.', 'automatic-translate-addon-for-translatepress'),
                    'icon' => 'anthropic-ai-translate.png',
                    'url' => 'https://docs.coolplugins.net/doc/generate-anthropic-ai-api-key-translatepress/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=anthropic_ai_translations'
                ],
                [
                    'logo' => 'powered-by-google-gemini.png',
                    'alt' => 'Gemini AI',
                    'title' => esc_html__('Gemini AI', 'automatic-translate-addon-for-translatepress'),
                    'description' => esc_html__('Fast and reliable translations with Gemini AI.', 'automatic-translate-addon-for-translatepress'),
                    'icon' => 'google-gemini-ai-translate.png',
                    'url' => 'https://docs.coolplugins.net/doc/generate-google-gemini-ai-api-key-translatepress/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=google_gemini_ai_translations'
                ],
                [
                    'logo' => 'powered-by-openai.png',
                    'alt' => 'OpenAI',
                    'title' => esc_html__('Open AI', 'automatic-translate-addon-for-translatepress'),
                    'description' => esc_html__('High-quality AI translations powered by OpenAI.', 'automatic-translate-addon-for-translatepress'),
                    'icon' => 'open-ai-translate.png',
                    'url' => 'https://docs.coolplugins.net/doc/generate-open-ai-api-key-translatepress/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=open_ai_translations'
                ],
            ];

            foreach ($ai_translations as $translation) {
                ?>
                <div class="tpa-dashboard-translation-card">
                    <div class="logo">
                        <img src="<?php echo esc_url( TPA_URL . 'assets/images/' . sanitize_file_name( $translation['logo'] ) ); ?>"
                            alt="<?php echo esc_attr( substr( $translation['alt'], 0, 100 ) ); ?>">
                    </div>
                    <h3><?php echo esc_html( substr( $translation['title'], 0, 100 ) ); ?></h3>
                    <p><?php echo esc_html( substr( $translation['description'], 0, 250 ) ); ?></p>
                    <div class="play-btn-container">
                        <a href="<?php echo esc_url($translation['url']); ?>" target="_blank">
                            <img src="<?php echo esc_url( TPA_URL . 'admin/tpa-dashboard/images/' . sanitize_file_name( $translation['icon'] ) ); ?>" alt="<?php echo esc_attr( substr( $translation['alt'], 0, 100 ) ); ?>">
                        </a>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>