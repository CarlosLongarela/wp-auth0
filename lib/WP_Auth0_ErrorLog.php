<?php
/**
 * Contains the WP_Auth0_LoginManager class.
 *
 * @package WP-Auth0
 */

/**
 * Class WP_Auth0_ErrorLog.
 * Handles error log CRUD actions and hooks.
 */
class WP_Auth0_ErrorLog {

	/**
	 * Option name used to store the error log.
	 */
	const OPTION_NAME = 'auth0_error_log';

	/**
	 * Add actions and filters for the error log settings section.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/admin_action__requestaction/
	 */
	public function init() {
		add_action( 'admin_action_wpauth0_clear_error_log', 'wp_auth0_errorlog_clear_error_log' );
	}

	/**
	 * Render the settings page.
	 *
	 * @see WP_Auth0_Settings_Section::init_menu()
	 */
	public function render_settings_page() {
		include WPA0_PLUGIN_DIR . 'templates/a0-error-log.php';
	}

	/**
	 * Get the error log.
	 *
	 * @return array
	 */
	public function get() {
		$log = get_option( self::OPTION_NAME );

		if ( empty( $log ) ) {
			$log = array();
		}

		return $log;
	}

	/**
	 * Update the error log with an array.
	 *
	 * @param array $log - Log array to update.
	 *
	 * @return bool
	 */
	public function update( array $log ) {
		return update_option( self::OPTION_NAME, $log );
	}

	/**
	 * Clear out the error log.
	 *
	 * @return bool
	 */
	public function clear() {
		return update_option( self::OPTION_NAME, array() );
	}

	/**
	 * Delete the error log option.
	 *
	 * @return bool
	 */
	public function delete() {
		return delete_option( self::OPTION_NAME );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @deprecated 3.6.0 - Not needed, handled in WP_Auth0_Admin::admin_enqueue()
	 */
	public function admin_enqueue() {
		// phpcs:ignore
		trigger_error( sprintf( __( 'Method %s is deprecated.', 'wp-auth0' ), __METHOD__ ), E_USER_DEPRECATED );
	}
}

/**
 * Function to call the method that clears out the error log.
 *
 * @hook admin_action_wpauth0_clear_error_log
 */
function wp_auth0_errorlog_clear_error_log() {

	if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'clear_error_log' ) ) {
		wp_die( __( 'Not allowed.', 'wp-auth0' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Not authorized.', 'wp-auth0' ) );
	}

	$error_log = new WP_Auth0_ErrorLog();
	$error_log->clear();

	wp_safe_redirect( admin_url( 'admin.php?page=wpa0-errors&cleared=1' ) );
	exit;
}
