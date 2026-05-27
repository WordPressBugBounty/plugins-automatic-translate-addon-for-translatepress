<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tpa-dashboard-free-vs-pro">
    <div class="tpa-dashboard-free-vs-pro-container">
    <div class="header">
        <h1><?php esc_html_e('Free VS Pro', 'automatic-translate-addon-for-translatepress'); ?></h1>
        <div class="tpa-dashboard-status">
            <span class="status"><?php esc_html_e('Inactive', 'automatic-translate-addon-for-translatepress'); ?></span>
            <a href="<?php echo esc_url('https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=freevspro#pricing'); ?>" class='tpa-dashboard-btn' target="_blank">
              <img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/upgrade-now.svg'); ?>" alt="<?php echo esc_attr(esc_html__('Upgrade Now', 'automatic-translate-addon-for-translatepress')); ?>">
                <?php echo esc_html(esc_html__('Upgrade Now', 'automatic-translate-addon-for-translatepress')); ?>
            </a>
        </div>
    </div>
    
    <p><?php echo esc_html(esc_html__('Compare the Free and Pro versions to choose the best option for your translation needs.', 'automatic-translate-addon-for-translatepress')); ?></p>

    <table>
        <thead>
            <tr>
                <th><?php echo esc_html__( 'Feature', 'automatic-translate-addon-for-translatepress' ); ?></th>
                <th><?php echo esc_html__( 'Free', 'automatic-translate-addon-for-translatepress' ); ?></th>
                <th><?php echo esc_html__( 'Pro', 'automatic-translate-addon-for-translatepress' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
                // Matches readme "Free vs. Pro" and the translation provider UI (Yandex/Chrome free; Google & AI providers Pro).
                $features = array(
                    esc_html__( 'Yandex automatic translation', 'automatic-translate-addon-for-translatepress' ) => array( true, true ),
                    esc_html__( 'Chrome Built-in AI translator', 'automatic-translate-addon-for-translatepress' ) => array( true, true ),
                    esc_html__( 'Google Translate widget', 'automatic-translate-addon-for-translatepress' ) => array( false, true ),
                    esc_html__( 'OpenAI, Google Gemini & Anthropic translation', 'automatic-translate-addon-for-translatepress' ) => array( false, true ),
                    esc_html__( 'Bulk translation', 'automatic-translate-addon-for-translatepress' ) => array( false, true ),
                    esc_html__( 'Unlimited strings & characters', 'automatic-translate-addon-for-translatepress' ) => array( true, true ),
                    esc_html__( 'Premium support (24–48h response)', 'automatic-translate-addon-for-translatepress' ) => array( false, true ),
                );
            foreach ( $features as $feature => $availability ) :
                ?>
                <tr>
                    <td><?php echo esc_html( $feature ); ?></td>
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