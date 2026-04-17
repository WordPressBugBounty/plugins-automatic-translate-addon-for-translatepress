<?php
/**
 * Plugin Name: AI Translation For TranslatePress
 * Description: Auto language translator add-on for TranslatePress to translate your website into any language using AI & Machine Translation tools—No API Key Needed!.
 * Author: Cool Plugins
 * Author URI: https://coolplugins.net/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=author_page&utm_content=plugins_list
 * Plugin URI:
 * Version: 2.0.5
 * License: GPL2
 * Text Domain:automatic-translate-addon-for-translatepress
 * Requires Plugins: translatepress-multilingual
 *
 *  @package TPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( defined( 'TPA_VERSION' ) ) {
	return;
}
define( 'TPA_VERSION', '2.0.5' );
define( 'TPA_FILE', __FILE__ );
define( 'TPA_PATH', plugin_dir_path( TPA_FILE ) );
define( 'TPA_URL', plugin_dir_url( TPA_FILE ) );
define('TPA_FEEDBACK_API',"https://feedback.coolplugins.net/");
if ( ! class_exists( 'TranslatePressAddon' ) ) {
	/**
	 * Main Class start here
	 */
	class TranslatePressAddon {

		/**
		 *  Construct the plugin object
		 */
		public function __construct() {

			register_activation_hook( __FILE__, array( $this, 'tpa_activate' ) );
            register_deactivation_hook( __FILE__, array( $this, 'tpa_deactivate' ) );
			add_filter( 'trp_string_groups', array( $this, 'tpa_string_groups' ) );
			add_action('admin_init', array($this, 'tpa_do_activation_redirect'));

			add_action( 'init', array( $this, 'tpa_load_plugin_text_domain' ) );
			add_action( 'plugins_loaded', array( $this, 'tpa_check_required_plugin' ) );
			if ( ! is_admin() ) {
				add_action( 'trp_translation_manager_footer', array( $this, 'tpa_register_assets' ) );
			}
			add_action( 'admin_init', array( $this, 'tpa_tranlatedata_review_notice' ) );
			add_action( 'wp_ajax_tpa_get_strings', array( $this, 'tpa_getstrings' ) );
			add_action( 'wp_ajax_tpa_save_translations', array( $this, 'tpa_save_translations' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'tpa_settings_page_link' ) );
			add_filter('plugin_row_meta', array( $this,'tpa_add_docs_link_to_plugin_meta'), 10, 2);
			add_action('wp_ajax_tpa_update_translate_data', array($this, 'tpa_update_translate_data'));
			add_action( 'wp_ajax_tpa_install_plugin', array( $this, 'tpa_install_plugin' ) );
			add_action( 'wp_ajax_tpa_save_provider_states', array( $this, 'tpa_save_provider_states' ) );
			add_action( 'manage_posts_extra_tablenav', array( $this, 'tpa_render_bulk_translate_button' ), 20, 1 );
			// Initialize cron
			$this->init_cron();

			// Initialize feedback notice.
			$this->init_feedback_notice();

			// Load admin styles/scripts only on the login and admin screens.
			add_action( 'admin_enqueue_scripts', array( $this, 'tpa_enqueue_admin_assets' ) );

			// Add the action to hide unrelated notices
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter used for read-only navigation, sanitized with sanitize_key()
			if(isset($_GET['page']) && sanitize_key($_GET['page']) == 'translatepress-tpap-dashboard'){
				add_action('admin_print_scripts', array($this, 'tpa_hide_unrelated_notices'));
			}

			if(!class_exists('Tpa_Dashboard')) {
				require_once TPA_PATH . 'admin/cpt_dashboard/cpt_dashboard.php';
				new Tpa_Dashboard();
			}
		}

		/**
		 * Enqueue plugin admin assets only on login.
		 *
		 * @param string $hook_suffix Current admin page hook suffix.
		 * @return void
		 */
		public function tpa_enqueue_admin_assets( $hook_suffix ) {
			if ( ! is_user_logged_in() ) {
				return;
			}

			wp_enqueue_style( 'tpa_admin_styles', TPA_URL . 'assets/css/tpa-admin-style.css', null, TPA_VERSION, 'all' );
		}

		/**
		 * Create 'settings' link in plugins page.
		 *
		 * @param array $links use for pro plugin.
		 */
		public function tpa_settings_page_link( $links ) {
			$links[] = '<a style="font-weight:bold" target="_blank" href="' . esc_url( 'https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=plugins_list#pricing' ) . '">Buy Pro</a>';
			return $links;
		}
		/**
		 * Redirect to the plugin settings page after activation.
		 */

		public function tpa_do_activation_redirect() {
			if (get_option('tpa_do_activation_redirect', false)) {
				update_option('tpa_do_activation_redirect', false);
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter used for read-only activation redirect check, sanitized with sanitize_key()
				if (!isset($_GET['activate-multi']) || empty(sanitize_key($_GET['activate-multi']))) {
					wp_safe_redirect(admin_url('admin.php?page=translatepress-tpap-dashboard'));
					exit;
				}
			}

			if(!get_option('tpa-install-date')) {
				add_option('tpa-install-date', gmdate('Y-m-d h:i:s'));
			}

			if (!get_option('tpa_initial_save_version')) {
				add_option('tpa_initial_save_version', TPA_VERSION);
			}

			if(!get_option('tpa_provider_yandex_enabled')) {
				add_option('tpa_provider_yandex_enabled', '1');
			}
			
			if(!get_option('tpa_provider_chrome_enabled')) {
				add_option('tpa_provider_chrome_enabled', '1');
			}
		}

        public function tpa_install_plugin()
        {

            if (! current_user_can('install_plugins')) {
                wp_send_json_error([
                    // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
                    'errorMessage' => __('Sorry, you are not allowed to install plugins on this site.', 'automatic-translate-addon-for-translatepress'),
                ]);
            }

            check_ajax_referer('tpa_install_nonce', '_wpnonce', true);

            if (empty($_POST['slug'])) {
                wp_send_json_error([
                    'slug'         => '',
                    'errorCode'    => 'no_plugin_specified',
                    // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
                    'errorMessage' => __('No plugin specified.', 'automatic-translate-addon-for-translatepress'),
                ]);
            }

            $slug = sanitize_key(wp_unslash($_POST['slug']));

            // Configuration for allowed plugins
            // 'files': array of main plugin files to check/activate (prioritized)
            // 'dependency': optional plugin that must be active
            // 'dependency_msg': error message if dependency is missing
            $plugins_config = [
                'automatic-translator-addon-for-loco-translate' => [
                    'files' => [
                        'loco-automatic-translate-addon-pro/loco-automatic-translate-addon-pro.php',
                        'automatic-translator-addon-for-loco-translate/automatic-translator-addon-for-loco-translate.php'
                    ],
                    'dependency'     => 'loco-translate/loco.php',
                    'dependency_msg' => 'Please activate Loco Translate plugin first.'
                ],
                'translate-words' => [
                    'files' => [
                        'translate-words/translate-wp-words.php'
                    ],
                    'dependency'     => null,
                    'dependency_msg' => ''
                ]
            ];

            if (!isset($plugins_config[$slug])) {
                wp_send_json_error([
                    'errorMessage' => esc_html__('Invalid plugin slug.', 'automatic-translate-addon-for-translatepress'),
                ]);
            }

            $config = $plugins_config[$slug];

            if (! current_user_can('activate_plugins')) {
                wp_send_json_error(['message' => 'Permission denied']);
            }

            // Get the action (install or activate)
            $plugin_action = isset($_POST['plugin_action']) ? sanitize_text_field(wp_unslash($_POST['plugin_action'])) : 'install';

            // 1. Check if any version is already installed
            foreach ($config['files'] as $file) {
                if (file_exists(WP_PLUGIN_DIR . '/' . $file)) {
                    // Check dependency requirements before activation
                    if ($plugin_action === 'activate') {
                        if (!empty($config['dependency']) && !is_plugin_active($config['dependency'])) {
                            wp_send_json_error(['message' => $config['dependency_msg']]);
                        }
                    }

                    // Activate
                    $network_wide = is_multisite();
                    $result = activate_plugin($file, '', $network_wide, true);
                    if (is_wp_error($result)) {
                        wp_send_json_error(['message' => $result->get_error_message()]);
                    }
                    wp_send_json_success(['message' => 'Plugin activated successfully', 'activated' => true]);
                    return;
                }
            }

            // 2. Not installed, proceed to install from repository
            $this->install_plugin_from_repo($slug, $config);
        }

        /**
         * Helper to install plugin from WP Repository
         *
         * @param string $slug Plugin slug
         * @param array $config Plugin configuration
         */
        private function install_plugin_from_repo($slug, $config)
        {
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            $api = plugins_api('plugin_information', [
                'slug'   => $slug,
                'fields' => ['sections' => false],
            ]);

            if (is_wp_error($api)) {
                wp_send_json_error(['message' => $api->get_error_message()]);
            }

            $skin     = new WP_Ajax_Upgrader_Skin();
            $upgrader = new Plugin_Upgrader($skin);
            $result   = $upgrader->install($api->download_link);

            // Handle specific installation errors
            if (is_wp_error($result)) {
                wp_send_json_error(['message' => $result->get_error_message()]);
            } elseif (is_wp_error($skin->result)) {
                // Special handling for "Destination folder already exists"
                if ($skin->result->get_error_message() === 'Destination folder already exists.') {
                    $install_status = install_plugin_install_status($api);
                    if (current_user_can('activate_plugin', $install_status['file'])) {
                        // Check dependency
                        if (!empty($config['dependency']) && !is_plugin_active($config['dependency'])) {
                            wp_send_json_success(['message' => $config['dependency_msg'], 'activated' => false]);
                            return;
                        }

                        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified in tpa_install_plugin()
                        $pagenow = isset($_POST['pagenow']) ? sanitize_key(wp_unslash($_POST['pagenow'])) : '';
                        $network_wide = (is_multisite() && 'import' !== $pagenow);
                        $activation_result = activate_plugin($install_status['file'], '', $network_wide, true);

                        if (is_wp_error($activation_result)) {
                            wp_send_json_error(['message' => $activation_result->get_error_message()]);
                        } else {
                            wp_send_json_success(['activated' => true, 'message' => 'Plugin activated successfully']);
                        }
                    } else {
                        wp_send_json_error(['message' => $skin->result->get_error_message()]);
                    }
                } else {
                    wp_send_json_error(['message' => $skin->result->get_error_message()]);
                }
            } elseif ($skin->get_errors()->has_errors()) {
                wp_send_json_error(['message' => $skin->get_error_messages()]);
            } elseif (is_null($result)) {
                global $wp_filesystem;
                // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
                $error_msg = __('Unable to connect to the filesystem. Please confirm your credentials.', 'automatic-translate-addon-for-translatepress');
                if ($wp_filesystem instanceof WP_Filesystem_Base && is_wp_error($wp_filesystem->errors) && $wp_filesystem->errors->has_errors()) {
                    $error_msg = esc_html($wp_filesystem->errors->get_error_message());
                }
                wp_send_json_error(['message' => $error_msg]);
            }

            // Auto-activate after successful install
            $install_status = install_plugin_install_status($api);
            if (current_user_can('activate_plugin', $install_status['file'])) {
                // Dependency check before auto-activate
                if (!empty($config['dependency']) && !is_plugin_active($config['dependency'])) {
                    // Installed but can't activate due to dependency
                    wp_send_json_success(['message' => $config['dependency_msg'], 'activated' => false]);
                    return;
                }

                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce already verified in tpa_install_plugin()
                $pagenow = isset($_POST['pagenow']) ? sanitize_key(wp_unslash($_POST['pagenow'])) : '';
                $network_wide = (is_multisite() && 'import' !== $pagenow);
                $activation_result = activate_plugin($install_status['file'], '', $network_wide, true);
                if (is_wp_error($activation_result)) {
                    wp_send_json_error(['message' => $activation_result->get_error_message()]);
                }
                wp_send_json_success(['message' => 'Plugin installed and activated successfully', 'activated' => true]);
            } else {
                wp_send_json_success(['message' => 'Plugin installed successfully', 'activated' => false]);
            }
        }

        /**
         * Save provider toggle states
         */
        public function tpa_save_provider_states() {
            // Verify nonce
            check_ajax_referer('tpa_provider_states_nonce', '_wpnonce', true);

            // Check user capabilities
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => esc_html__('You do not have permission to modify settings.', 'automatic-translate-addon-for-translatepress')]);
                wp_die();
            }

            // Get provider states from POST data
            $yandex_enabled = isset($_POST['yandex_enabled']) ? sanitize_text_field(wp_unslash($_POST['yandex_enabled'])) : '1';
            $chrome_enabled = isset($_POST['chrome_enabled']) ? sanitize_text_field(wp_unslash($_POST['chrome_enabled'])) : '1';

            // Save to database
            update_option('tpa_provider_yandex_enabled', $yandex_enabled === '1' ? '1' : '0');
            update_option('tpa_provider_chrome_enabled', $chrome_enabled === '1' ? '1' : '0');

            wp_send_json_success(['message' => esc_html__('Provider settings saved successfully.', 'automatic-translate-addon-for-translatepress')]);
        }

		public function tpa_add_docs_link_to_plugin_meta($links, $file) {
			if (plugin_basename(__FILE__) === $file) {
				$docs_link = '<a href="https://docs.coolplugins.net/plugin/ai-translation-for-translatepress/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=docs&utm_content=plugins_list" target="_blank">Docs</a>';
				$multilingual_link = '<a target="_blank" href="' . esc_url( admin_url( 'plugin-install.php?s=Linguator+AI+Auto+Translate+Create+Multilingual+Sites&tab=search&type=term' ) ) . '">Create Multilingual Site</a>';
				$links[] = $docs_link;
				$links[] = $multilingual_link;
			}
			return $links;
		}

		/**
		 * Initialize the cron job for the plugin.
		 */
		public function init_cron(){
		// if (is_admin()) {
			
				require_once TPA_PATH . '/admin/cpfm-feedback/cron/tpa-cron.php';
			$cron = new TPA_cronjob();
			$cron->tpa_cron_init_hooks();
		// }
		}

		public function init_feedback_notice() {
			if (is_admin()) {
			
				if(!class_exists('CPFM_Feedback_Notice')){
					require_once TPA_PATH . '/admin/cpfm-feedback/cpfm-common-notice.php';
					
				}

			add_action('cpfm_register_notice', function () {
				if (!class_exists('CPFM_Feedback_Notice') || !current_user_can('manage_options')) {
					return;
				}
				
				$notice = [
					'title' => esc_html__('AI Translation For TranslatePress', 'automatic-translate-addon-for-translatepress'),
					'message' => esc_html__('Help us make this plugin more compatible with your site by sharing non-sensitive site data.', 'automatic-translate-addon-for-translatepress'),
					'pages' => ['translatepress-tpap-dashboard'],
					'always_show_on' => ['translatepress-tpap-dashboard'], // This enables auto-show
					'plugin_name'=>'tpa'
				];
				CPFM_Feedback_Notice::cpfm_register_notice('cool_translations', $notice);
					if (!isset($GLOBALS['cool_plugins_feedback'])) {
						$GLOBALS['cool_plugins_feedback'] = [];
					}
					$GLOBALS['cool_plugins_feedback']['cool_translations'][] = $notice;
			});

			add_action('cpfm_after_opt_in_tpa', function($category) {
				if ($category === 'cool_translations') {
					TPA_cronjob::tpa_send_data();
					$options = get_option('tpa_feedback_opt_in');
					$options = 'yes';
					update_option('tpa_feedback_opt_in', $options);	
				}
			  });
		    }
		}

		/**
		 * Set settings on plugin activation.
		 */
		public function tpa_activate() {
			$active_plugins = get_option('active_plugins', array());
            if (!in_array("automatic-translate-addon-pro-for-translatepress/automatic-translate-addon-for-translatepress-pro.php", $active_plugins) && in_array("translatepress-multilingual/index.php", $active_plugins)) {
                add_option('tpa_do_activation_redirect', true);
            }

			update_option( 'tpa-v', TPA_VERSION );
			update_option( 'tpa-type', 'FREE' );
			update_option( 'tpa-installDate', gmdate( 'Y-m-d h:i:s' ) );
			update_option( 'tpa-ratingDiv', 'no' );

			if(!get_option('tpa-install-date')) {
				add_option('tpa-install-date', gmdate('Y-m-d h:i:s'));
			}

			if (!get_option( 'tpa_initial_save_version' ) ) {
				add_option( 'tpa_initial_save_version', TPA_VERSION );
			}

			if(!get_option('tpa_provider_yandex_enabled')) {
				add_option('tpa_provider_yandex_enabled', '1');
			}
			if(!get_option('tpa_provider_chrome_enabled')) {
				add_option('tpa_provider_chrome_enabled', '1');
			}

			$get_opt_in = get_option('tpa_feedback_opt_in');
			
			if ($get_opt_in =='yes' && !wp_next_scheduled('tpa_extra_data_update')) {

				wp_schedule_event(time(), 'every_30_days', 'tpa_extra_data_update');
			}


		}

		/**
		 * Set settings on plugin deactivation.
		 */
		public function tpa_deactivate() {

			wp_clear_scheduled_hook('tpa_extra_data_update');
			
		}
	/**
	 * Change string groups
	 */
	public function tpa_string_groups() {
		$string_groups = array(
			'slugs'           => 'Slugs',
			'metainformation' => 'Meta Information',
			'stringlist'      => 'String List',
			'gettextstrings'  => 'Gettext Strings',
			'images'          => 'Images',
			'videos'          => 'Videos',
			'audios'          => 'Audios',
			'dynamicstrings'  => 'Dynamically Added Strings',
		);
		return $string_groups;
	}


		/**
		 * Update translation data
		 */
			public function tpa_update_translate_data() {
		// Verify nonce
		if ( ! check_ajax_referer( 'auto-translate-press-nonces', false ) ) {
			wp_send_json_error( esc_html__( 'Invalid security token sent.', 'automatic-translate-addon-for-translatepress' ) );
			wp_die( '0', 400 );
			exit();
		}

		// Check user capabilities
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => esc_html__('You do not have permission to modify translation data.', 'automatic-translate-addon-for-translatepress')));
			wp_die();
		}
		
		// Validate and decode the JSON data
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON data will be decoded and sanitized after json_decode()
		$raw_data = isset($_POST['data']) ? wp_unslash($_POST['data']) : '';
		if (empty($raw_data)) {
			wp_send_json_error(esc_html__('No data provided.', 'automatic-translate-addon-for-translatepress'));
			wp_die();
		}
		
		// Validate JSON structure before processing
		$data = json_decode($raw_data, true);
		// Sanitize decoded data
		$data = is_array($data) ? array_map('sanitize_text_field', $data) : array();
		if (json_last_error() !== JSON_ERROR_NONE) {
			wp_send_json_error(esc_html__('Invalid JSON data provided.', 'automatic-translate-addon-for-translatepress'));
			wp_die();
		}
		
		// Additional validation for JSON structure
		if (!is_array($data)) {
			wp_send_json_error(esc_html__('JSON data must decode to an array.', 'automatic-translate-addon-for-translatepress'));
			wp_die();
		}
			$provider = isset($data['provider']) ? sanitize_text_field($data['provider']) : '';
			$total_word_count = isset($data['totalWordCount']) ? absint($data['totalWordCount']) : 0;
			$total_char_count = isset($data['totalCharacterCount']) ? absint($data['totalCharacterCount']) : 0;
			$date = isset($data['date']) ? gmdate('Y-m-d H:i:s', strtotime(sanitize_text_field($data['date']))) : '';
			$source_lang = isset($data['default_lang']) ? sanitize_text_field($data['default_lang']) : '';
			$target_lang = isset($data['language_code']) ? sanitize_text_field($data['language_code']) : '';
			$time_taken = isset($data['timeTaken']) ? absint($data['timeTaken']) : 0;
			$post_id = isset($data['post_id']) ? absint($data['post_id']) : 0;
			if (class_exists('Tpa_Dashboard')) {
				$translation_data = array(
					'post_id' => $post_id,
					'service_provider' => $provider,
					'source_language' => $source_lang,
					'target_language' => $target_lang,
					'time_taken' => $time_taken,
					'string_count' => $total_word_count,
					'character_count' => $total_char_count,
					'date_time' => $date,
					'version_type' => 'free'
				);

				Tpa_Dashboard::store_options(
					'tpa',
					'post_id', 
					'update',
					$translation_data
				);

				wp_send_json_success(
					die()
				// 	array(
				// 	'message' => __('Translation data updated successfully', 'automatic-translate-addon-for-translatepress')
				// )
			);
			} else {
				wp_send_json_error(array(
					'message' => esc_html__('Tpa_Dashboard class not found', 'automatic-translate-addon-for-translatepress') 
				));
			}
			exit;
		}

		/**
		 *  Show review notice of translate data.
		 */
		public function tpa_tranlatedata_review_notice() {
			$already_rated     = get_option( 'tpa-ratingDiv' ) != false ? get_option( 'tpa-ratingDiv' ) : 'no';
			if(class_exists('Tpa_Dashboard') && ($already_rated === 'no') && !defined( 'TPAP_VERSION' )) {
				Tpa_Dashboard::review_notice(
					'tpa', // Required
					'AI Translation For TranslatePress', // Required
					'https://wordpress.org/support/plugin/automatic-translate-addon-for-translatepress/reviews/#new-post' // Required
					
				);
			}
		}

		/*
		|------------------------------------------------------------------------
		|  Hide unrelated notices
		|------------------------------------------------------------------------
		*/

		public function tpa_hide_unrelated_notices()
			{ // phpcs:ignore Generic.Metrics.CyclomaticComplexity.MaxExceeded, Generic.Metrics.NestingLevel.MaxExceeded
				$cfkef_pages = false;
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter used for read-only navigation, sanitized with sanitize_key()
				if(isset($_GET['page']) && sanitize_key($_GET['page']) == 'translatepress-tpap-dashboard'){
					$cfkef_pages = true;
				}

				if ($cfkef_pages) {
					global $wp_filter;
					// Define rules to remove callbacks.
					$rules = [
						'user_admin_notices' => [], // remove all callbacks.
						'admin_notices'      => [],
						'all_admin_notices'  => [],
						'admin_footer'       => [
							'render_delayed_admin_notices', // remove this particular callback.
						],
					];
					$notice_types = array_keys($rules);
					foreach ($notice_types as $notice_type) {
						if (empty($wp_filter[$notice_type]->callbacks) || ! is_array($wp_filter[$notice_type]->callbacks)) {
							continue;
						}
						$remove_all_filters = empty($rules[$notice_type]);
						foreach ($wp_filter[$notice_type]->callbacks as $priority => $hooks) {
							foreach ($hooks as $name => $arr) {
								if (is_object($arr['function']) && is_callable($arr['function'])) {
									if ($remove_all_filters) {
										unset($wp_filter[$notice_type]->callbacks[$priority][$name]);
									}
									continue;
								}
								$class = ! empty($arr['function'][0]) && is_object($arr['function'][0]) ? strtolower(get_class($arr['function'][0])) : '';
								// Remove all callbacks except WPForms notices.
								if ($remove_all_filters && strpos($class, 'wpforms') === false) {
									unset($wp_filter[$notice_type]->callbacks[$priority][$name]);
									continue;
								}
								$cb = is_array($arr['function']) ? $arr['function'][1] : $arr['function'];
								// Remove a specific callback.
								if (! $remove_all_filters) {
									if (in_array($cb, $rules[$notice_type], true)) {
										unset($wp_filter[$notice_type]->callbacks[$priority][$name]);
									}
									continue;
								}
							}
						}
					}
				}

				 add_action( 'admin_notices', [ $this, 'tpa_admin_notices' ], PHP_INT_MAX );
			}

			function tpa_admin_notices() {
				do_action( 'tpa_display_admin_notices' );
			}

			function tpa_display_admin_notices() {

				$already_rated     = get_option( 'tpa-ratingDiv' ) != false ? get_option( 'tpa-ratingDiv' ) : 'no';
				if(class_exists('Tpa_Dashboard') && ($already_rated === 'no') && !defined( 'TPAP_VERSION' )) {
					Tp_Dashboard::review_notice(
						'tpa', // Required
						'AI Translation For TranslatePress', // Required
						'https://wordpress.org/plugins/automatic-translate-addon-for-translatepress/reviews/#new-post', // Required
						
					);
				}
			}

		/**
		 * Check if required "TranslatePress - Multilingual" plugin is activeF
		 * also register the plugin text domain
		 */

		public function tpa_load_plugin_text_domain(){
			// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- Required for custom text domain loading
			load_plugin_textdomain( 'TPA', false, basename( dirname( TPA_FILE ) ) . '/languages/' );
			if(!get_option('tpa-install-date')) {
				add_option('tpa-install-date', gmdate('Y-m-d h:i:s'));
			}

			if (!get_option( 'tpa_initial_save_version' ) ) {
                add_option( 'tpa_initial_save_version', TPA_VERSION );
            }
		}

		public function tpa_check_required_plugin() {

			if ( is_admin() && !defined( 'TPAP_VERSION' ) ) {
				include_once TPA_PATH . 'admin/tpap-register/tpap-admin-menu.php';
				/** Feedback form after deactivation */
				require_once __DIR__ . '/admin/feedback/admin-feedback-form.php';
			}
		}

		
		/**
		 *  Register Assets
		 * Hooked to trp_translation_manager_footer.
		 */
		public function tpa_register_assets() {
			wp_register_script( 'tpa-yandex-widget', TPA_URL . 'assets/js/widget.js?widgetId=ytWidget&pageLang=en&widgetTheme=light&autoMode=false', array(), TPA_VERSION, true );
			wp_register_script( 'tpa-chrome-ai-translation', TPA_URL . 'assets/js/chrome-ai-translation.js', array(), TPA_VERSION, true );
			wp_register_script( 'tpscript', TPA_URL . 'assets/js/tpa-custom-script.js', array( 'jquery', 'jquery-ui-dialog', 'tpa-chrome-ai-translation' ), TPA_VERSION, true );
			wp_register_style( 'tpa-editor-styles', TPA_URL . 'assets/css/tpa-custom.css', null, TPA_VERSION, 'all' );
			$extra_data['preloader_path'] = TPA_URL . '/assets/images/preloader.gif';
			$extra_data['gt_preview']     = TPA_URL . '/assets/images/google.png';
			$extra_data['yt_preview']     = TPA_URL . '/assets/images/yandex.png';
			$extra_data['chrome_preview']     = TPA_URL . '/assets/images/chrome.png';
			$extra_data['openai_preview']  = TPA_URL . '/assets/images/openAI.png';
			$extra_data['gemini_preview']  = TPA_URL . '/assets/images/google-gemini.png';
			$extra_data['anthropic_preview']  = TPA_URL . '/assets/images/anthropic.png';
			$extra_data['document_preview']  = TPA_URL . '/assets/images/document.svg';
        	$extra_data['error_preview'] = TPA_URL . '/assets/images/error-icon.svg';
			$extra_data['dashboard_url'] = admin_url('admin.php?page=');
			$extra_data['extra_class']= is_rtl() ? 'tpa-rtl' : '';
			$extra_data['ajax_url']       = admin_url( 'admin-ajax.php' );
			$extra_data['nonce']          = wp_create_nonce( 'auto-translate-press-nonces' );
			$extra_data['plugin_url']     = plugins_url();
			$extra_data['post_id']        = get_the_ID();
			$extra_data['provider_yandex_enabled'] = get_option('tpa_provider_yandex_enabled', '1');
			$extra_data['provider_chrome_enabled'] = get_option('tpa_provider_chrome_enabled', '1');
			$extra_data['chrome_ai_bypass_browser_check'] = get_option('tpa_chrome_ai_bypass_browser_check', '0');
			$extra_data['chrome_ai_bypass_security_check'] = get_option('tpa_chrome_ai_bypass_security_check', '0');
			$extra_data['chrome_ai_bypass_api_check'] = get_option('tpa_chrome_ai_bypass_api_check', '0');
			wp_enqueue_script( 'tpa-chrome-ai-translation' );
			wp_enqueue_script( 'tpscript' );
			wp_localize_script( 'tpscript', 'extradata', $extra_data );
			wp_enqueue_script( 'tpa-yandex-widget' );
			wp_print_styles( 'tpa-editor-styles' );
		}
		/**
		 * Get Data From Database
		 * Hooked to wp_ajax_get_strings.
		 */
		public function tpa_getstrings() {
			// Verify nonce
			if (!check_ajax_referer('auto-translate-press-nonces', false)) {
				wp_send_json_error(array('message' => esc_html__('Security check failed.', 'automatic-translate-addon-for-translatepress')));
				wp_die();
			}

			// Check user capabilities
			if (!current_user_can('manage_options')) {
				wp_send_json_error(array('message' => esc_html__('You do not have permission to access translation strings.', 'automatic-translate-addon-for-translatepress')));
				wp_die();
			}

			$reg_exUrl = '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/';
			global $wpdb;
			$result           = array();
			$data             = array();
			$default_code     = isset( $_POST['data'] ) ? sanitize_text_field( wp_unslash( $_POST['data'] ) ) : '';
			$default_language = isset( $_POST['default_lang'] ) ? sanitize_text_field( wp_unslash( $_POST['default_lang'] ) ) : '';
			$current_page_id  = isset( $_POST['dictionary_id'] ) ? sanitize_text_field( wp_unslash( $_POST['dictionary_id'] ) ) : '';
			$gettxt_id        = isset( $_POST['gettxt_id'] ) ? sanitize_text_field( wp_unslash( $_POST['gettxt_id'] ) ) : '';
			$strings_ID       = explode( ',', $current_page_id );
			$get_txt_ids      = explode( ',', $gettxt_id );
			$in_str_arrs      = array_fill( 0, count( $get_txt_ids ), '%d' );
			$in_strs          = join( ',', $in_str_arrs );
			$in_str_arr       = array_fill( 0, count( $strings_ID ), '%d' );
			$in_str           = join( ',', $in_str_arr );
			$def_lang         = strtolower( $default_language );
			$table2           = $wpdb->get_blog_prefix() . 'trp_gettext_' . strtolower( $default_code );
			$table1           = $wpdb->get_blog_prefix() . 'trp_dictionary_' . $def_lang . '_' . strtolower( $default_code );
			// Sanitize and validate IDs
			$sanitized_strings_ID = array_map('absint', $strings_ID);
			$sanitized_get_txt_ids = array_map('absint', $get_txt_ids);
			
			// Build secure IN clauses
			$in_str_placeholders = implode(',', array_fill(0, count($sanitized_strings_ID), '%d'));
			$in_strs_placeholders = implode(',', array_fill(0, count($sanitized_get_txt_ids), '%d'));
			
			// Validate table names by ensuring they only contain allowed patterns
			$valid_table_pattern = '/^' . preg_quote($wpdb->get_blog_prefix(), '/') . '(trp_dictionary|trp_gettext)_[a-z0-9_]+$/';
			
			if (!preg_match($valid_table_pattern, $table1) || !preg_match($valid_table_pattern, $table2)) {
				wp_send_json_error(array('message' => 'Invalid table name'));
				return;
			}
			
			// Use esc_sql for table names
			$table1_name = esc_sql($table1);
			$table2_name = esc_sql($table2);
			
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names are validated and escaped with esc_sql(), placeholders are properly prepared
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Querying TranslatePress plugin tables (not WordPress core), caching not applicable for dynamic translation data
			$results_gettxt = $wpdb->get_results(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names are validated and escaped with esc_sql(), placeholders are properly prepared
					"SELECT id, original_id, original FROM {$table1_name} WHERE id IN ($in_str_placeholders) AND status != %s",
					array_merge($sanitized_strings_ID, array('2'))
				)
			);
			
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Querying TranslatePress plugin tables (not WordPress core), caching not applicable for dynamic translation data
			$results = $wpdb->get_results(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names are validated and escaped with esc_sql(), placeholders are properly prepared
					"SELECT id, original FROM {$table2_name} WHERE id IN ($in_strs_placeholders) AND status != %s",
					array_merge($sanitized_get_txt_ids, array('2'))
				)
			);
			$final_res        = array_merge( $results_gettxt, $results );
			if ( is_array( $final_res ) && count( $final_res ) > 0 ) {
				foreach ( $final_res as $row ) {
					$original_id = isset( $row->original_id ) ? absint( $row->original_id ) : '';
					$original    = isset( $row->original ) ? $row->original : '';
					$string      = htmlspecialchars_decode( $original );
					if ( $string != wp_strip_all_tags( $string ) ) {
						continue;
					} elseif ( preg_match( $reg_exUrl, $string ) ) {
						continue;
					}
					if ( $original_id == '' ) {
						$group = 'Gettext';
					} else {
						$group = 'String';
					}
					$data['strings']      = $string;
					$data['database_ids'] = isset( $row->id ) ? absint( $row->id ) : '';
					$data['data_group']   = $group;

					$result[] = $data;
				}
			}
			wp_send_json( $result );
		}
		/**
		 *  Save translation from ajax post
		 * Hooked to wp_ajax_save_translations.
		 */
		public function tpa_save_translations() {
			// Verify nonce
			if (!check_ajax_referer('auto-translate-press-nonces', false)) {
				wp_send_json_error(array('message' => esc_html__('Security check failed.', 'automatic-translate-addon-for-translatepress')));
				wp_die();
			}

			// Check user capabilities
			if (!current_user_can('manage_options')) {
				wp_send_json_error(array('message' => esc_html__('You do not have permission to modify translations.', 'automatic-translate-addon-for-translatepress')));
				wp_die();
			}

			// Validate POST data
			if (!isset($_POST['data']) || empty($_POST['data'])) {
				wp_send_json_error(array('message' => esc_html__('No translation data provided.', 'automatic-translate-addon-for-translatepress')));
				wp_die();
			}

			global $wpdb;
			
			// Sanitize and decode JSON data
			$raw_data = sanitize_text_field(wp_unslash($_POST['data']));
			$decoded_data = json_decode($raw_data, true);
			
			if (json_last_error() !== JSON_ERROR_NONE) {
				wp_send_json_error(array('message' => esc_html__('Invalid JSON data provided.', 'automatic-translate-addon-for-translatepress')));
				wp_die();
			}
			
			// Sanitize array values
			$strings = array();
			if (is_array($decoded_data)) {
				foreach ($decoded_data as $key => $value) {
					if (is_array($value)) {
						$strings[$key] = array_map('sanitize_text_field', $value);
					}
				}
			}
			if ( is_array( $strings ) && count( $strings ) > 0 ) {
				$table1_query = array();
				$table2_query = array();
				$table1       = null;
				$table2       = null;
				foreach ( $strings as $languages => $string ) {
					$types            = isset( $string['data_group'] ) ? sanitize_text_field( $string['data_group'] ) : '';
					$default_code     = isset( $string['language_code'] ) ? sanitize_text_field( $string['language_code'] ) : '';
					$default_language = isset( $string['default_lang'] ) ? sanitize_text_field( $string['default_lang'] ) : '';
					$def_lang         = strtolower( $default_language );
					$table2           = $wpdb->get_blog_prefix() . 'trp_gettext_' . strtolower( $default_code );
					$table1           = $wpdb->get_blog_prefix() . 'trp_dictionary_' . $def_lang . '_' . strtolower( $default_code );
					if ( $types == 'String' ) {
						$table_name     = sanitize_text_field( $table1 );
						$table1_query[] = $string;
					} else {
						$table_name     = sanitize_text_field( $table2 );
						$table2_query[] = $string;
					}
				}
				if ( null !== $table1 && null !== $table2 ) {
					$this->wp_insert_rows( $table1, true, 'id', $table1_query );
					$this->wp_insert_rows( $table2, true, 'id', $table2_query );
				}
				wp_die();
			}
		}
		/**
		 *  A method for inserting multiple rows into the specified table
		 *  Updated to include the ability to Update existing rows by primary key.
		 *
		 * @param string $wp_table_name use for pro plugin.
		 *
		 * @param bool   $update use for pro plugin.
		 *
		 * @param string $primary_key use for pro plugin.
		 *
		 * @param array  $row_arrays use for pro plugin.
		 */
			public function wp_insert_rows( $wp_table_name, $update = false, $primary_key = 'id', $row_arrays = array() ) {
		global $wpdb;
		
		// Validate inputs
		if (empty($wp_table_name) || empty($row_arrays) || !is_array($row_arrays)) {
			return false;
		}
		
		// Validate table name by ensuring it only contains allowed patterns
		$valid_table_pattern = '/^' . preg_quote($wpdb->prefix, '/') . '[a-zA-Z0-9_]+$/';
		if (!preg_match($valid_table_pattern, $wp_table_name)) {
			return false;
		}
		
		// Use esc_sql for table name
		$table_name = esc_sql($wp_table_name);
		
		// Validate primary key
		$primary_key = sanitize_key($primary_key);
		
		$success = true;
		
		// Process rows one by one using WordPress methods
		foreach ($row_arrays as $row_data) {
			// Filter out non-database fields
			$data = array_diff_key($row_data, array_flip(array('data_group', 'original', 'language_code', 'database_id', 'default_lang')));
			
			// Skip empty rows
			if (empty($data)) {
				continue;
			}
			
			// Use WordPress direct methods based on update parameter
			if ($update && isset($data[$primary_key])) {
				// Use update if primary key exists
				$where = array($primary_key => $data[$primary_key]);
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Using standard WordPress $wpdb->update() method for TranslatePress plugin tables
				$result = $wpdb->update($table_name, $data, $where);
			} else {
				// Use insert for new rows
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Using standard WordPress $wpdb->insert() method for TranslatePress plugin tables
				$result = $wpdb->insert($table_name, $data);
			}
			
			// Track if any operation fails
			if ($result === false) {
				$success = false;
			}
		}
		
		return $success;
	}

	/**
	 * Output the Bulk Translate button in the posts list table.
	 *
	 * Hooked into `manage_posts_extra_tablenav`.
	 *
	 * @param string $which 'top' or 'bottom' tablenav.
	 * @return void
	 */
	public function tpa_render_bulk_translate_button( $which ) {
		// Only show in the top toolbar.
		if ( 'top' !== $which ) {
			return;
		}

		// Don't show on Trash views.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only: used only to decide button visibility, value sanitized.
		$current_status = isset( $_GET['post_status'] ) ? sanitize_key( wp_unslash( $_GET['post_status'] ) ) : '';
		if ( 'trash' === $current_status ) {
			return;
		}

		echo '<a type="button" class="button tpa-bulk-translate-btn" href="https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=bulk_translate#pricing" target="_blank">' . esc_html__( 'AI Translate', 'automatic-translate-addon-for-translatepress' ) . '</a>';
	}

	public static function tpa_get_user_info() {
		global $wpdb;
		$server_info = [
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- $_SERVER variables don't require unslashing
		'server_software'        => sanitize_text_field($_SERVER['SERVER_SOFTWARE'] ?? 'N/A'),
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- MySQL version query, caching not necessary as version rarely changes
		'mysql_version'          => sanitize_text_field($wpdb->get_var("SELECT VERSION()")),
		'php_version'            => sanitize_text_field(phpversion()),
		'wp_version'             => sanitize_text_field(get_bloginfo('version')),
		'wp_debug'               => sanitize_text_field(defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled'),
		'wp_memory_limit'        => sanitize_text_field(ini_get('memory_limit')),
		'wp_max_upload_size'     => sanitize_text_field(ini_get('upload_max_filesize')),
		'wp_permalink_structure' => sanitize_text_field(get_option('permalink_structure', 'Default')),
		'wp_multisite'           => sanitize_text_field(is_multisite() ? 'Enabled' : 'Disabled'),
		'wp_language'            => sanitize_text_field(get_option('WPLANG', get_locale()) ?: get_locale()),
		'wp_prefix'              => sanitize_key($wpdb->prefix), // Sanitizing database prefix
		];
		$theme_data = [
		'name'      => sanitize_text_field(wp_get_theme()->get('Name')),
		'version'   => sanitize_text_field(wp_get_theme()->get('Version')),
		'theme_uri' => esc_url(wp_get_theme()->get('ThemeURI')),
		];
		if (!function_exists('get_plugins')) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_data = array_map(function ($plugin) {
		$plugin_info = get_plugin_data(WP_PLUGIN_DIR . '/' . sanitize_text_field($plugin));
		$author_url = ( isset( $plugin_info['AuthorURI'] ) && !empty( $plugin_info['AuthorURI'] ) ) ? esc_url( $plugin_info['AuthorURI'] ) : 'N/A';
		$plugin_url = ( isset( $plugin_info['PluginURI'] ) && !empty( $plugin_info['PluginURI'] ) ) ? esc_url( $plugin_info['PluginURI'] ) : '';
		return [
			'name'       => sanitize_text_field($plugin_info['Name']),
			'version'    => sanitize_text_field($plugin_info['Version']),
			'plugin_uri' => !empty($plugin_url) ? $plugin_url : $author_url,
		];
		}, get_option('active_plugins', []));
		return [
			'server_info' => $server_info,
			'extra_details' => [
				'wp_theme' => $theme_data,
				'active_plugins' => $plugin_data,
			]
		];
	}
		/**
		* TranslatePressAddon Class Close
		*/
	}
}
new TranslatePressAddon();
