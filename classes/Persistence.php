<?php
namespace LockedUsers;

// ToDo: Get rid of the implicit dependency on Plugin

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

		// URL to redirect to on unauthorized page access attempts
		add_settings_field(
			SettingsFields::RedirectURLID,             // ID
			SettingsFields::RedirectURLTitle,          // Title
			array( __CLASS__, 'field_redirect_url' ),  // callback
			SettingsMeta::OptionMenuPage,              // page
			SettingsMeta::OptionSectionGeneral         // section
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
		echo sprintf( $textarea_template, SettingsMeta::OptionName, SettingsFields::GlobalWhitelistID, $option_value );
	}

	/**
	 * 
	 */
	static function field_authentication_message () {

		$option_value = self::get_authentication_message();

		$textarea_template = '<textarea cols="40" rows="5" name="%s[%s]">%s</textarea>';
		echo sprintf( $textarea_template, SettingsMeta::OptionName, SettingsFields::AuthenticationMessageID, $option_value );
	}

	/**
	 * 
	 */
	static function field_redirect_url () {

		$option_value = self::get_redirect_url();

		$textarea_template = '<textarea cols="40" rows="5" name="%s[%s]">%s</textarea>';
		echo sprintf( $textarea_template, SettingsMeta::OptionName, SettingsFields::RedirectURLID, $option_value );
		
	}
	
	/**
	 * Called by the personal_options action
	 *
	 * @param  object $user The user object for the user currently being edited.
	 * 
	 * ToDo: Get rid of the implicit dependency on Plugin
	 */
	static function personal_options ( $user ) {
		
		$user_status = self::get_member_status( $user->ID );
		?>
		<tr class="locked-users">
			<th scope="row">Locked Users</th>
			<td>
				<fieldset>
					<label for="<?= UserMeta::MemberStatus; ?>">
						<input name="<?= UserMeta::MemberStatus; ?>" type="radio" value="<?= MemberStatus::Member; ?>" <?php checked( MemberStatus::Member, $user_status ); ?> /> Member
						<br>
						<input name="<?= UserMeta::MemberStatus; ?>" type="radio" value="<?= MemberStatus::Probationary; ?>" <?php checked( MemberStatus::Probationary, $user_status ); ?> /> Probationary
						<br>
						<input name="<?= UserMeta::MemberStatus; ?>" type="radio" value="<?= MemberStatus::Disabled; ?>" <?php checked( MemberStatus::Disabled, $user_status ); ?> /> Disabled
						<br>
					</label> 
					<br> 
					<label for="<? UserMeta::Whitelist; ?>">
						<textarea name="<?= UserMeta::Whitelist; ?>" rows="8"><?= esc_textarea( self::get_user_whitelist( $user->ID ) ); ?></textarea>
						<br> <span class="description"><?= UserMeta::WhitelistTitle; ?></span>
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

		$status = isset( $_POST[ UserMeta::MemberStatus ] ) ? $_POST[ UserMeta::MemberStatus ] : MemberStatus::Member;
		$whitelist = ( isset( $_POST[ UserMeta::Whitelist ] ) ) ? $_POST[ UserMeta::Whitelist ] : '';

		self::set_member_status( $user_id, $status );
		self::set_user_whitelist( $user_id, $whitelist );

	}

	/**
	 * @return string
	 */
	static function get_global_whitelist() {
		
		$settings = get_option( SettingsMeta::OptionName, array() );
		return $settings[ SettingsFields::GlobalWhitelistID ];
	}

	/**
	 * @return string
	 */
	static function get_authentication_message () {
		
		$settings = get_option( SettingsMeta::OptionName, array() );
		return $settings[ SettingsFields::AuthenticationMessageID ];
	}

	/**
	 * @return string
	 */
	static function get_redirect_url () {

		$settings = get_option( SettingsMeta::OptionName, array() );
		return $settings[ SettingsFields::RedirectURLID ];
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
		
		$meta = get_user_meta( $user_id, UserMeta::MemberStatus, true );
		
		// Default to 'member' if they don't have any meta saved at all
		if ( '' === $meta ) {
			
			return MemberStatus::Member;
		}
		
		return $meta;
	}

	/**
	 * @param int $user_id
	 * @param mixed $status
	 */
	static function set_member_status( $user_id, $status ) {
		
		update_user_meta( $user_id, UserMeta::MemberStatus, $status);
	}
	
}
