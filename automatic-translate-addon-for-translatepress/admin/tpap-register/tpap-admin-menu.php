<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
	 * Get TranslatePress language settings
	 *
	 * @return array Language settings including all configured languages
	 */
	private function get_trp_language_settings() {
		$defaults = array(
			'source_language' => 'en',
			'target_language' => 'hi',
			'source_language_label' => 'English',
			'target_language_label' => 'Hindi',
			'all_languages' => array(),
		);
		
		// Check if TranslatePress is active
		if ( ! class_exists( 'TRP_Translate_Press' ) ) {
			return $defaults;
		}
		
		// Get TRP settings
		$trp_settings = get_option( 'trp_settings', array() );
		
		if ( empty( $trp_settings ) ) {
			return $defaults;
		}
		
		// Get default language (source)
		$default_lang = isset( $trp_settings['default-language'] ) ? $trp_settings['default-language'] : 'en_US';
		
		// Get published languages
		$published_languages = isset( $trp_settings['publish-languages'] ) ? $trp_settings['publish-languages'] : array();
		$translation_languages = isset( $trp_settings['translation-languages'] ) ? $trp_settings['translation-languages'] : array();
		$published_languages = count( array_diff( $published_languages, array( $default_lang ) ) ) > 0 ? $published_languages : $translation_languages;
		// Get language names - pass language codes to get_language_names()
		$language_names = array();
		$all_language_codes = array_unique( array_merge( array( $default_lang ), $published_languages ) );
		
		if ( class_exists( 'TRP_Translate_Press' ) && ! empty( $all_language_codes ) ) {
			$trp = TRP_Translate_Press::get_trp_instance();
			if ( $trp && method_exists( $trp, 'get_component' ) ) {
				$trp_languages = $trp->get_component( 'languages' );
				if ( $trp_languages && method_exists( $trp_languages, 'get_language_names' ) ) {
					// Pass language codes array to get_language_names()
					$language_names = $trp_languages->get_language_names( $all_language_codes );
				}
			}
		}
		
		// Extract base language code (e.g., 'en' from 'en_US')
		$source_language = strtolower( substr( $default_lang, 0, 2 ) );
		$source_language_label = isset( $language_names[ $default_lang ] ) ? $language_names[ $default_lang ] : 'English';
		
		// Build array of all languages with their codes and labels
		$all_languages = array();
		
		// Add source language
		$all_languages[] = array(
			'code' => $source_language,
			'full_code' => $default_lang,
			'label' => $source_language_label,
			'is_default' => true,
		);
		
		// Add all published languages (excluding default if it's already in published languages)
		if ( ! empty( $published_languages ) && is_array( $published_languages ) ) {
			foreach ( $published_languages as $lang_code ) {
				if ( $lang_code !== $default_lang ) {
					$lang_base = strtolower( substr( $lang_code, 0, 2 ) );
					$lang_label = isset( $language_names[ $lang_code ] ) ? $language_names[ $lang_code ] : ucfirst( $lang_base );
					$all_languages[] = array(
						'code' => $lang_base,
						'full_code' => $lang_code,
						'label' => $lang_label,
						'is_default' => false,
					);
				}
			}
		}
		
		// Get first target language for backward compatibility
		$target_language = 'hi';
		$target_language_label = 'Hindi';
		if ( ! empty( $published_languages ) && is_array( $published_languages ) ) {
			foreach ( $published_languages as $lang_code ) {
				if ( $lang_code !== $default_lang ) {
					$target_language = strtolower( substr( $lang_code, 0, 2 ) );
					$target_language_label = isset( $language_names[ $lang_code ] ) ? $language_names[ $lang_code ] : 'Hindi';
					break;
				}
			}
		}
		
		return array(
			'source_language' => $source_language,
			'target_language' => $target_language,
			'source_language_label' => $source_language_label,
			'target_language_label' => $target_language_label,
			'all_languages' => $all_languages,
			'chrome_ai_bypass_api_check' => get_option('tpa_chrome_ai_bypass_api_check', '0'),
			'chrome_ai_bypass_secure_check' => get_option('tpa_chrome_ai_bypass_secure_check', '0'),
			'chrome_ai_bypass_browser_check' => get_option('tpa_chrome_ai_bypass_browser_check', '0'),
		);
	}

	/**
	 * Css file loaded for registration page.
	 */
	public function tpa_set_admin_style() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter used for read-only navigation, sanitized with sanitize_key()
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		if ( $page === 'translatepress-tpap-dashboard' ) {
			wp_enqueue_style( 'tpap-dashboard-style', TPA_URL . 'admin/tpa-dashboard/css/admin-styles.css', null, TPA_VERSION, 'all' );
		}

		if ( $page === 'translatepress-tpap-dashboard' ) {
			wp_enqueue_script( 'tpa-dashboard-script', TPA_URL . 'admin/tpa-dashboard/js/tpa-data-share-setting.js', array( 'jquery', 'tpa-chrome-ai-translation' ), TPA_VERSION, true );
			// Localize script with AJAX URL and nonce for provider states
			wp_localize_script( 'tpa-dashboard-script', 'tpaDashboard', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'tpa_provider_states_nonce' ),
			) );
			
			// Get TRP language settings and pass to JavaScript (for language checks)
			$trp_languages = $this->get_trp_language_settings();
			wp_localize_script( 'tpa-dashboard-script', 'tpaTrpLanguages', $trp_languages );
			
			// Enqueue Chrome AI translation script for utility methods (browser/API/secure checks)
			wp_enqueue_script( 'tpa-chrome-ai-translation', TPA_URL . 'assets/js/chrome-ai-translation.js', array(), TPA_VERSION, true );
			wp_enqueue_script( 'tpa-chrome-ai-notice', TPA_URL . 'admin/tpa-dashboard/js/tpa-chrome-ai-notice.js', array( 'jquery', 'tpa-chrome-ai-translation' ), TPA_VERSION, true );
			wp_localize_script( 'tpa-chrome-ai-notice', 'tpaTrpLanguages', $trp_languages );
		}
	}

	/**
	 * Sub menu for Auto Translate Addon.
	 */
	public function tpa_free_active_admin_menu() {
		add_options_page(
			esc_html__( 'AI Translation [TranslatePress]', 'automatic-translate-addon-for-translatepress' ),
			esc_html__( 'AI Translation [TranslatePress]', 'automatic-translate-addon-for-translatepress' ),
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
		$file_prefix = 'admin/tpa-dashboard/views/';
		
		$valid_tabs = [
			'dashboard'       => esc_html__('Dashboard', 'automatic-translate-addon-for-translatepress'),
			'settings'        => esc_html__('Settings', 'automatic-translate-addon-for-translatepress'),
			'license'         => esc_html__('License', 'automatic-translate-addon-for-translatepress'),
			'ai-translations' => esc_html__('Documentation', 'automatic-translate-addon-for-translatepress'),
			'free-vs-pro'     => esc_html__('Free vs Pro', 'automatic-translate-addon-for-translatepress')
		];

		// Get current tab with fallback
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter used for read-only navigation, sanitized with sanitize_key() and validated against whitelist
		$tab 			= isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';
		// Make sure tab is on our whitelist of allowed values
		$tab = array_key_exists($tab, $valid_tabs) ? $tab : 'dashboard';
		$current_tab 	= $tab;
		
		// Action buttons configuration
		$buttons = [
			[
				'url'  => 'https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard_header#pricing',
				'img'  => 'upgrade-now.svg',
				'alt'  => esc_html__('premium', 'automatic-translate-addon-for-translatepress'),
				'text' => esc_html__('Unlock Pro Features', 'automatic-translate-addon-for-translatepress')
			],
			[
				'url' => 'https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=dashboard_header',
				'img' => 'document.svg',
				'alt' => esc_html__('document', 'automatic-translate-addon-for-translatepress')
			],
			[
				'url' => 'https://coolplugins.net/support/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=support&utm_content=dashboard_header',
				'img' => 'contact.svg',
				'alt' => esc_html__('contact', 'automatic-translate-addon-for-translatepress')
			]
		];

		// Start HTML output
		?>
		<div class="tpa-dashboard-wrapper">
			<div class="tpa-dashboard-header">
				<div class="tpa-dashboard-header-left">
					<img src="<?php echo esc_url(TPA_URL . 'admin/tpa-dashboard/images/translatepress-addon.svg'); ?>" 
						alt="<?php esc_attr_e('TranslatePress Addon Logo', 'automatic-translate-addon-for-translatepress'); ?>">
					<div class="tpa-dashboard-tab-title">
						<span>↳</span> <?php echo esc_html($valid_tabs[$current_tab]); ?>
					</div>
				</div>
				<div class="tpa-dashboard-header-right">
					<span><?php esc_html_e('Auto translate pages and posts.', 'automatic-translate-addon-for-translatepress'); ?></span>
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
			
			<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e('Dashboard navigation', 'automatic-translate-addon-for-translatepress'); ?>">
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
