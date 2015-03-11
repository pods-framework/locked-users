<?php
namespace LockedUsers;

/**
 * Currently manages both the settings and the usermeta
 */
class Persistence {

	/**
	 * Called on the plugins_loaded action
	 */
	static function plugins_loaded () {

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
			SettingsMeta::OptionSectionGeneral,     // ID
			SettingsMeta::SectionGeneralTitle,      // title
			array( __CLASS__, 'section_general' ),  // callback
			SettingsMeta::OptionMenuPage            // page
		);

		// Global whitelist
		add_settings_field(
			SettingsFields::GlobalWhitelistID,             // ID
			SettingsFields::GlobalWhitelistTitle,          // Title
			array( __CLASS__, 'field_global_whitelist' ),  // callback
			SettingsMeta::OptionMenuPage,                  // page
			SettingsMeta::OptionSectionGeneral             // section
		);

		// Authentication message on unauthorized login attempt
		add_settings_field(
			SettingsFields::AuthenticationMessageID,             // ID
			SettingsFields::AuthenticationMessageTitle,          // Title
			array( __CLASS__, 'field_authentication_message' ),  // callback
			SettingsMeta::OptionMenuPage,                        // page
			SettingsMeta::OptionSectionGeneral                   // section
		);

		// URL to redirect to on unauthorized page access attempts for locked users
		add_settings_field(
			SettingsFields::LockedRedirectURLID,              // ID
			SettingsFields::LockedRedirectURLTitle,           // Title
			array( __CLASS__, 'field_locked_redirect_url' ),  // callback
			SettingsMeta::OptionMenuPage,                     // page
			SettingsMeta::OptionSectionGeneral                // section
		);

		// URL to redirect to on unauthorized page access attempts for disabled users
		add_settings_field(
			SettingsFields::DisabledRedirectURLID,              // ID
			SettingsFields::DisabledRedirectURLTitle,           // Title
			array( __CLASS__, 'field_disabled_redirect_url' ),  // callback
			SettingsMeta::OptionMenuPage,                       // page
			SettingsMeta::OptionSectionGeneral                  // section
		);

		register_setting( SettingsMeta::OptionGroup, SettingsMeta::OptionName );

	}

	/**
	 * Called on the admin_menu action
	 */
	static function admin_menu () {

		add_options_page(
			'Locked User Settings',              // page title
			'Locked User Settings',              // menu title
			'manage_options',                    // cap
			SettingsMeta::OptionMenuPage,        // menu slug
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
			<?php settings_fields( SettingsMeta::OptionGroup ); ?>
			<?php do_settings_sections( SettingsMeta::OptionMenuPage ); ?>
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

		echo sprintf( $textarea_template, SettingsMeta::OptionName, SettingsFields::GlobalWhitelistID, esc_textarea( $option_value ) );

	}

	/**
	 *
	 */
	static function field_authentication_message () {

		$option_value = self::get_authentication_message();

		$textarea_template = '<textarea cols="40" rows="5" name="%s[%s]">%s</textarea>';

		echo sprintf( $textarea_template, esc_attr( SettingsMeta::OptionName ), esc_attr( SettingsFields::AuthenticationMessageID ), esc_textarea( $option_value ) );

	}

	/**
	 *
	 */
	static function field_locked_redirect_url () {

		$option_value = self::get_locked_redirect_url();

		$textarea_template = '<textarea cols="40" rows="5" name="%s[%s]">%s</textarea>';

		echo sprintf( $textarea_template, esc_attr( SettingsMeta::OptionName ), esc_attr( SettingsFields::LockedRedirectURLID ), esc_textarea( $option_value ) );

	}

	/**
	 *
	 */
	static function field_disabled_redirect_url () {

		$option_value = self::get_disabled_redirect_url();

		$textarea_template = '<textarea cols="40" rows="5" name="%s[%s]">%s</textarea>';

		echo sprintf( $textarea_template, esc_attr( SettingsMeta::OptionName ), esc_attr( SettingsFields::DisabledRedirectURLID ), esc_textarea( $option_value ) );

	}

	/**
	 * Called by the personal_options action
	 *
	 * @param object $user The user object for the user currently being edited.
	 */
	static function personal_options ( $user ) {

		$user_status = self::get_member_status( $user->ID );
?>
	<tr class="locked-users">
		<th scope="row">Locked Users</th>
		<td>
			<fieldset>
				<label for="<?php echo esc_attr( UserMeta::MemberStatus ); ?>">
					<input name="<?php echo esc_attr( UserMeta::MemberStatus ); ?>" type="radio" value="<?php echo esc_attr( MemberStatus::Normal ); ?>"<?php checked( MemberStatus::Normal, $user_status ); ?> />
					Normal<br />
					<input name="<?php echo esc_attr( UserMeta::MemberStatus ); ?>" type="radio" value="<?php echo esc_attr( MemberStatus::Locked ); ?>"<?php checked( MemberStatus::Locked, $user_status ); ?> />
					Locked<br />
					<input name="<?php echo esc_attr( UserMeta::MemberStatus ); ?>" type="radio" value="<?php echo esc_attr( MemberStatus::Disabled ); ?>"<?php checked( MemberStatus::Disabled, $user_status ); ?> />
					Disabled<br />
				</label>
				<br>
				<label for="<?php echo esc_attr( UserMeta::Whitelist ); ?>">
					<textarea name="<?php echo esc_attr( UserMeta::Whitelist ); ?>" rows="8"><?php echo esc_textarea( self::get_user_whitelist( $user->ID ) ); ?></textarea>
					<br /> <span class="description"><?php echo wp_kses_post( UserMeta::WhitelistTitle ); ?></span>
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

		// Get status
		$status = MemberStatus::Normal;

		if ( isset( $_POST[ UserMeta::MemberStatus ] ) ) {
			$status = sanitize_text_field( $_POST[ UserMeta::MemberStatus ] );
		}

		// Get whitelist
		$whitelist = '';

		if ( isset( $_POST[ UserMeta::Whitelist ] ) ) {
			$whitelist = sanitize_text_field( $_POST[ UserMeta::Whitelist ] );
		}

		// Set
		self::set_member_status( $user_id, $status );
		self::set_user_whitelist( $user_id, $whitelist );

	}

	/**
	 * @param string $option_name
	 *
	 * @return string
	 */
	static function get_option( $option_name ) {

		$settings = get_option( SettingsMeta::OptionName, array() );

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

		return self::get_option( SettingsFields::GlobalWhitelistID );

	}

	/**
	 * @return string
	 */
	static function get_authentication_message () {

		return self::get_option( SettingsFields::AuthenticationMessageID );

	}

	/**
	 * @return string
	 */
	static function get_locked_redirect_url () {

		return self::get_option( SettingsFields::LockedRedirectURLID );

	}

	/**
	 * @return string
	 */
	static function get_disabled_redirect_url () {

		return self::get_option( SettingsFields::DisabledRedirectURLID );

	}

	/**
	 * @param int $user_id User ID.
	 *
	 * @return mixed
	 */
	static function get_user_whitelist ( $user_id ) {

		return get_user_meta( $user_id, UserMeta::Whitelist, true );

	}

	/**
	 * @param int $user_id User ID.
	 * @param string $whitelist
	 */
	static function set_user_whitelist ( $user_id, $whitelist ) {

		update_user_meta( $user_id, UserMeta::Whitelist, $whitelist );

	}

	/**
	 * @param int $user_id
	 *
	 * @return mixed
	 */
	static function get_member_status( $user_id ) {

		$status = get_user_meta( $user_id, UserMeta::MemberStatus, true );

		// Default to 'member' if they don't have any meta saved at all
		if ( '' === $status || ! defined( __NAMESPACE__ . '\\MemberStatus::' . ucwords( $status ) ) ) {
			$status = MemberStatus::Normal;
		}

		return $status;

	}

	/**
	 * @param int $user_id
	 * @param mixed $status
	 */
	static function set_member_status( $user_id, $status ) {

		// Check if status exists, if it does then enforce the internal value
		if ( $status && defined( __NAMESPACE__ . '\\MemberStatus::' . ucwords( $status ) ) ) {
			$status = constant( __NAMESPACE__ . '\\MemberStatus::' . ucwords( $status ) );
		} else {
			$status = MemberStatus::Normal;
		}

		update_user_meta( $user_id, UserMeta::MemberStatus, $status );

	}

}