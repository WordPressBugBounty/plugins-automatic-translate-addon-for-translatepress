<?php
/**
 * Shared helper functions for the plugin.
 *
 * @package TPA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'tpa_get_installed_plugins' ) ) {
	/**
	 * Return installed plugins list (cached per request).
	 *
	 * @return array<string, array<string, mixed>>
	 */
	function tpa_get_installed_plugins() {
		static $cached_plugins = null;

		if ( null === $cached_plugins ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$cached_plugins = get_plugins();
		}

		return $cached_plugins;
	}
}

if ( ! function_exists( 'tpa_format_time_taken' ) ) {
	/**
	 * Format seconds into a human-readable time string.
	 *
	 * @param int $time_taken Time in seconds.
	 * @return string
	 */
	function tpa_format_time_taken( $time_taken ) {
		if ( 0 === $time_taken ) {
			return esc_html__( '0', 'automatic-translate-addon-for-translatepress' );
		}
		if ( $time_taken < 60 ) {
			return sprintf(
				/* translators: %d: Number of seconds */
				esc_html__( '%d sec', 'automatic-translate-addon-for-translatepress' ),
				$time_taken
			);
		}
		if ( $time_taken < 3600 ) {
			$min = floor( $time_taken / 60 );
			$sec = $time_taken % 60;
			return sprintf(
				/* translators: %1$d: Number of minutes, %2$d: Number of seconds */
				esc_html__( '%1$d min %2$d sec', 'automatic-translate-addon-for-translatepress' ),
				$min,
				$sec
			);
		}
		$hours = floor( $time_taken / 3600 );
		$min   = floor( ( $time_taken % 3600 ) / 60 );
		return sprintf(
			/* translators: %1$d: Number of hours, %2$d: Number of minutes */
			esc_html__( '%1$d hours %2$d min', 'automatic-translate-addon-for-translatepress' ),
			$hours,
			$min
		);
	}
}

if ( ! function_exists( 'tpa_detect_browser_type' ) ) {
	/**
	 * Detect the admin user's browser for built-in AI provider visibility.
	 *
	 * Built-in Translator AI is only available in desktop Chrome and Edge
	 * (not other Chromium forks). This helper drives which provider cards
	 * are shown in the dashboard.
	 *
	 * Why both HTTP_SEC_CH_UA and HTTP_USER_AGENT are checked:
	 * - Sec-CH-UA (Client Hints) is preferred when present: it reports a
	 *   structured brand list ("Google Chrome", "Microsoft Edge") and is
	 *   less ambiguous than the legacy User-Agent string.
	 * - User-Agent is the fallback when Client Hints are missing (older
	 *   browsers, privacy settings, or proxies that strip CH headers).
	 *
	 * Why Opera / Vivaldi are excluded from the UA Chrome match:
	 * - Those browsers include "Chrome/" in their User-Agent but do not
	 *   ship the Chrome/Edge Translator API. Matching Chrome alone would
	 *   incorrectly treat them as Chrome. Sec-CH-UA already distinguishes
	 *   brands, so the OPR/Opera/Vivaldi exclusion only applies on the UA path.
	 *
	 * Browser support docs:
	 * - https://developer.chrome.com/docs/ai/translator-api
	 * - https://developer.mozilla.org/en-US/docs/Web/API/Translator_and_Language_Detector_APIs
	 * - https://docs.coolplugins.net/docs/automatic-translate-addon-for-translatepress-pro/how-to-translate-your-website-content-automatically-via-chrome-ai/
	 *
	 * @return 'chrome'|'edge'|'other'
	 */
	function tpa_detect_browser_type() {
		$sec_ch_ua  = isset( $_SERVER['HTTP_SEC_CH_UA'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_CH_UA'] ) ) : '';
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		if ( ! empty( $sec_ch_ua ) ) {
			$is_edge   = (bool) preg_match( '/"Microsoft Edge"/i', $sec_ch_ua );
			$is_chrome = ! $is_edge && (bool) preg_match( '/"Google Chrome"/i', $sec_ch_ua );
		} else {
			$is_edge   = (bool) preg_match( '/Edg(e)?\//i', $user_agent );
			$is_chrome = ! $is_edge && (bool) preg_match( '/Chrome\/|Chromium\//i', $user_agent ) && ! (bool) preg_match( '/OPR\/|Opera\/|Vivaldi\//i', $user_agent );
		}

		if ( $is_edge ) {
			return 'edge';
		}
		if ( $is_chrome ) {
			return 'chrome';
		}

		return 'other';
	}
}

if ( ! function_exists( 'tpa_settings_tab_is_visible' ) ) {
	/**
	 * Whether the dashboard Settings tab should be shown.
	 *
	 * Visible when at least one provider with settings (Chrome/Edge built-in AI)
	 * is enabled, or when the usage-data feedback section is available.
	 *
	 * @return bool
	 */
	function tpa_settings_tab_is_visible() {
		if ( (bool) get_option( 'cpfm_opt_in_choice_cool_translations', false ) ) {
			return true;
		}

		$visible_providers = tpa_get_visible_builtin_ai_provider_keys();

		if (
			in_array( 'chrome', $visible_providers, true )
			&& '1' === (string) get_option( 'tpa_provider_chrome_enabled', '1' )
		) {
			return true;
		}

		if (
			in_array( 'edge', $visible_providers, true )
			&& '1' === (string) get_option( 'tpa_provider_edge_enabled', '1' )
		) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'tpa_get_visible_builtin_ai_provider_keys' ) ) {
	/**
	 * Built-in AI cards: Chrome-only on Chrome, Edge-only on Edge, both on every other browser.
	 *
	 * @return string[]
	 */
	function tpa_get_visible_builtin_ai_provider_keys() {
		$browser_type = tpa_detect_browser_type();

		if ( 'edge' === $browser_type ) {
			return array( 'edge' );
		}
		if ( 'chrome' === $browser_type ) {
			return array( 'chrome' );
		}

		return array( 'chrome', 'edge' );
	}
}
