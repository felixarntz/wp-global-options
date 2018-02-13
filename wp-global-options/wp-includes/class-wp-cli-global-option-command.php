<?php
/**
 * WP-CLI: WP_CLI_Global_Option_Command class
 *
 * @package WPGlobalOptions
 * @since 1.0.0
 */

if ( ! class_exists( 'WP_CLI_Global_Option_Command' ) ) :

	/**
	 * Retrieves and sets global options, including plugin and WordPress settings.
	 *
	 * ## EXAMPLES
	 *
	 *     # Get global name.
	 *     $ wp global-option get global_name
	 *     My Networks
	 *
	 *     # Add global option.
	 *     $ wp global-option add my_option foobar
	 *     Success: Added 'my_option' global option.
	 *
	 *     # Update global option.
	 *     $ wp global-option update my_option '{"foo": "bar"}' --format=json
	 *     Success: Updated 'my_option' global option.
	 *
	 *     # Delete global option.
	 *     $ wp global-option delete my_option
	 *     Success: Deleted 'my_option' global option.
	 *
	 * @since 1.0.0
	 */
	class WP_CLI_Global_Option_Command extends WP_CLI_Command {

		/**
		 * Gets the value for a global option.
		 *
		 * ## OPTIONS
		 *
		 * <key>
		 * : Key for the global option.
		 *
		 * [--format=<format>]
		 * : Get value in a particular format.
		 * ---
		 * default: var_export
		 * options:
		 *   - var_export
		 *   - json
		 *   - yaml
		 * ---
		 *
		 * ## EXAMPLES
		 *
		 *     # Get global option.
		 *     $ wp global-option get global_name
		 *     My Networks
		 *
		 *     # Get global admin email.
		 *     $ wp global-option get admin_email
		 *     someone@example.com
		 *
		 *     # Get option in JSON format.
		 *     $ wp global-option get active_plugins --format=json
		 *     {"0":"wp-global-admin\/wp-global-admin.php","1":"wp-global-options\/wp-global-options.php","2":"wp-network-roles\/wp-network-roles.php"}
		 *
		 * @since 1.0.0
		 *
		 * @param array $args       Positional arguments.
		 * @param array $assoc_args Associative arguments.
		 */
		public function get( $args, $assoc_args ) {
			list( $key ) = $args;

			$value = get_global_option( $key, false );

			if ( false === $value ) {
				WP_CLI::error( "Could not get '$key' option. Does it exist?" );
			}

			WP_CLI::print_value( $value, $assoc_args );
		}

		/**
		 * Adds a new global option value.
		 *
		 * Errors if the global option already exists.
		 *
		 * ## OPTIONS
		 *
		 * <key>
		 * : The name of the global option to add.
		 *
		 * [<value>]
		 * : The value of the global option to add. If ommited, the value is read from STDIN.
		 *
		 * [--format=<format>]
		 * : The serialization format for the value.
		 * ---
		 * default: plaintext
		 * options:
		 *   - plaintext
		 *   - json
		 * ---
		 *
		 * [--autoload=<autoload>]
		 * : Should this global option be automatically loaded.
		 * ---
		 * options:
		 *   - 'yes'
		 *   - 'no'
		 * ---
		 *
		 * ## EXAMPLES
		 *
		 *     # Create a global option by reading a JSON file.
		 *     $ wp global-option add my_option --format=json < config.json
		 *     Success: Added 'my_option' global option.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args       Positional arguments.
		 * @param array $assoc_args Associative arguments.
		 */
		public function add( $args, $assoc_args ) {
			$key = $args[0];

			$value = WP_CLI::get_value_from_arg_or_stdin( $args, 1 );
			$value = WP_CLI::read_value( $value, $assoc_args );

			if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'autoload' ) === 'yes' ) {
				$autoload = 'yes';
			} else {
				$autoload = 'no';
			}

			if ( ! add_global_option( $key, $value, '', $autoload ) ) {
				WP_CLI::error( "Could not add global option '$key'. Does it already exist?" );
			} else {
				WP_CLI::success( "Added '$key' global option." );
			}
		}

		/**
		 * Lists global options and their values.
		 *
		 * ## OPTIONS
		 *
		 * [--search=<pattern>]
		 * : Use wildcards ( * and ? ) to match global option name.
		 *
		 * [--exclude=<pattern>]
		 * : Pattern to exclude. Use wildcards ( * and ? ) to match global option name.
		 *
		 * [--autoload=<value>]
		 * : Match only autoload global options when value is on, and only not-autoload global option when off.
		 *
		 * [--transients]
		 * : List only transients. Use `--no-transients` to ignore all transients.
		 *
		 * [--field=<field>]
		 * : Prints the value of a single field.
		 *
		 * [--fields=<fields>]
		 * : Limit the output to specific object fields.
		 *
		 * [--format=<format>]
		 * : The serialization format for the value. total_bytes displays the total size of matching global options in bytes.
		 * ---
		 * default: table
		 * options:
		 *   - table
		 *   - json
		 *   - csv
		 *   - count
		 *   - yaml
		 *   - total_bytes
		 * ---
		 *
		 * [--orderby=<fields>]
		 * : Set orderby which field.
		 * ---
		 * default: option_id
		 * options:
		 *  - option_id
		 *  - option_name
		 *  - option_value
		 * ---
		 *
		 * [--order=<order>]
		 * : Set ascending or descending order.
		 * ---
		 * default: asc
		 * options:
		 *  - asc
		 *  - desc
		 * ---
		 *
		 * ## AVAILABLE FIELDS
		 *
		 * This field will be displayed by default for each matching global option:
		 *
		 * * option_name
		 * * option_value
		 *
		 * These fields are optionally available:
		 *
		 * * autoload
		 * * size_bytes
		 *
		 * ## EXAMPLES
		 *
		 *     # Get the total size of all autoload global options.
		 *     $ wp global-option list --autoload=on --format=total_bytes
		 *     33198
		 *
		 *     # Find biggest global transients.
		 *     $ wp global-option list --search="*_transient_*" --fields=option_name,size_bytes | sort -n -k 2 | tail
		 *     option_name size_bytes
		 *     _site_transient_timeout_theme_roots 10
		 *     _site_transient_theme_roots 76
		 *     _site_transient_update_themes   181
		 *     _site_transient_update_core 808
		 *     _site_transient_update_plugins  6645
		 *
		 *     # List all global options beginning with "i2f_".
		 *     $ wp global-option list --search="i2f_*"
		 *     +-------------+--------------+
		 *     | option_name | option_value |
		 *     +-------------+--------------+
		 *     | i2f_version | 0.1.0        |
		 *     +-------------+--------------+
		 *
		 *     # Delete all global options beginning with "global_".
		 *     $ wp global-option list --search="global_*" --field=option_name | xargs -I % wp global-option delete %
		 *     Success: Deleted 'global_name' global option.
		 *     Success: Deleted 'global_administrators' global option.
		 *
		 * @subcommand list
		 *
		 * @since 1.0.0
		 *
		 * @global wpdb $wpdb WordPress database abstraction object.
		 *
		 * @param array $args       Positional arguments.
		 * @param array $assoc_args Associative arguments.
		 */
		public function list_( $args, $assoc_args ) {
			global $wpdb;

			$pattern        = '%';
			$exclude        = '';
			$fields         = array( 'option_name', 'option_value' );
			$size_query     = ',LENGTH(option_value) AS `size_bytes`';
			$autoload_query = '';

			if ( isset( $assoc_args['search'] ) ) {
				$pattern = self::esc_like( $assoc_args['search'] );
				// Substitute wildcards.
				$pattern = str_replace( '*', '%', $pattern );
				$pattern = str_replace( '?', '_', $pattern );
			}

			if ( isset( $assoc_args['exclude'] ) ) {
				$exclude = self::esc_like( $assoc_args['exclude'] );
				$exclude = str_replace( '*', '%', $exclude );
				$exclude = str_replace( '?', '_', $exclude );
			}

			if ( isset( $assoc_args['fields'] ) ) {
				$fields = explode( ',', $assoc_args['fields'] );
			}

			if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'format' ) === 'total_bytes' ) {
				$fields     = array( 'size_bytes' );
				$size_query = ',SUM(LENGTH(option_value)) AS `size_bytes`';
			}

			if ( isset( $assoc_args['autoload'] ) ) {
				if ( 'on' === $assoc_args['autoload'] ) {
					$autoload_query = " AND autoload='yes'";
				} elseif ( 'off' === $assoc_args['autoload'] ) {
					$autoload_query = " AND autoload='no'";
				} else {
					WP_CLI::error( "Value of '--autoload' should be on or off." );
				}
			}

			// By default we don't want to display transients.
			$show_transients = \WP_CLI\Utils\get_flag_value( $assoc_args, 'transients', false );

			$transients_query = '';
			if ( $show_transients ) {
				$transients_query = " AND option_name LIKE '\_transient\_%'";
			} else {
				$transients_query = " AND option_name NOT LIKE '\_transient\_%'";
			}

			$where = '';
			if ( $pattern ) {
				$where .= $wpdb->prepare( 'WHERE `option_name` LIKE %s', $pattern );
			}

			if ( $exclude ) {
				$where .= $wpdb->prepare( ' AND `option_name` NOT LIKE %s', $exclude );
			}
			$where .= $autoload_query . $transients_query;

			$results = $wpdb->get_results( 'SELECT `option_name`,`option_value`,`autoload`' . $size_query . " FROM `$wpdb->global_options` {$where}" );

			$orderby = \WP_CLI\Utils\get_flag_value( $assoc_args, 'orderby' );
			$order   = \WP_CLI\Utils\get_flag_value( $assoc_args, 'order' );

			// Sort result.
			if ( 'option_id' !== $orderby ) {
				usort( $results, function ( $a, $b ) use ( $orderby, $order ) {
					return 'asc' === $order ? $a->$orderby > $b->$orderby : $a->$orderby < $b->$orderby;
				});
			} elseif ( 'option_id' === $orderby && 'desc' === $order ) { // Sort by default descending.
				krsort( $results );
			}

			if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'format' ) === 'total_bytes' ) {
				WP_CLI::line( $results[0]->size_bytes );
			} else {
				$formatter = new \WP_CLI\Formatter( $assoc_args, $fields );
				$formatter->display_items( $results );
			}
		}

		/**
		 * Updates a global option value.
		 *
		 * ## OPTIONS
		 *
		 * <key>
		 * : The name of the global option to update.
		 *
		 * [<value>]
		 * : The new value. If ommited, the value is read from STDIN.
		 *
		 * [--autoload=<autoload>]
		 * : Requires WP 4.2. Should this global option be automatically loaded.
		 * ---
		 * options:
		 *   - 'yes'
		 *   - 'no'
		 * ---
		 *
		 * [--format=<format>]
		 * : The serialization format for the value.
		 * ---
		 * default: plaintext
		 * options:
		 *   - plaintext
		 *   - json
		 * ---
		 *
		 * ## EXAMPLES
		 *
		 *     # Update a global option by reading from a file.
		 *     $ wp global-option update my_option < value.txt
		 *     Success: Updated 'my_option' global option.
		 *
		 *     # Update global name.
		 *     $ wp global-option update global_name "My Environment"
		 *     Success: Updated 'global_name' global option.
		 *
		 *     # Update global admin email address.
		 *     $ wp global-option update admin_email someone@example.com
		 *     Success: Updated 'admin_email' global option.
		 *
		 * @alias set
		 *
		 * @since 1.0.0
		 *
		 * @param array $args       Positional arguments.
		 * @param array $assoc_args Associative arguments.
		 */
		public function update( $args, $assoc_args ) {
			$key = $args[0];

			$value = WP_CLI::get_value_from_arg_or_stdin( $args, 1 );
			$value = WP_CLI::read_value( $value, $assoc_args );

			$autoload = \WP_CLI\Utils\get_flag_value( $assoc_args, 'autoload' );
			if ( ! in_array( $autoload, array( 'yes', 'no' ), true ) ) {
				$autoload = null;
			}

			$value     = sanitize_global_option( $key, $value );
			$old_value = sanitize_global_option( $key, get_global_option( $key ) );

			if ( $value === $old_value && is_null( $autoload ) ) {
				WP_CLI::success( "Value passed for '$key' global option is unchanged." );
			} else {
				if ( update_global_option( $key, $value, $autoload ) ) {
					WP_CLI::success( "Updated '$key' global option." );
				} else {
					WP_CLI::error( "Could not update global option '$key'." );
				}
			}
		}

		/**
		 * Deletes a global option.
		 *
		 * ## OPTIONS
		 *
		 * <key>
		 * : Key for the global option.
		 *
		 * ## EXAMPLES
		 *
		 *     # Delete a global option.
		 *     $ wp global-option delete my_option
		 *     Success: Deleted 'my_option' global option.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args Positional arguments.
		 */
		public function delete( $args ) {
			list( $key ) = $args;

			if ( ! delete_global_option( $key ) ) {
				WP_CLI::error( "Could not delete '$key' global option. Does it exist?" );
			} else {
				WP_CLI::success( "Deleted '$key' global option." );
			}
		}

		/**
		 * Gets a nested value from a global option.
		 *
		 * ## OPTIONS
		 *
		 * <key>
		 * : The option name.
		 *
		 * <key-path>...
		 * : The name(s) of the keys within the value to locate the value to pluck.
		 *
		 * [--format=<format>]
		 * : The output format of the value.
		 * ---
		 * default: plaintext
		 * options:
		 *   - plaintext
		 *   - json
		 *   - yaml
		 * ---
		 *
		 * @since 1.0.0
		 *
		 * @param array $args       Positional arguments.
		 * @param array $assoc_args Associative arguments.
		 */
		public function pluck( $args, $assoc_args ) {
			list( $key ) = $args;

			$value = get_global_option( $key, false );

			if ( false === $value ) {
				WP_CLI::halt( 1 );
			}

			$key_path = array_map( function( $key ) {
				if ( is_numeric( $key ) && (string) intval( $key ) === $key ) {
					return (int) $key;
				}
				return $key;
			}, array_slice( $args, 1 ) );

			$traverser = new \WP_CLI\Entity\RecursiveDataStructureTraverser( $value );

			try {
				$value = $traverser->get( $key_path );
			} catch ( \Exception $e ) {
				die( 1 );
			}

			WP_CLI::print_value( $value, $assoc_args );
		}

		/**
		 * Updates a nested value in a global option.
		 *
		 * ## OPTIONS
		 *
		 * <action>
		 * : Patch action to perform.
		 * ---
		 * options:
		 *   - insert
		 *   - update
		 *   - delete
		 * ---
		 *
		 * <key>
		 * : The option name.
		 *
		 * <key-path>...
		 * : The name(s) of the keys within the value to locate the value to patch.
		 *
		 * [<value>]
		 * : The new value. If omitted, the value is read from STDIN.
		 *
		 * [--format=<format>]
		 * : The serialization format for the value.
		 * ---
		 * default: plaintext
		 * options:
		 *   - plaintext
		 *   - json
		 * ---
		 *
		 * @since 1.0.0
		 *
		 * @param array $args       Positional arguments.
		 * @param array $assoc_args Associative arguments.
		 */
		public function patch( $args, $assoc_args ) {
			list( $action, $key ) = $args;

			$key_path = array_map( function( $key ) {
				if ( is_numeric( $key ) && (string) intval( $key ) === $key ) {
					return (int) $key;
				}
				return $key;
			}, array_slice( $args, 2 ) );

			if ( 'delete' === $action ) {
				$patch_value = null;
			} elseif ( \WP_CLI\Entity\Utils::has_stdin() ) {
				$stdin_value = WP_CLI::get_value_from_arg_or_stdin( $args, -1 );
				$patch_value = WP_CLI::read_value( trim( $stdin_value ), $assoc_args );
			} else {
				// Take the patch value as the last positional argument. Mutates $key_path to be 1 element shorter!
				$patch_value = WP_CLI::read_value( array_pop( $key_path ), $assoc_args );
			}

			/* Need to make a copy of $current_value here as it is modified by reference */
			$old_value     = sanitize_global_option( $key, get_global_option( $key ) );
			$current_value = $old_value;

			$traverser = new \WP_CLI\Entity\RecursiveDataStructureTraverser( $current_value );

			try {
				$traverser->$action( $key_path, $patch_value );
			} catch ( \Exception $e ) {
				WP_CLI::error( $e->getMessage() );
			}

			$patched_value = sanitize_global_option( $key, $traverser->value() );

			if ( $patched_value === $old_value ) {
				WP_CLI::success( "Value passed for '$key' global option is unchanged." );
			} else {
				if ( update_global_option( $key, $patched_value ) ) {
					WP_CLI::success( "Updated '$key' global option." );
				} else {
					WP_CLI::error( "Could not update global option '$key'." );
				}
			}
		}

		/**
		 * Escapes LIKE special characters % and _ before preparing SQL.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @global wpdb $wpdb WordPress database abstraction object.
		 *
		 * @param string $old Unescaped LIKE value.
		 * @return string Escaped LIKE value.
		 */
		private static function esc_like( $old ) {
			global $wpdb;

			// Remove notices in 4.0 and support backwards compatibility.
			if ( method_exists( $wpdb, 'esc_like' ) ) { // 4.0 and newer.
				$old = $wpdb->esc_like( $old );
			} else {  // 3.9 and older.
				$old = like_escape( esc_sql( $old ) );
			}

			return $old;
		}
	}

endif;
