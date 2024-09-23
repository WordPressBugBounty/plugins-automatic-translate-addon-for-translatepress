<?php
/**
 * Get feedback from user.
 */
class TranslatepressAutomaticTranslateAddonFree {
	/** Class for feedback.
	 * Get file path.
	 *
	 * @var plugin_file
	 */
	public $plugin_file = __FILE__;
	/**
	 *
	 * Redirect user on license page.
	 *
	 * @var slug
	 */
	public $slug = 'translatepress-tpap-register';

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'set_admin_style' ) );
		add_action( 'admin_menu', array( $this, 'tpa_free_active_admin_menu' ), 11 );
	}

	/**
	 * Css file loaded for registration page.
	 */
	public function set_admin_style() {
		wp_enqueue_style( 'tpap-free-editor-styles', TPA_URL . 'assets/css/tpa-admin-style.css', null, TPA_VERSION, 'all' );
	}

	/**
	 * Sub menu for Auto Translate Addon.
	 */
	public function tpa_free_active_admin_menu() {
		add_options_page(
			__( 'TranslatePress - Auto Translate Addon', 'tpap' ),
			__( 'TranslatePress - Auto Translate Addon', 'tpap' ),
			'manage_options',
			$this->slug,
			array(
				$this,
				'tpa_free_license_form',
			)
		);
	}

	/**
	 * Free license fom.
	 */
	public function tpa_free_license_form() {
		?>
		<form method="post">
			<input type="hidden" name="action">
			<div class="tpa-license-container">
				<h3 class="tpa-license-title"><i class="dashicons-before dashicons-translation"></i> <?php esc_html_e( 'Automatic Translate Addon For TranslatePress', $this->slug ); ?></h3>
				<div class="tpa-license-content">
					<div class="tpa-license-form">
						<?php
						$site_url = esc_url( get_site_url() );
						?>

						<p><?php esc_html_e( 'Thanks for using automatic translate addon free version that supports Yandex page translate widget for unlimited translations. You can also use', $this->slug ); ?>
							<b><?php esc_html_e( 'Google page translate widget', $this->slug ); ?></b>
							<?php esc_html_e( 'in our pro version for a better translation experience.', $this->slug ); ?></p>
						<a class="button button-primary" href='https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard'
						   target='_blank'>✅ <?php esc_html_e( 'Buy Pro Plugin', $this->slug ); ?></a>
						<a class="button button-secondary"
						   href="<?php echo esc_url( $site_url ); ?>/?trp-edit-translation=true"
						   target='_blank'>🔄 <?php esc_html_e( 'Auto Translate Site', $this->slug ); ?></a>
						<a class="button button-secondary"
						   href="https://wordpress.org/support/plugin/automatic-translate-addon-for-translatepress/reviews/#new-post"
						   target="_blank">🌟 <?php esc_html_e( 'Submit Review', $this->slug ); ?></a>

						<h3><?php esc_html_e( 'Compare Free vs Pro', $this->slug ); ?></h3>
						<table class="tp-addon-license">
							<tr>
								<th><?php esc_html_e( 'Features', $this->slug ); ?></th>
								<th><?php esc_html_e( 'Free License', $this->slug ); ?></th>
								<th><?php esc_html_e( 'Premium License', $this->slug ); ?></th>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Yandex Translate Widget Support', $this->slug ); ?><br/><img
											style="border: 1px solid;"
											src="<?php echo esc_url( TPA_URL . '/assets/images/powered-by-yandex.png' ); ?>"/></td>
								<td><span style="color:green;font-size:1.4em;">✅</span> <?php esc_html_e( 'Available', $this->slug ); ?></td>
								<td><span style="color:green;font-size:1.4em;">✅</span> <?php esc_html_e( 'Available', $this->slug ); ?></td>
							</tr>
							<tr style="background:#fffb7a;font-weight: bold;">
								<td><?php esc_html_e( 'Google Translate Widget Support', $this->slug ); ?><br/><img
											style="border: 1px solid;"
											src="<?php echo esc_url( TPA_URL . '/assets/images/powered-by-google.png' ); ?>"/></td>
								<td>❌ <?php esc_html_e( 'Not Available', $this->slug ); ?></td>
								<td><span style="color:green;font-size:1.4em;">✅</span> <?php esc_html_e( 'Available', $this->slug ); ?><br/><span
											style="font-size:11px;font-weight:bold;"><?php esc_html_e( '(Better than Yandex)', $this->slug ); ?></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Unlimited Translations', $this->slug ); ?></td>
								<td><span style="color:green;font-size:1.4em;">✅</span> <?php esc_html_e( 'Available', $this->slug ); ?><br/><span
											style="font-size:11px;font-weight:bold;"><?php esc_html_e( '(Via Yandex Only)', $this->slug ); ?></span></td>
								<td><span style="color:green;font-size:1.4em;">✅</span> <?php esc_html_e( 'Available', $this->slug ); ?><br/><span
											style="font-size:11px;font-weight:bold;"><?php esc_html_e( '(Via Yandex, Google)', $this->slug ); ?></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'No API Key Required', $this->slug ); ?></td>
								<td><span style="color:green;font-size:1.4em;">✅</span> <?php esc_html_e( 'API Not Required', $this->slug ); ?><br/><span
											style="font-size:11px;font-weight:bold;"><?php esc_html_e( '(Only Yandex Support)', $this->slug ); ?></span></td>
								<td><span style="color:green;font-size:1.4em;">✅</span> <?php esc_html_e( 'API Not Required', $this->slug ); ?><br/><span
											style="font-size:11px;font-weight:bold;"><?php esc_html_e( '(Yandex, Google)', $this->slug ); ?></span></td>
							</tr>
							<tr>
								<td><strong><?php esc_html_e( 'Premium Support', $this->slug ); ?></strong></td>
								<td>❌ <?php esc_html_e( 'Not Available', $this->slug ); ?><br/><strong><?php esc_html_e( '(Support Time: 7 – 10 days)', $this->slug ); ?></strong></td>
								<td><span style="color:green;font-size:1.4em;">✅</span> <?php esc_html_e( 'Available', $this->slug ); ?><br/><strong><?php esc_html_e( '(Support Time: 24 - 48 Hrs)', $this->slug ); ?></strong></td>
							</tr>
						</table>
					</div>

					<div class="tpa-license-textbox">
						<strong style="color:#e00b0b;"><?php esc_html_e( '*Important Points', $this->slug ); ?></strong>
						<ol>
							<li><b>1)</b> <?php esc_html_e( 'Automatic translate providers do not support HTML and special characters translations. So the plugin will not translate any string that contains HTML or special characters.', $this->slug ); ?></li>
							<li><b>2)</b> <?php esc_html_e( 'If any auto-translation provider stops any of its free translation service then the plugin will not support that translation service provider.', $this->slug ); ?></li>
							<li><b>3)</b> <?php esc_html_e( 'Translate plugins and themes internal strings using', $this->slug ); ?>
								<a href="https://wordpress.org/plugins/automatic-translator-addon-for-loco-translate/"
								   target="_blank"><?php esc_html_e( 'Automatic Translate Addon For Loco Translate', $this->slug ); ?></a>.</li>
						</ol>
						<div class="tpa-pluginby">
							<?php esc_html_e( 'Plugin by', $this->slug ); ?><br/>
							<a href="https://coolplugins.net/?utm_source=plugin_dashboard&utm_medium=image" target="_blank"><img
										src="<?php echo esc_url( TPA_URL . '/assets/images/coolplugins-logo.png' ); ?>"/></a>
						</div>
					</div>
				</div>
			</div>
		</form>
		<?php
	}
}

new TranslatepressAutomaticTranslateAddonFree();
