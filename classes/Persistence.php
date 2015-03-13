<?php
namespace LockedUsers;

/**
 * Currently manages both the settings and the usermeta
 */
class Persistence implements PersistenceInterface {

	/**
	 *
	 */
	static function init () {

		self::add_actions();

	}

	/**
	 *
	 */
	static function add_actions () {

		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );

		/* User meta updates */
		add_action( 'personal_options', array( __CLASS__, 'personal_options' ) );
		add_action( 'personal_options_update', array( __CLASS__, 'save_user_meta' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'save_user_meta' ) );

	}

	/**
	 * Called on the admin_init action
	 */
	static function admin_init () {

		add_settings_section(
			SettingsMeta::OPTION_SECTION_GENERAL,     // ID
			SettingsMeta::SECTION_GENERAL_TITLE,      // title
			array( __CLASS__, 'section_general' ),  // callback
			SettingsMeta::OPTION_MENU_PAGE            // page
		);

		// Global whitelist
		add_settings_field(
			SettingsFields::GLOBAL_WHITELIST_ID,             // ID
			SettingsFields::GLOBAL_WHITELIST_TITLE,          // Title
			array( __CLASS__, 'field_global_whitelist' ),  // callback
			SettingsMeta::OPTION_MENU_PAGE,                  // page
			SettingsMeta::OPTION_SECTION_GENERAL             // section
		);

		// Authentication message on unauthorized login attempt
		add_settings_field(
			SettingsFields::AUTHENTICATION_MESSAGE_ID,             // ID
			SettingsFields::AUTHENTICATION_MESSAGE_TITLE,          // Title
			array( __CLASS__, 'field_authentication_message' ),  // callback
			SettingsMeta::OPTION_MENU_PAGE,                        // page
			SettingsMeta::OPTION_SECTION_GENERAL                   // section
		);

		// URL to redirect to on unauthorized page access attempts for locked users
		add_settings_field(
			SettingsFields::LOCKED_REDIRECT_URL_ID,              // ID
			SettingsFields::LOCKED_REDIRECT_URL_TITLE,           // Title
			array( __CLASS__, 'field_locked_redirect_url' ),  // callback
			SettingsMeta::OPTION_MENU_PAGE,                     // page
			SettingsMeta::OPTION_SECTION_GENERAL                // section
		);

		// URL to redirect to on unauthorized page access attempts for disabled users
		add_settings_field(
			SettingsFields::DISABLED_REDIRECT_URL_ID,              // ID
			SettingsFields::DISABLED_REDIRECT_URL_TITLE,           // Title
			array( __CLASS__, 'field_disabled_redirect_url' ),  // callback
			SettingsMeta::OPTION_MENU_PAGE,                       // page
			SettingsMeta::OPTION_SECTION_GENERAL                  // section
		);

		register_setting( SettingsMeta::OPTION_GROUP, SettingsMeta::OPTION_NAME );

	}

	/**
	 * Called on the admin_menu action
	 */
	static function admin_menu () {

		add_options_page(
			'Locked User Settings',              // page title
			'Locked User Settings',              // menu title
			'manage_options',                    // cap
			SettingsMeta::OPTION_MENU_PAGE,        // menu slug
			array( __CLASS__, 'settings_page' )  // callback
		);

	}

	/**
	 * admin_menu hook for the options page calls this
	 */
	static function settings_page () {

?>
	<div class="wrap">
		<div id="icon-themes" class="icon32"></div>
		<h2>Locked User Settings</h2>

		<form method="post" action="options.php">
			<?php settings_fields( SettingsMeta::OPTION_GROUP ); ?>
			<?php do_settings_sections( SettingsMeta::OPTION_MENU_PAGE ); ?>
			<?php submit_button(); ?>
		</form>
	</div>
<?php

	}

	/**
	 *
	 */
	static function section_general () {

		// No extra markup at the moment but this function must exist?

	}

	/**
	 *
	 */
	static function field_global_whitelist () {

		$option_value = self::get_global_whitelist();

		$textarea_template = '<textarea cols="40" rows="5" name="%s[%s]">%s</textarea>';

		echo sprintf( $textarea_template, SettingsMeta::OPTION_NAME, SettingsFields::GLOBAL_WHITELIST_ID, esc_textarea( $option_value ) );

	}

	/**
	 *
	 */
	static function field_authentication_message () {

		$option_value = self::get_authentication_message();

		$textarea_template = '<textarea cols="40" rows="5" name="%s[%s]">%s</textarea>';

		echo sprintf( $textarea_template, esc_attr( SettingsMeta::OPTION_NAME ), esc_attr( SettingsFields::AUTHENTICATION_MESSAGE_ID ), esc_textarea( $option_value ) );

	}

	/**
	 *
	 */
	static function field_locked_redirect_url () {

		$option_value = self::get_locked_redirect_url();

		$textarea_template = '<textarea cols="40" rows="5" name="%s[%s]">%s</textarea>';

		echo sprintf( $textarea_template, esc_attr( SettingsMeta::OPTION_NAME ), esc_attr( SettingsFields::LOCKED_REDIRECT_URL_ID ), esc_textarea( $option_value ) );

	}

	/**
	 *
	 */
	static function field_disabled_redirect_url () {

		$option_value = self::get_disabled_redirect_url();

		$textarea_template = '<textarea cols="40" rows="5" name="%s[%s]">%s</textarea>';

		echo sprintf( $textarea_template, esc_attr( SettingsMeta::OPTION_NAME ), esc_attr( SettingsFields::DISABLED_REDIRECT_URL_ID ), esc_textarea( $option_value ) );

	}

	/**
	 * Called by the personal_options action
	 *
	 * @param object $user The user object for the user currently being edited.
	 */
	static function personal_options ( $user ) {

		// Check for bypass via custom filter
		if ( apply_filters( 'locked_users_disable_user_profile', false ) ) {
			return;
		}

		// Don't allow locking for admins
		if ( is_super_admin( $user->ID ) || user_can( $user, 'manage_options' ) ) {
			return;
		}

		$user_status = self::get_user_status( $user->ID );
?>
	<tr class="locked-users">
		<th scope="row">Locked Users</th>
		<td>
			<fieldset>
				<label for="<?php echo esc_attr( UserMeta::USER_STATUS ); ?>">
					<input name="<?php echo esc_attr( UserMeta::USER_STATUS ); ?>" type="radio" value="<?php echo esc_attr( UserStatuses::NORMAL ); ?>"<?php checked( UserStatuses::NORMAL, $user_status ); ?> />
					Normal<br />
					<input name="<?php echo esc_attr( UserMeta::USER_STATUS ); ?>" type="radio" value="<?php echo esc_attr( UserStatuses::LOCKED ); ?>"<?php checked( UserStatuses::LOCKED, $user_status ); ?> />
					Locked<br />
					<input name="<?php echo esc_attr( UserMeta::USER_STATUS ); ?>" type="radio" value="<?php echo esc_attr( UserStatuses::DISABLED ); ?>"<?php checked( UserStatuses::DISABLED, $user_status ); ?> />
					Disabled<br />
				</label>
				<br>
				<label for="<?php echo esc_attr( UserMeta::WHITELIST ); ?>">
					<textarea name="<?php echo esc_attr( UserMeta::WHITELIST ); ?>" rows="8"><?php echo esc_textarea( self::get_user_whitelist( $user->ID ) ); ?></textarea>
					<br /> <span class="description"><?php echo wp_kses_post( UserMeta::WHITELIST_TITLE ); ?></span>
				</label>
			</fieldset>
		</td>
	</tr>
<?php

	}

	/**
	 * @param int $user_id User ID.
	 */
	static function save_user_meta ( $user_id ) {

		// Check for bypass via custom filter
		if ( apply_filters( 'locked_users_disable_user_profile', false ) ) {

			// Bypass completely
			return;

		}

		// Don't allow locking for admins
		if ( is_super_admin( $user_id ) || user_can( $user_id, 'manage_options' ) ) {

			return;

		}

		// Get status
		$status = UserStatuses::NORMAL;
		if ( isset( $_POST[ UserMeta::USER_STATUS ] ) ) {

			$status = sanitize_text_field( $_POST[ UserMeta::USER_STATUS ] );

		}

		// Get whitelist
		$whitelist = '';
		if ( isset( $_POST[ UserMeta::WHITELIST ] ) ) {

			$whitelist = sanitize_text_field( $_POST[ UserMeta::WHITELIST ] );

		}

		// Set
		self::set_user_status( $user_id, $status );
		self::set_user_whitelist( $user_id, $whitelist );

	}

	/**
	 * @param string $option_name
	 *
	 * @return string
	 */
	static function get_option( $option_name ) {

		$settings = get_option( SettingsMeta::OPTION_NAME, array() );

		$setting = '';

		if ( ! empty( $settings[ $option_name ] ) ) {
			$setting = $settings[ $option_name ];
		}

		return $setting;

	}

	/**
	 * @return string
	 */
	static function get_global_whitelist() {

		return self::get_option( SettingsFields::GLOBAL_WHITELIST_ID );

	}

	/**
	 * @return string
	 */
	static function get_authentication_message () {

		return self::get_option( SettingsFields::AUTHENTICATION_MESSAGE_ID );

	}

	/**
	 * @return string
	 */
	static function get_locked_redirect_url () {

		return self::get_option( SettingsFields::LOCKED_REDIRECT_URL_ID );

	}

	/**
	 * @return string
	 */
	static function get_disabled_redirect_url () {

		return self::get_option( SettingsFields::DISABLED_REDIRECT_URL_ID );

	}

	/**
	 * @param int $user_id User ID.
	 *
	 * @return mixed
	 */
	static function get_user_whitelist ( $user_id ) {

		return get_user_meta( $user_id, UserMeta::WHITELIST, true );

	}

	/**
	 * @param int $user_id User ID.
	 * @param string $whitelist
	 */
	static function set_user_whitelist ( $user_id, $whitelist ) {

		update_user_meta( $user_id, UserMeta::WHITELIST, $whitelist );

	}

	/**
	 * @param int $user_id User ID.
	 *
	 * @return mixed
	 */
	static function get_user_access_hash ( $user_id ) {

		return get_user_meta( $user_id, UserMeta::ACCESS_HASH, true );

	}

	/**
	 * @param int $user_id User ID.
	 * @param string $access_hash The hash code to save
	 */
	static function set_user_access_hash ( $user_id, $access_hash ) {

		update_user_meta( $user_id, UserMeta::ACCESS_HASH, $access_hash );

	}

	/**
	 * @param int $user_id
	 *
	 * @return mixed
	 */
	static function get_user_status( $user_id ) {

		$status = get_user_meta( $user_id, UserMeta::USER_STATUS, true );

		// Default to 'member' if they don't have any meta saved at all, or if they are a super admin
		if ( '' === $status || is_super_admin( $user_id ) || user_can( $user_id, 'manage_options' ) ) {

			$status = UserStatuses::NORMAL;

		} elseif ( ! UserStatuses::user_status_exists( $status ) && true !== apply_filters( 'locked_users_status_supported', false, $status ) ) {

			// Disable users if status is not supported, better option than to allow full access as normal user
			$status = UserStatuses::DISABLED;
		}

		return $status;

	}

	/**
	 * @param int $user_id
	 * @param mixed $new_status
	 */
	static function set_user_status( $user_id, $new_status ) {

		if ( is_super_admin( $user_id ) || user_can( $user_id, 'manage_options' ) ) {

			// Admins cannot be locked or disabled
			return;

		}

		$old_status = self::get_user_status( $user_id );

		// Is the supplied user status invalid?
		if ( !UserStatuses::user_status_exists( $new_status ) ) {

			// Last ditch effort: has anything extended via filter to accept this status?
			if ( true !== apply_filters( 'locked_users_status_supported', false, $new_status ) ) {

				// This just isn't a valid status. Do not update, we don't want to unlock/change users if
				// the status is not supported
				return;

			}
		}

		update_user_meta( $user_id, UserMeta::USER_STATUS, $new_status );
		do_action( 'locked_users_user_status_change', $user_id, $old_status, $new_status );

	}

}
