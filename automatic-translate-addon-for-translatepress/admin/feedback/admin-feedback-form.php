<?php
namespace TPA\feedback;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class for feedback from user before deactivate plugin.
 */
class tpa_feedback {
	/** Class for feedback.
	 * Get file path.
	 *
	 * @var plugin_url
	 */
	private $plugin_url = __FILE__;
	/**
	 *
	 * Define plugin version.
	 *
	 * @var plugin_version
	 */
	private $plugin_version = TPA_VERSION;
	/**
	 *
	 * Define plugin slug.
	 *
	 * @var plugin_slug
	 */
	private $plugin_slug = 'automatic-translate-addon-for-translatepress';
	/**
	 *
	 * Define plugin name.
	 *
	 * @var plugin_name
	 */
	private $plugin_name = 'AI Translation For TranslatePress';
	/**
	 *
	 * Define plugin prefix.
	 *
	 * @var plugin_prefix
	 */
	private $plugin_prefix = 'TPA';
	/**
	 *
	 * Define feedback url for redirection.
	 *
	 * @var feedback_url
	 */
	private $feedback_url = TPA_FEEDBACK_API.'wp-json/coolplugins-feedback/v1/feedback';
	/**
	 * Use this constructor to fire all actions and filters.
	 */
	public function __construct() {
		$this->plugin_url = plugin_dir_url( $this->plugin_url );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_feedback_scripts' ) );
		add_action( 'admin_head', array( $this, 'show_deactivate_feedback_popup' ) );
		add_action( 'wp_ajax_' . $this->plugin_prefix . '_submit_deactivation_response', array( $this, 'submit_deactivation_response' ) );
	}

	/**
	 * Enqueue all scripts and styles to required page only.
	 */
	public function enqueue_feedback_scripts() {
		$screen = get_current_screen();
		if ( isset( $screen ) && $screen->id === 'plugins' ) {
			wp_enqueue_script( __NAMESPACE__ . 'feedback-script', $this->plugin_url . '/js/admin-feedback.js', array( 'jquery' ), $this->plugin_version, true );
			wp_enqueue_style( 'cool-plugins-feedback-style', $this->plugin_url . '/css/admin-feedback.css', null, $this->plugin_version, 'all');
		}
	}

	/**
	 * HTML for creating feedback popup form.
	 */
	public function show_deactivate_feedback_popup() {
		$screen = get_current_screen();
		if ( ! isset( $screen ) || $screen->id !== 'plugins' ) {
			return;
		}
		$deactivate_reasons = array(
			'didnt_work_as_expected'         => array(
				'title'             => __( 'The plugin didn\'t work as expected', 'automatic-translate-addon-for-translatepress' ),
				'input_placeholder' => 'What did you expect?',
			),
			'found_a_better_plugin'          => array(
				'title'             => __( 'I found a better plugin', 'automatic-translate-addon-for-translatepress' ),
				'input_placeholder' => __( 'Please share which plugin', 'automatic-translate-addon-for-translatepress' ),
			),
			'couldnt_get_the_plugin_to_work' => array(
				'title'             => __( 'The plugin is not working', 'automatic-translate-addon-for-translatepress' ),
				'input_placeholder' => 'Please share your issue. So we can fix that for other users.',
			),
			'temporary_deactivation'         => array(
				'title'             => __( 'It\'s a temporary deactivation', 'automatic-translate-addon-for-translatepress' ),
				'input_placeholder' => '',
			),
			'other'                          => array(
				'title'             => __( 'Other', 'automatic-translate-addon-for-translatepress' ),
				'input_placeholder' => __( 'Please share the reason', 'automatic-translate-addon-for-translatepress' ),
			),
		);

		?>
		<div id="cool-plugins-deactivate-feedback-dialog-wrapper" class="hide-feedback-popup" data-slug="<?php echo esc_attr( $this->plugin_slug ); ?>">
						
			<div class="cool-plugins-deactivation-response">
			<div id="cool-plugins-deactivate-feedback-dialog-header">
				<span id="cool-plugins-feedback-form-title"><?php echo esc_html__( 'Quick Feedback', 'automatic-translate-addon-for-translatepress' ); ?></span>
			</div>
			<div id="cool-plugins-loader-wrapper">
				<div class="cool-plugins-loader-container">
					<img class="cool-plugins-preloader" src="<?php echo esc_url( $this->plugin_url . 'images/cool-plugins-preloader.gif' ); ?>">
				</div>
			</div>
			<div id="cool-plugins-form-wrapper" class="cool-plugins-form-wrapper-cls">
			<form id="cool-plugins-deactivate-feedback-dialog-form" method="post" action="#" novalidate aria-labelledby="cool-plugins-feedback-form-title">
				<?php
				wp_nonce_field( '_cool-plugins_deactivate_feedback_nonce' );
				?>
				<input type="hidden" name="action" value="cool-plugins_deactivate_feedback" />
				<div id="cool-plugins-deactivate-feedback-dialog-form-caption"><?php echo esc_html__( 'If you have a moment, please share why you are deactivating this plugin.', 'automatic-translate-addon-for-translatepress' ); ?></div>
				<div id="cool-plugins-deactivate-feedback-dialog-form-body">
					<?php foreach ( $deactivate_reasons as $reason_key => $reason ) : ?>
						<div class="cool-plugins-deactivate-feedback-dialog-input-wrapper">
							<input id="cool-plugins-deactivate-feedback-<?php echo esc_attr( $reason_key ); ?>" class="cool-plugins-deactivate-feedback-dialog-input" type="radio" name="reason_key" value="<?php echo esc_attr( $reason_key ); ?>" />
							<label for="cool-plugins-deactivate-feedback-<?php echo esc_attr( $reason_key ); ?>" class="cool-plugins-deactivate-feedback-dialog-label"><?php echo esc_html( $reason['title'] ); ?></label>
							<?php if ( ! empty( $reason['input_placeholder'] ) ) : ?>
								<textarea class="cool-plugins-feedback-text" type="textarea" name="reason_<?php echo esc_attr( $reason_key ); ?>" placeholder="<?php echo esc_attr( $reason['input_placeholder'] ); ?>"></textarea>
							<?php endif; ?>
							<?php if ( ! empty( $reason['alert'] ) ) : ?>
								<div class="cool-plugins-feedback-text"><?php echo esc_html( $reason['alert'] ); ?></div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
					<input class="cool-plugins-GDPR-data-notice" id="cool-plugins-GDPR-data-notice-<?php echo esc_attr( $this->plugin_prefix ); ?>" type="checkbox">
					<label for="cool-plugins-GDPR-data-notice-<?php echo esc_attr( $this->plugin_prefix ); ?>"><?php echo esc_html__( 'I agree to share anonymous usage data and basic site details (such as server, PHP, and WordPress versions) to support AI Translation Addon for TranslatePress improvement efforts. Additionally, I allow Cool Plugins to store all information provided through this form and to respond to my inquiry.', 'automatic-translate-addon-for-translatepress' ); ?></label>
				</div>
				<div class="cool-plugin-popup-button-wrapper">
					<button type="submit" class="cool-plugins-button button-deactivate" id="cool-plugin-submitNdeactivate"><?php esc_html_e( 'Submit and Deactivate', 'automatic-translate-addon-for-translatepress' ); ?></button>
					<button type="button" class="cool-plugins-button" id="cool-plugin-skipNdeactivate"><?php esc_html_e( 'Skip and Deactivate', 'automatic-translate-addon-for-translatepress' ); ?></button>
				</div>
			</form>
			</div>
		   </div>
		</div>
		<?php
	}



	/**
	 * Function to submit feedback rom user.
	 */

	public function submit_deactivation_response() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), '_cool-plugins_deactivate_feedback_nonce' ) ) {
			wp_send_json_error();
			return;
		}

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error();
			return;
		}

		$reason             = isset( $_POST['reason'] ) ? sanitize_text_field( wp_unslash( $_POST['reason'] ) ) : '';
		$deactivate_reasons = array(
			'didnt_work_as_expected'         => array(
				'title'             => __( 'The plugin didn\'t work as expected', 'automatic-translate-addon-for-translatepress' ),
				'input_placeholder' => 'What did you expect?',
			),
			'found_a_better_plugin'          => array(
				'title'             => __( 'I found a better plugin', 'automatic-translate-addon-for-translatepress' ),
				'input_placeholder' => __( 'Please share which plugin', 'automatic-translate-addon-for-translatepress' ),
			),
			'couldnt_get_the_plugin_to_work' => array(
				'title'             => __( 'The plugin is not working', 'automatic-translate-addon-for-translatepress' ),
				'input_placeholder' => 'Please share your issue. So we can fix that for other users.',
			),
			'temporary_deactivation'         => array(
				'title'             => __( 'It\'s a temporary deactivation', 'automatic-translate-addon-for-translatepress' ),
				'input_placeholder' => '',
			),
			'other'                          => array(
				'title'             => __( 'Other', 'automatic-translate-addon-for-translatepress' ),
				'input_placeholder' => __( 'Please share the reason', 'automatic-translate-addon-for-translatepress' ),
			),
		);

		$deativation_reason = array_key_exists( $reason, $deactivate_reasons ) ? $reason : 'other';

		$plugin_initial = sanitize_text_field( get_option( 'tpa_initial_save_version' ) );
		$message          = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : '';
		$sanitized_message = $message === '' ? 'N/A' : $message;
		$admin_email   = sanitize_email( get_option( 'admin_email' ) );
		$info          = \TranslatePressAddon::tpa_get_user_info();
		$server_info   = $info['server_info'];
		$extra_details = $info['extra_details'];
		$site_url      = esc_url( get_site_url() );
		$install_date  = sanitize_text_field( get_option( 'tpa-install-date' ) );
		$unique_key    = '17'; // Ensure this key is unique per plugin to prevent collisions when site URL and install date are the same across plugins.
		$site_id       = $site_url . '-' . $install_date . '-' . $unique_key;
		$response          = wp_remote_post(
			$this->feedback_url,
			array(
				'timeout'   => 30,
				'sslverify' => true,
				'body'      => array(
					'site_id'         => md5( $site_id ),
					'server_info'     => wp_json_encode( $server_info ),
					'extra_details'   => wp_json_encode( $extra_details ),
					'plugin_version'  => $this->plugin_version,
					'plugin_name'     => $this->plugin_name,
					'plugin_initial'  => isset( $plugin_initial ) ? sanitize_text_field( $plugin_initial ) : 'N/A',
					'reason'          => $deativation_reason,
					'review'          => $sanitized_message,
					'email'           => $admin_email,
					'domain'          => $site_url,
				),
			)
		);

		wp_send_json_success( array( 'message' => esc_html__( 'Feedback submitted.', 'automatic-translate-addon-for-translatepress' ) ) );
	}
}
new tpa_feedback();
