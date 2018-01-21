<?php
/**
 * Formatting API
 *
 * @package WPGlobalOptions
 * @since 1.0.0
 */

if ( ! function_exists( 'sanitize_global_option' ) ) :

	/**
	 * Sanitizes a global option value based on the nature of the option.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $option The name of the option.
	 * @param string $value  The unsanitised value.
	 * @return string Sanitized value.
	 */
	function sanitize_global_option( $option, $value ) {
		global $wpdb;

		$original_value = $value;

		$error = new WP_Error();

		/**
		 * Filters the error object for whether an option value is valid.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Error $error  Empty error object to populate in case of errors.
		 * @param string   $value  The sanitized option value.
		 * @param string   $option The option name.
		 */
		$error = apply_filters( "validate_global_option_{$option}", $error, $value, $option );

		if ( ! empty( $error->errors ) ) {
			$value = get_global_option( $option );
			if ( function_exists( 'add_global_settings_error' ) ) {
				add_global_settings_error( $option, "invalid_{$option}", $error->get_error_message() );
			}
		}

		/**
		 * Filters an option value following sanitization.
		 *
		 * @since 1.0.0
		 *
		 * @param string $value          The sanitized option value.
		 * @param string $option         The option name.
		 * @param string $original_value The original value passed to the function.
		 */
		return apply_filters( "sanitize_global_option_{$option}", $value, $option, $original_value );
	}

endif;
