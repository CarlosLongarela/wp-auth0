<?php
/**
 * Contains the WP_Auth0_ErrorManager class.
 *
 * @package WP-Auth0
 */

/**
 * Class WP_Auth0_ErrorManager.
 * Handles creating a new error log entry.
 */
class WP_Auth0_ErrorManager {

	/**
	 * Create a row in the error log.
	 *
	 * @param string $section - Portion of the codebase that generated the error.
	 * @param mixed  $error - Error message string or discoverable error type.
	 *
	 * @return bool
	 */
	public static function insert_auth0_error( $section, $error ) {

		$new_entry = array(
			'section' => $section,
			'code'    => 'unknown_code',
			'message' => __( 'Unknown error message', 'wp-auth0' ),
		);

		if ( $error instanceof WP_Error ) {
			$new_entry['code']    = $error->get_error_code();
			$new_entry['message'] = $error->get_error_message();
		} elseif ( $error instanceof Exception ) {
			$new_entry['code']    = $error->getCode();
			$new_entry['message'] = $error->getMessage();
		} elseif ( is_array( $error ) && ! empty( $error['response'] ) ) {
			if ( ! empty( $error['response']['code'] ) ) {
				$new_entry['code'] = sanitize_text_field( $error['response']['code'] );
			}
			if ( ! empty( $error['response']['message'] ) ) {
				$new_entry['message'] = sanitize_text_field( $error['response']['message'] );
			}
		} else {
			$new_entry['message'] = is_object( $error ) || is_array( $error ) ? serialize( $error ) : $error;
		}

		$error_log = new WP_Auth0_ErrorLog();
		$log       = $error_log->get();

		// Prepare the last error log entry to compare with the new one.
		$last_entry = null;
		if ( ! empty( $log ) ) {
			// Get the last error logged.
			$last_entry = $log[0];

			// Remove date and count fields so it can be compared with the new error.
			$last_entry = array_diff_key( $last_entry, array_flip( array( 'date', 'count' ) ) );
		}

		if ( serialize( $last_entry ) === serialize( $new_entry ) ) {
			// New error and last error are the same so set the current time and increment the counter.
			$log[0]['date']  = time();
			$log[0]['count'] = isset( $log[0]['count'] ) ? intval( $log[0]['count'] ) + 1 : 2;
		} else {
			// New error is not a repeat to set required fields.
			$new_entry['date']  = time();
			$new_entry['count'] = 1;
			array_unshift( $log, $new_entry );
		}

		// Make sure we stay under 30 logged errors.
		if ( count( $log ) > 30 ) {
			array_pop( $log );
		}

		return $error_log->update( $log );
	}
}
