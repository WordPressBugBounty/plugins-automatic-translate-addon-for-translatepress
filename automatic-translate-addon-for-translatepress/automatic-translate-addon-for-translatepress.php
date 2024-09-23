<?php
/**
 * Plugin Name: Automatic Translate Addon For TranslatePress
 * Description: Auto language translator add-on for TranslatePress official plugin to translate website into any language via fully automatic machine translations via Yandex Translate Widget.
 * Author: Cool Plugins
 * Author URI: https://coolplugins.net/
 * Plugin URI:
 * Version: 1.1.1
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
define( 'TPA_VERSION', '1.1.1' );
define( 'TPA_FILE', __FILE__ );
define( 'TPA_PATH', plugin_dir_path( TPA_FILE ) );
define( 'TPA_URL', plugin_dir_url( TPA_FILE ) );
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
			add_filter( 'trp_string_groups', array( $this, 'tpa_string_groups' ) );
			add_action( 'plugins_loaded', array( $this, 'tpa_check_required_plugin' ) );
			if ( ! is_admin() ) {
				add_action( 'trp_translation_manager_footer', array( $this, 'tpa_register_assets' ) );
			}
			add_action( 'wp_ajax_tpa_get_strings', array( $this, 'tpa_getstrings' ) );
			add_action( 'wp_ajax_tpa_save_translations', array( $this, 'tpa_save_translations' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'tpa_settings_page_link' ) );

		}

		/**
		 * Create 'settings' link in plugins page.
		 *
		 * @param array $links use for pro plugin.
		 */
		public function tpa_settings_page_link( $links ) {
			$links[] = '<a style="font-weight:bold" href="' . esc_url( get_admin_url( null, 'options-general.php?page=translatepress-tpap-register' ) ) . '">Pro</a>';
			return $links;
		}

		/**
		 * Set settings on plugin activation.
		 */
		public function tpa_activate() {
			update_option( 'tpa-v', TPA_VERSION );
			update_option( 'tpa-type', 'FREE' );
			update_option( 'tpa-installDate', gmdate( 'Y-m-d h:i:s' ) );
			update_option( 'tpa-ratingDiv', 'no' );
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
		 * Check if required "TranslatePress - Multilingual" plugin is activeF
		 * also register the plugin text domain
		 */
		public function tpa_check_required_plugin() {
			if ( ! function_exists( 'trp_enable_translatepress' ) ) {
				add_action( 'admin_notices', array( $this, 'tpa_plugin_required_admin_notice' ) );
			}

			if ( is_admin() ) {
				include_once TPA_PATH . 'admin/tpap-register/tpap-admin-menu.php';
				/** Feedback form after deactivation */
				require_once __DIR__ . '/admin/feedback/admin-feedback-form.php';
				/*** Plugin review notice file */
				require_once TPA_PATH . 'admin/tpa-feedback-notice.php';
				new TPAFeedbackNotice();
			}
			load_plugin_textdomain( 'TPA', false, basename( dirname( TPA_FILE ) ) . '/languages/' );
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
			$extra_data['ajax_url']       = admin_url( 'admin-ajax.php' );
			$extra_data['nonce']          = wp_create_nonce( 'auto-translate-press-nonces' );
			$extra_data['plugin_url']     = plugins_url();
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
		/**
		* TranslatePressAddon Class Close
		*/
	}
}
new TranslatePressAddon();
