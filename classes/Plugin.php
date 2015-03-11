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
		switch ( self::get_member_status( $current_user_id ) ) {
			
			case MemberStatus::Probationary:
				
				// Check the whitelists	
				if ( !self::is_whitelisted( $current_user_id, $_SERVER[ 'REQUEST_URI' ] ) ) {
				
					self::access_redirect();
				}
				break;
			
			case MemberStatus::Disabled:

				// They have no access
				self::access_redirect();
				break;

			case MemberStatus::Member:
				
				// Business as usual
				break;
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

		if ( MemberStatus::Member != self::get_member_status( $user_id ) ) {
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
		
		if ( is_a( $user, 'WP_User') ) {
			
			if ( MemberStatus::Member != self::get_member_status( $user->ID ) ) {
				
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
	 * @return Boolean
	 * 
	 * ToDo: Implicit dependency on Persistence
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

	/**
	 *
	 */
	static function access_redirect() {

		// ToDo: implicit dependency
		wp_redirect( Persistence::get_redirect_url() );
	}
	
	/**
	 * @param int $user_id User ID.
	 *
	 * @return int
	 */
	static function get_member_status ( $user_id ) {

		// ToDo: implicit dependency
		return Persistence::get_member_status( $user_id );
	}

	/**
	 * @param int $user_id User ID.
	 * @param int $status
	 */
	static function set_member_status ( $user_id, $status ) {

		// ToDo: implicit dependency
		Persistence::set_member_status( $user_id, $status );
	}

}
