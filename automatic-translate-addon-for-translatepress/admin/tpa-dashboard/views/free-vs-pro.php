<div class="tpa-dashboard-free-vs-pro">
    <div class="tpa-dashboard-free-vs-pro-container">
    <div class="header">
        <h1><?php esc_html_e('Free VS Pro', $text_domain); ?></h1>
        <div class="tpa-dashboard-status">
            <span class="status"><?php esc_html_e('Inactive', $text_domain); ?></span>
            <a href="<?php echo esc_url('https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=freevspro#pricing'); ?>" class='tpa-dashboard-btn' target="_blank">
              <img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/upgrade-now.svg'); ?>" alt="<?php echo esc_attr(esc_html__('Upgrade Now', $text_domain)); ?>">
                <?php echo esc_html(esc_html__('Upgrade Now', $text_domain)); ?>
            </a>
        </div>
    </div>
    
    <p><?php echo esc_html(esc_html__('Compare the Free and Pro versions to choose the best option for your translation needs.', $text_domain)); ?></p>

    <table>
        <thead>
            <tr>
                <th><?php echo esc_html__('Dynamic Content', $text_domain); ?></th>
                <th><?php echo esc_html__('Free', $text_domain); ?></th>
                <th><?php echo esc_html__('Pro', $text_domain); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $features = [
                    'Yandex Translate Widget Support' => [true, true],
                    'No API Key Required' => [true, true],
                    'Unlimited Translations' => [true, true],
                    'Google Translate Widget Support' => [false, true],
                    'Chrome Built-in AI Support' => [false, true],
                    'Premium Support' => [false, true],
                ];
             foreach ($features as $feature => $availability): ?>
                <tr>
                    <td><?php echo esc_html($feature); ?></td>
                    <td class="<?php echo esc_attr($availability[0] ? 'check' : 'cross'); ?>">
                        <?php echo esc_html($availability[0] ? '✓' : '✗'); ?>
                    </td>
                    <td class="<?php echo esc_attr($availability[1] ? 'check' : 'cross'); ?>">
                        <?php echo esc_html($availability[1] ? '✓' : '✗'); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>