<?php
namespace LockedUsers;

/**
 *
 */
class Plugin {

	/**
	 * Called on the plugins_loaded action.  This is the bootstrap
	 */
	static function plugins_loaded () {

		self::add_actions();
		self::add_filters();

		//--!! Prototype, testing only below
		$current_user_id = get_current_user_id();
		
		// ToDo: needs to be multi-state (normal, provisional, disabled)
		if ( true == self::get_locked_status( $current_user_id ) ) {

			if ( !self::is_whitelisted( $current_user_id, $_SERVER[ 'REQUEST_URI' ] ) ) {
				
				//wp_die( 'Nope, nope.' );
			}

		}

	}

	/**
	 *
	 */
	static function add_actions () {

		// Nothing yet but still anticipated
	}

	/**
	 *
	 */
	static function add_filters () {

		add_filter( 'allow_password_reset', array( __CLASS__, 'allow_password_reset' ), 10, 2 );
		add_filter( 'authenticate', array( __CLASS__, 'authenticate' ), 30, 3 );
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

		if ( self::get_locked_status( $user_id ) ) {
			return false;
		}
		else {
			return $allow;
		}
	}

	/**
	 * WordPress authenticate filter
	 * 
	 * @param \WP_User|\WP_Error $user
	 * @param string $username
	 * @param string $password
	 *
	 * @return \WP_User|\WP_Error
	 */
	static function authenticate( $user, $username, $password ) {
		
		// ToDo: locked user status checking
		if ( is_a( $user, 'WP_User') ) {
			
			if ( self::get_locked_status( $user->ID ) ) {
				
				// ToDo: get rid of implicit dependency on Persistence
				$user = new \WP_Error( 'Locked Users', Persistence::get_authentication_message() );
			}
		}
		
		return $user;
	}

	/**
	 * @param int $user_id User ID
	 *
	 * @param string $url The URL to be tested against the consolidated whitelist for this user
	 *
	 * @return bool
	 */
	static function is_whitelisted( $user_id, $url ) {

		$user_whitelist = explode( "\r\n", Persistence::get_user_whitelist( $user_id ) );
		$global_whitelist = explode( "\r\n", Persistence::get_global_whitelist() );
		$whitelist = array_filter( array_merge( $global_whitelist, $user_whitelist ) );

		foreach ( $whitelist as $this_pattern ) {

			$this_pattern = sprintf( '`^%s$`', $this_pattern ); // Delimiting our regex with backticks here... potential issues, better solution?
			if ( preg_match( $this_pattern, $url ) ) {

				return true;
			}
		}

		return false;
	}

	// ToDo: these should transition to the multi-state get_user_status/set_user_status and be handled by the Persistence class 
	
	/**
	 * @param int $user_id User ID.
	 *
	 * @return Boolean
	 */
	static function get_locked_status ( $user_id ) {

		return (Bool) get_user_meta( $user_id, UserMeta::Locked, true );
	}

	/**
	 * @param int $user_id User ID.
	 * @param Boolean $locked
	 */
	static function set_locked_status ( $user_id, $locked ) {

		update_user_meta( $user_id, UserMeta::Locked, $locked );
	}
	
}
