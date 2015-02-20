<?php
namespace LockedUsers;

/**
 *
 */
class Plugin {
	
	// ToDo: global whitelist option
	
	/**
	 * Called on the plugins_loaded action
	 */
	static function plugins_loaded () {
		
		//--!! Prototype, testing
		$current_user_id = get_current_user_id();
		if ( true == self::get_locked_status( $current_user_id ) ) {
		
			$subject = $_SERVER[ 'REQUEST_URI' ];
			$whitelist = self::get_whitelist( $current_user_id );
			$pattern = sprintf( '`^%s$`', $whitelist );
			if ( !preg_match( $pattern, $subject ) ) {

				// ToDo: redirect target option for disallowed viewing
				wp_die( 'Nope nope.');	
			} 			
		}
		
		load_plugin_textdomain( 'locked-users', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		add_filter( 'allow_password_reset', __NAMESPACE__ . '\\allow_password_reset', 10, 2 );

		/* User meta updates */
		add_action( 'personal_options', array( __CLASS__,'personal_options' ) );
		add_action( 'personal_options_update', array ( __CLASS__, 'save_user_meta' ) );
		add_action( 'edit_user_profile_update', array ( __CLASS__, 'save_user_meta' ) );
	}

	/**
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
	 * Called by the personal_options action
	 *
	 * @param  object $user The user object for the user currently being edited.
	 */
	static function personal_options ( $user ) {
		?>
		<tr class="locked-users">
			<th scope="row"><?php _e( 'Locked Users', 'locked-users' ); ?></th>
			<td>
				<fieldset>
					<label for="locked-user">
						<input name="<?php echo MetaKeys::Locked; ?>" type="checkbox" id="locked-user" value="1" <?php checked( self::get_locked_status( $user->ID ) ); ?> />
						<?php _e( 'Lock this user', 'locked-users' ); ?>
					</label>
					<br>
					<label for="locked-user-whitelist">
						<textarea name="<?php echo MetaKeys::Whitelist; ?>" rows="8"><?php echo esc_textarea( self::get_whitelist( $user->ID ) ); ?></textarea>
						<br>
						<span class="description"><?php _e( 'List of allowed URLs', 'locked-users' ); ?></span>
					</label>
				</fieldset>
			</td>
		</tr>
	<?php }

	/**
	 * @param int $user_id User ID.
	 */
	static function save_user_meta ( $user_id ) {

		$locked = isset( $_POST[ MetaKeys::Locked ] ) ? 1 : 0;
		$whitelist = ( isset( $_POST[ MetaKeys::Whitelist ] ) ) ? $_POST[ MetaKeys::Whitelist ] : '';

		self::set_locked_status( $user_id, $locked );
		self::set_whitelist( $user_id, $whitelist );

	}

	/**
	 * @param int $user_id User ID.
	 *
	 * @return Boolean
	 */
	static function get_locked_status( $user_id ) {

		return (Bool) get_user_meta( $user_id, MetaKeys::Locked, true );
	}


	/**
	 * @param int $user_id User ID.
	 * @param Boolean $locked
	 */
	static function set_locked_status( $user_id, $locked ) {

		update_user_meta( $user_id, MetaKeys::Locked, $locked );
	}

	/**
	 * @param int $user_id User ID.
	 *
	 * @return mixed
	 */
	static function get_whitelist( $user_id ) {

		return get_user_meta( $user_id, MetaKeys::Whitelist, true );
	}

	/**
	 * @param int $user_id User ID.
	 * @param string $whitelist
	 */
	static function set_whitelist( $user_id, $whitelist ) {

		update_user_meta( $user_id, MetaKeys::Whitelist, $whitelist );
	}
}
