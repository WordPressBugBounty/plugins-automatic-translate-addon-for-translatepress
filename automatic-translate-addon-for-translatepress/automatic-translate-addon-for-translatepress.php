<?php
/**
 * Plugin Name: AI Translation For TranslatePress
 * Description: Auto language translator add-on for TranslatePress to translate your website into any language using AI & Machine Translation tools—No API Key Needed!.
 * Author: Cool Plugins
 * Author URI: https://coolplugins.net/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=author_page&utm_content=dashboard
 * Plugin URI:
 * Version: 1.2.3
 * License: GPL2
 * Text Domain:TPA
 * Domain Path: languages
 *
 *  @package TPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( defined( 'TPA_VERSION' ) ) {
	return;
}
define( 'TPA_VERSION', '1.2.3' );
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
			add_action('wp_ajax_tpa_update_translate_data', array($this, 'tpa_update_translate_data'));

			// Initialize cron
			$this->init_cron();

			// Initialize feedback notice.
			$this->init_feedback_notice();

			// Add the action to hide unrelated notices
			if(isset($_GET['page']) && $_GET['page'] == 'translatepress-tpap-dashboard'){
				add_action('admin_print_scripts', array($this, 'tpa_hide_unrelated_notices'));
			}

			if(!class_exists('Tpa_Dashboard')) {
				require_once TPA_PATH . 'admin/cpt_dashboard/cpt_dashboard.php';
				new Tpa_Dashboard();
			}
		}

		/**
		 * Create 'settings' link in plugins page.
		 *
		 * @param array $links use for pro plugin.
		 */
		public function tpa_settings_page_link( $links ) {
			$links[] = '<a style="font-weight:bold" target="_blank" href="' . esc_url( 'https://coolplugins.net/product/automatic-translate-addon-for-translatepress-pro/?utm_source=tpa_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=dashboard#pricing' ) . '">Buy Pro</a>';
			return $links;
		}
		/**
		 * Redirect to the plugin settings page after activation.
		 */

		public function tpa_do_activation_redirect() {
			if (get_option('tpa_do_activation_redirect', false)) {
                update_option('tpa_do_activation_redirect', false);
				if (!isset($_GET['activate-multi'])) {
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
			
			// 	require_once TPA_PATH . '/admin/cpfm-feedback/cron/tpa-cron.php';
			// $cron = new TPA_cronjob();
			// $cron->tpa_cron_init_hooks();

				if(!class_exists('CPFM_Feedback_Notice')){
					require_once TPA_PATH . '/admin/cpfm-feedback/cpfm-common-notice.php';
					
				}

			add_action('cpfm_register_notice', function () {
				if (!class_exists('CPFM_Feedback_Notice') || !current_user_can('manage_options')) {
					return;
				}
				
				$notice = [
					'title' => __('AI Translation For TranslatePress', 'TPA'),
					'message' => __('Help us make this plugin more compatible with your site by sharing non-sensitive site data.', 'TPA'),
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
				'dynamicstrings'  => 'Dynamically Added Strings',
			);
			return $string_groups;
		}


		/**
		 * Update translation data
		 */
		public function tpa_update_translate_data() {
			if ( ! check_ajax_referer( 'auto-translate-press-nonces', false ) ) {
				wp_send_json_error( __( 'Invalid security token sent.', 'automatic-translations-for-polylang' ) );
				wp_die( '0', 400 );
				exit();
			}
			// Decode the JSON data
			$data = json_decode(stripslashes($_POST['data']), true);
			$provider = isset($data['provider']) ? sanitize_text_field($data['provider']) : '';
			$total_word_count = isset($data['totalWordCount']) ? absint($data['totalWordCount']) : 0;
			$total_char_count = isset($data['totalCharacterCount']) ? absint($data['totalCharacterCount']) : 0;
			$date = isset($data['date']) ? date('Y-m-d H:i:s', strtotime(sanitize_text_field($data['date']))) : '';
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
				// 	'message' => __('Translation data updated successfully', 'tpap')
				// )
			);
			} else {
				wp_send_json_error(array(
					'message' => __('Tpa_Dashboard class not found', 'tpap') 
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
					'https://wordpress.org/plugins/automatic-translate-addon-for-translatepress/reviews/#new-post', // Required
					TPA_URL . 'assets/images/tpa-icon.png'
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

				if(isset($_GET['page']) && $_GET['page'] == 'translatepress-tpap-dashboard'){
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
						TPA_URL . 'assets/images/tpa-icon.png'
					);
				}
			}

		/**
		 * Check if required "TranslatePress - Multilingual" plugin is activeF
		 * also register the plugin text domain
		 */

		public function tpa_load_plugin_text_domain(){
			
			load_plugin_textdomain( 'TPA', false, basename( dirname( TPA_FILE ) ) . '/languages/' );
			if(!get_option('tpa-install-date')) {
				add_option('tpa-install-date', gmdate('Y-m-d h:i:s'));
			}

			if (!get_option( 'tpa_initial_save_version' ) ) {
                add_option( 'tpa_initial_save_version', TPA_VERSION );
            }
		}

		public function tpa_check_required_plugin() {
			if ( ! function_exists( 'trp_enable_translatepress' ) ) {
				add_action( 'admin_notices', array( $this, 'tpa_plugin_required_admin_notice' ) );
				add_action( 'tpa_display_admin_notices', array( $this, 'tpa_plugin_required_admin_notice' ) );
			}

			if ( is_admin() && !defined( 'TPAP_VERSION' ) ) {
				include_once TPA_PATH . 'admin/tpap-register/tpap-admin-menu.php';
				/** Feedback form after deactivation */
				require_once __DIR__ . '/admin/feedback/admin-feedback-form.php';
				/*** Plugin review notice file */
				require_once TPA_PATH . 'admin/tpa-feedback-notice.php';
				new TPAFeedbackNotice();
			}
		}
		/**
		 * Notice to 'Admin' if "TranslatePress - Multilingual" is not active
		 */
		public function tpa_plugin_required_admin_notice() {
			if ( current_user_can( 'activate_plugins' ) ) {
				$url         = 'plugin-install.php?tab=plugin-information&plugin=translatepress-multilingual&TB_iframe=true';
				$title       = 'TranslatePress - Multilingual';
				$plugin_info = get_plugin_data( TPA_FILE, true, true );
				echo '<div class="error"><p>' .
				sprintf(
					__(
						'In order to use <strong>%1$s</strong> plugin, please install and activate the latest version  of <a href="%2$s" class="thickbox" title="%3$s">%4$s</a>',
						'TPA'
					),
					esc_html( $plugin_info['Name'] ),
					esc_url( $url ),
					esc_attr( $title ),
					esc_attr( $title )
				) . '.</p></div>';

				deactivate_plugins( plugin_basename( TPA_FILE ) );
			}
		}
		/**
		 *  Register Assets
		 * Hooked to trp_translation_manager_footer.
		 */
		public function tpa_register_assets() {
			wp_register_script( 'tpscript', TPA_URL . 'assets/js/tpa-custom-script.min.js', array( 'jquery', 'jquery-ui-dialog' ), TPA_VERSION );
			wp_register_script( 'tpa-yandex-widget', TPA_URL . 'assets/js/widget.js?widgetId=ytWidget&pageLang=en&widgetTheme=light&autoMode=false', array(), TPA_VERSION, true );
			wp_register_style( 'tpa-editor-styles', TPA_URL . 'assets/css/tpa-custom.css', null, TPA_VERSION, 'all' );
			$extra_data['preloader_path'] = TPA_URL . '/assets/images/preloader.gif';
			$extra_data['gt_preview']     = TPA_URL . '/assets/images/powered-by-google.png';
			$extra_data['yt_preview']     = TPA_URL . '/assets/images/powered-by-yandex.png';
			$extra_data['chrome_preview']     = TPA_URL . '/assets/images/powered-by-chrome-api.png';
			$extra_data['document_preview']  = TPA_URL . '/assets/images/document.svg';
        	$extra_data['information_preview'] = TPA_URL . '/assets/images/information.svg';
			$extra_data['extra_class']= is_rtl() ? 'tpa-rtl' : '';
			$extra_data['ajax_url']       = admin_url( 'admin-ajax.php' );
			$extra_data['nonce']          = wp_create_nonce( 'auto-translate-press-nonces' );
			$extra_data['plugin_url']     = plugins_url();
			$extra_data['post_id']        = get_the_ID();
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
			// Ready for the magic to protect our code.
			check_ajax_referer( 'auto-translate-press-nonces' );
			$reg_exUrl = '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/';
			global $wpdb;
			$result           = array();
			$data             = array();
			$default_code     = isset( $_POST['data'] ) ? sanitize_text_field( $_POST['data'] ) : '';
			$default_language = isset( $_POST['default_lang'] ) ? sanitize_text_field( $_POST['default_lang'] ) : '';
			$current_page_id  = isset( $_POST['dictionary_id'] ) ? sanitize_text_field( $_POST['dictionary_id'] ) : '';
			$gettxt_id        = isset( $_POST['gettxt_id'] ) ? sanitize_text_field( $_POST['gettxt_id'] ) : '';
			$strings_ID       = explode( ',', $current_page_id );
			$get_txt_ids      = explode( ',', $gettxt_id );
			$in_str_arrs      = array_fill( 0, count( $get_txt_ids ), '%d' );
			$in_strs          = join( ',', $in_str_arrs );
			$in_str_arr       = array_fill( 0, count( $strings_ID ), '%d' );
			$in_str           = join( ',', $in_str_arr );
			$def_lang         = strtolower( $default_language );
			$table2           = $wpdb->get_blog_prefix() . 'trp_gettext_' . strtolower( $default_code );
			$table1           = $wpdb->get_blog_prefix() . 'trp_dictionary_' . $def_lang . '_' . strtolower( $default_code );
			$results_gettxt   = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id, original_id, original FROM $table1 WHERE id IN ($in_str) AND  status!='2'",
					$strings_ID
				)
			);
			$results          = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id, original FROM $table2 WHERE id IN ($in_strs) AND status!='2'",
					$get_txt_ids
				)
			);
			$final_res        = array_merge( $results_gettxt, $results );
			if ( is_array( $final_res ) && count( $final_res ) > 0 ) {
				foreach ( $final_res as $row ) {
					$original_id = isset( $row->original_id ) ? absint( $row->original_id ) : '';
					$original    = isset( $row->original ) ? $row->original : '';
					$string      = htmlspecialchars_decode( $original );
					if ( $string != strip_tags( $string ) ) {
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
			echo json_encode( $result );
			wp_die();
		}
		/**
		 *  Save translation from ajax post
		 * Hooked to wp_ajax_save_translations.
		 */
		public function tpa_save_translations() {
			// Ready for the magic to protect our code.
			check_ajax_referer( 'auto-translate-press-nonces' );
			global $wpdb;
			$strings = filter_var_array( json_decode( stripslashes( $_POST['data'] ), true ), FILTER_SANITIZE_STRING );
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
			$wp_table_name = esc_sql( $wp_table_name );
			// Setup arrays for Actual Values, and Placeholders.
			$values        = array();
			$place_holders = array();
			$query         = '';
			$query_columns = '';
			$query        .= "INSERT INTO `{$wp_table_name}` (";
			foreach ( $row_arrays as $count => $row_array ) {
				foreach ( $row_array as $key => $value ) {
					if ( in_array( $key, array( 'data_group', 'original', 'language_code', 'database_id', 'default_lang' ) ) ) {
						continue;
					}
					if ( $count == 0 ) {
						if ( $query_columns ) {
							$query_columns .= ', `' . $key . '`';
						} else {
							$query_columns .= '`' . $key . '`';
						}
					}
					$values[] = $value;
					$symbol   = '%s';
					if ( is_numeric( $value ) ) {
						$symbol = '%d';
					}
					if ( isset( $place_holders[ $count ] ) ) {
						$place_holders[ $count ] .= ", '$symbol'";
					} else {
						$place_holders[ $count ] = "( '$symbol'";
					}
				}
				// Mind closing the GAP.
				$place_holders[ $count ] .= ')';
			}
			$query .= " $query_columns ) VALUES ";
			$query .= implode( ', ', $place_holders );
			if ( $update ) {
				$update = " ON DUPLICATE KEY UPDATE `$primary_key`=VALUES( `$primary_key` ),";
				$cnt    = 0;
				foreach ( $row_arrays[0] as $key => $value ) {
					if ( in_array( $key, array( 'data_group', 'original', 'language_code', 'database_id', 'default_lang' ) ) ) {
						continue;
					}
					if ( $cnt == 0 ) {
						$update .= "`$key`=VALUES(`$key`)";
						$cnt     = 1;
					} else {
						$update .= ", `$key`=VALUES(`$key`)";
					}
				}
				$query .= $update;
			}
			$sql = $wpdb->prepare( $query, $values );
			if ( $wpdb->query( $sql ) ) {
				return true;
			} else {
				return false;
			}
		}
	public static function tpa_get_user_info() {
		global $wpdb;
		$server_info = [
		'server_software'        => sanitize_text_field($_SERVER['SERVER_SOFTWARE'] ?? 'N/A'),
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
