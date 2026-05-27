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
