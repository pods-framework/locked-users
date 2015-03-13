<?php
namespace LockedUsers;

/**
 *
 */
class Plugin {

	/**
	 * Called on the plugins_loaded action.  This is the bootstrap
	 */
	static function init () {

		self::add_actions();
		self::add_filters();

		// ToDo: This is here for prototyping only
		if ( ! self::check_access( $_SERVER['REQUEST_URI'] ) ) {
			wp_die( "access denied" );
		}

	}

	/**
	 *
	 */
	static function add_actions () {

		add_action( 'locked_users_user_status_change', array( __CLASS__, 'user_status_change'),  10, 3 );

	}

	/**
	 *
	 */
	static function add_filters () {

		add_filter( 'allow_password_reset', array( __CLASS__, 'allow_password_reset' ), 10, 2 );
		add_filter( 'authenticate', array( __CLASS__, 'authenticate' ), 30 );

	}

	/**
	 * WordPress allow_password_reset filter
	 *
	 * @param Boolean $allow
	 * @param int $user_id User ID.
	 *
	 * @return Boolean
	 */
	static function allow_password_reset ( $allow, $user_id ) {

		if ( UserStatuses::NORMAL != self::get_user_status( $user_id ) ) {
			$allow = false;
		}

		return $allow;

	}

	/**
	 * WordPress authenticate filter
	 *
	 * @param \WP_User|\WP_Error $user
	 *
	 * @return \WP_User|\WP_Error
	 */
	static function authenticate( $user ) {

		if ( is_a( $user, 'WP_User' ) ) {

			if ( UserStatuses::NORMAL != self::get_user_status( $user->ID ) ) {

				// ToDo: get rid of implicit dependency on Persistence
				$user = new \WP_Error( 'Locked Users', Persistence::get_authentication_message() );
			}
		}

		return $user;

	}

	/**
	 * Custom action hook, triggered by Persistence::set_user_status()
	 *
	 * @param int $user_id
	 * @param string $old_status
	 * @param string $new_status
	 */
	static function user_status_change( $user_id, $old_status, $new_status ) {

		// Make sure the user has an access hash assigned whenever they're set to LOCKED
		if ( UserStatuses::LOCKED == $new_status ) {

			// No hash assigned yet?
			if ( ! Persistence::get_user_access_hash( $user_id ) ) {

				// Assign a new hash
				Persistence::set_user_access_hash( $user_id, self::generate_user_access_hash() );
			}
		}
	}

	/**
	 * @param string $url
	 * @param int $user_id User ID
	 *
	 * @return bool
	 *
	 * ToDo: Still needs clean-up and consistency (returning a boolean or doing the redirects, pick one)
	 */
	static function check_access( $url, $user_id = null ) {

		// Do we have the required args for a special access page?
		if ( ! empty( $_GET[ QueryArgs::ACCESS_HASH ] ) && ! empty( $_GET[ QueryArgs::USER_ID  ] ) ) {

			// Log user out if they are already logged in
			if ( is_user_logged_in() ) {
				wp_logout();
			}

			// ToDo: Evaluate sanitizing needs
			$access_hash = $_GET[ QueryArgs::ACCESS_HASH ];
			$user_id = (int) $_GET[ QueryArgs::USER_ID ];

			// Verify that the supplied user ID and access hash match
			$matching_user = get_users( array(
				'include'    => array( $user_id ),
				'meta_key'   => UserMeta::ACCESS_HASH,
				'meta_value' => $access_hash
			) );

			// We have the right query args but can't find the associated user
			if ( empty( $matching_user ) ) {
				return false; // Deny access
			}

			$matching_user = array_shift( $matching_user );

			// Log the user in
			wp_set_current_user( $user_id, $matching_user->user_login );
			wp_set_auth_cookie( $user_id );

			do_action( 'wp_login', $matching_user->user_login );

			// Strip the query args so we can test the url against the whitelists
			$url = remove_query_arg( array( QueryArgs::USER_ID, QueryArgs::ACCESS_HASH ), $url );
		}

		// User ID omitted and we didn't find them via the access hash?
		if ( null === $user_id ) {

			// Not a page with access hash and user id, is this a logged-in user?
			if ( ! is_user_logged_in() ) {

				// Not a logged in user nor an access hash, we should not deny access
				return true;
			}

			// Someone is logged in, get their ID
			$user_id = get_current_user_id();
		}

		// If we make it here, we should have a valid user ID
		switch ( self::get_user_status( $user_id ) ) {

			case UserStatuses::LOCKED:

				// Check the whitelists
				if ( ! self::is_whitelisted( $url, $user_id ) ) {

					// Avoid redirect loop
					if ( Persistence::get_locked_redirect_url() != $url ) {

						$redirect_url = Persistence::get_locked_redirect_url();
						wp_redirect( $redirect_url );
						die();
					}
				}

				break;

			case UserStatuses::DISABLED:

				// They have no access but avoid a redirect loop
				// ToDo: since we don't check any whitelist we still have a problem with disabled users and the user switching plugin, not a prob for locked users as you can whitelist the url
				if ( Persistence::get_disabled_redirect_url() != $url ) {

					$redirect_url = Persistence::get_disabled_redirect_url();
					wp_redirect( $redirect_url );
					die();
				}

				break;

			case UserStatuses::NORMAL:

				// Business as usual
				break;
		}

		return true;

	}
	/**
	 * @param string $url The URL to be tested against the consolidated whitelist for this user
	 *
	 * @param int $user_id User ID
	 *
	 * @return Boolean
	 *
	 * ToDo: Implicit dependency on Persistence
	 */
	static function is_whitelisted( $url, $user_id ) {

		$user_whitelist = explode( "\r\n", Persistence::get_user_whitelist( $user_id ) );
		$global_whitelist = explode( "\r\n", Persistence::get_global_whitelist() );
		$whitelist = array_filter( array_merge( $global_whitelist, $user_whitelist ) );

		foreach ( $whitelist as $this_pattern ) {

			if ( preg_match( '/^' . preg_quote( $this_pattern, '/' ) . '$/', $url ) ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * @param $url
	 * @param int|null $user_id User ID
	 *
	 * @return string
	 */
	static function get_access_hash_url( $url, $user_id = null ) {

		// Lookup the current user if no user was specified
		if ( null === $user_id ) {

			$user_id = get_current_user_id();
		}

		if ( false === get_userdata( $user_id ) ) {

			// ToDo: what we do about invalid user ID or users that aren't logged in
		}

		// Get/generate the hash
		$access_hash = Persistence::get_user_access_hash( $user_id );

		if ( empty( $access_hash ) ) {

			// ToDo: what to do when the specified user has no saved hash
		}

		// Check to see if the URL is already whitelisted for this user
		$already_whitelisted = false;
		$whitelist_array = array();
		$whitelist_string = Persistence::get_user_whitelist( $user_id );

		// If we call explode with the empty string we'll have a single element array with the empty string
		// which fouls up the subsequent implode(), so just keep the array empty unless we have actual content
		if ( ! empty( $whitelist_string ) ) {

			$whitelist_array = explode( "\r\n",  $whitelist_string );
			foreach ( $whitelist_array as $this_pattern ) {

				// Set the flag and early exit if we find the target
				if ( $this_pattern == $url ) {
					$already_whitelisted = true;
					break;
				}
			}
		}

		// Add the url to the user's whitelist if it wasn't already
		if ( ! $already_whitelisted ) {

			array_push( $whitelist_array, $url );
			Persistence::set_user_whitelist( $user_id, implode( "\r\n", $whitelist_array ) );
		}

		return add_query_arg( array( QueryArgs::ACCESS_HASH => $access_hash, QueryArgs::USER_ID => $user_id ), $url );

	}

	/**
	 * @return string
	 */
	static function generate_user_access_hash() {

		return wp_generate_password( 20, false, false );

	}

	/**
	 * @param int $user_id User ID.
	 *
	 * @return int
	 */
	static function get_user_status ( $user_id ) {

		// ToDo: implicit dependency
		return Persistence::get_user_status( $user_id );

	}

	/**
	 * @param int $user_id User ID.
	 * @param int $status
	 */
	static function set_user_status ( $user_id, $status ) {

		// ToDo: implicit dependency
		Persistence::set_user_status( $user_id, $status );

	}

}