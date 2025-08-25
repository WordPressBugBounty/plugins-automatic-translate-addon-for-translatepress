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
	public $slug = 'translatepress-tpap-dashboard';

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'tpa_set_admin_style' ) );
		add_action( 'admin_menu', array( $this, 'tpa_free_active_admin_menu' ), 11 );
	}

	/**
	 * Css file loaded for registration page.
	 */
	public function tpa_set_admin_style() {
		if(isset($_GET['page']) && $_GET['page'] == 'translatepress-tpap-dashboard') {
			wp_enqueue_style( 'tpap-dashboard-style', TPA_URL . 'admin/tpa-dashboard/css/admin-styles.css',null, TPA_VERSION, 'all' );
		}

		if(isset($_GET['page']) && $_GET['page'] == 'translatepress-tpap-dashboard') {
			wp_enqueue_script('tpa-dashboard-script',TPA_URL . 'admin/tpa-dashboard/js/tpa-data-share-setting.js',array('jquery'), TPA_VERSION ,true);
		}
	}

	/**
	 * Sub menu for Auto Translate Addon.
	 */
	public function tpa_free_active_admin_menu() {
		add_options_page(
			esc_html__( 'AI Translation [TranslatePress]', 'TPA' ),
			esc_html__( 'AI Translation [TranslatePress]', 'TPA' ),
			'manage_options',
			$this->slug,
			array(
				$this,
				'tpa_dashboard_page',
			)
		);
	}

	/**
	 * Free license fom.
	 */
	public function tpa_dashboard_page() {
		$text_domain = 'TPA';
		$file_prefix = 'admin/tpa-dashboard/views/';
		
		$valid_tabs = [
			'dashboard'       => esc_html__('Dashboard', $text_domain),
			'ai-translations' => esc_html__('AI Translations', $text_domain),
			'settings'        => esc_html__('Settings', $text_domain),
			'license'         => esc_html__('License', $text_domain),
			'free-vs-pro'     => esc_html__('Free vs Pro', $text_domain)
		];

		// Get current tab with fallback

		$tab 			= isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';
		// Make sure tab is on our whitelist of allowed values
		$tab = array_key_exists($tab, $valid_tabs) ? $tab : 'dashboard';
		$current_tab 	= $tab;
		
		// Action buttons configuration
		$buttons = [
			[
				'url'  => 'https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_header#pricing',
				'img'  => 'upgrade-now.svg',
				'alt'  => esc_html__('premium', $text_domain),
				'text' => esc_html__('Unlock Pro Features', $text_domain)
			],
			[
				'url' => 'https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_header',
				'img' => 'document.svg',
				'alt' => esc_html__('document', $text_domain)
			],
			[
				'url' => 'https://coolplugins.net/support/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=support&utm_content=dashboard_header',
				'img' => 'contact.svg',
				'alt' => esc_html__('contact', $text_domain)
			]
		];

		// Start HTML output
		?>
		<div class="tpa-dashboard-wrapper">
			<div class="tpa-dashboard-header">
				<div class="tpa-dashboard-header-left">
					<img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/translatepress-addon.svg'); ?>" 
						alt="<?php esc_attr_e('TranslatePress Addon Logo', $text_domain); ?>">
					<div class="tpa-dashboard-tab-title">
						<span>↳</span> <?php echo esc_html($valid_tabs[$current_tab]); ?>
					</div>
				</div>
				<div class="tpa-dashboard-header-right">
					<span><?php esc_html_e('Auto translate pages and posts.', $text_domain); ?></span>
					<?php foreach ($buttons as $button): ?>
						<a href="<?php echo esc_url($button['url']); ?>" 
						class="tpa-dashboard-btn" 
						target="_blank"
						aria-label="<?php echo esc_attr($button['alt']); ?>">
							<img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/' . $button['img']); ?>" 
								alt="<?php echo esc_attr($button['alt']); ?>">
							<?php if (isset($button['text'])): ?>
								<span><?php echo esc_html($button['text']); ?></span>
							<?php endif; ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
			
			<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e('Dashboard navigation', $text_domain); ?>">
				<?php foreach ($valid_tabs as $tab_key => $tab_title): ?>
					<a href="?page=translatepress-tpap-dashboard&tab=<?php echo esc_attr($tab_key); ?>" 
					class="nav-tab <?php echo esc_attr($tab === $tab_key ? 'nav-tab-active' : ''); ?>">
						<?php echo esc_html($tab_title); ?>
					</a>
				<?php endforeach; ?>
			</nav>
			
			<div class="tab-content">
				<?php
				// Define whitelist of valid file names that can be included
				$valid_files = array(
					'dashboard', 'ai-translations', 'settings', 'license', 'free-vs-pro'
				);
				
				// Validate tab against whitelist before including the file
				if (in_array($tab, $valid_files, true)) {
					$include_file = TPA_PATH . $file_prefix . $tab . '.php';
					// Check if file exists as additional security measure
					if (file_exists($include_file)) {
						require_once $include_file;
					} else {
						// Fallback to dashboard if file doesn't exist
						require_once TPA_PATH . $file_prefix . 'dashboard.php';
					}
				} else {
					// If not in whitelist, load dashboard as default
					require_once TPA_PATH . $file_prefix . 'dashboard.php';
				}
				
				require_once TPA_PATH . $file_prefix . 'sidebar.php';
				
				?>
			</div>
			
			<?php require_once TPA_PATH . $file_prefix . 'footer.php'; ?>
		</div>
		<?php
	}
}

new TranslatepressAutomaticTranslateAddonFree();
