<?php
/*
Plugin Name: Locked Users
Description: Locked users lets you create user accounts that will have no ability to login, reset password, or access any pages on a site -- except for a whitelist of pages and any added to the specific user -- access to the site will be explicitly given through function calls that get sent via e-mail with a unique keyed link
Version: 0.0.1
Author: Pods Framework Team
Author URI: http://pods.io
License:     GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Copyright 2009-2014  Pods Foundation, Inc  (email : contact@podsfoundation.org)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
namespace LockedUsers;

define( 'LOCKED_USERS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LOCKED_USERS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once 'MetaKeys.php';

/* Set up the plugin on the 'plugins_loaded' hook. */
add_action( 'plugins_loaded', __NAMESPACE__ . '\\plugins_loaded' );
add_action( 'personal_options', __NAMESPACE__ . '\\personal_options' );

/**
 *
 */
function plugins_loaded () {

	load_plugin_textdomain( 'locked-users', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	add_filter( 'allow_password_reset', __NAMESPACE__ . '\\allow_password_reset', 10, 2 );

	/* User meta updates */
	add_action( 'personal_options_update', __NAMESPACE__ . '\\save_user_meta' );
	add_action( 'edit_user_profile_update', __NAMESPACE__ . '\\save_user_meta' );
}

/**
 * @param Boolean $allow
 * @param int $user_id User ID.
 *
 * @return Boolean
 */
function allow_password_reset ( $allow, $user_id ) {

	if ( is_locked( $user_id ) ) {
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
function personal_options ( $user ) { ?>
	<tr class="locked-users">
		<th scope="row"><?php _e( 'Locked Users', 'locked-users' ); ?></th>
		<td>
			<fieldset>
				<label for="locked-user">
					<input name="<?php echo MetaKeys::Locked; ?>" type="checkbox" id="locked-user" value="1" <?php checked( is_locked( $user->ID ) ); ?> />
					<?php _e( 'Lock this user', 'locked-users' ); ?>
				</label>
				<br>
				<label for="locked-user-whitelist">
					<textarea name="<?php echo MetaKeys::Whitelist; ?>" rows="8"><?php echo esc_textarea( whitelist( $user->ID ) ); ?></textarea>
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
function save_user_meta ( $user_id ) {

	$locked = isset( $_POST[ MetaKeys::Locked ] ) ? 1 : 0;
	$whitelist = ( isset( $_POST[ MetaKeys::Whitelist ] ) ) ? $_POST[ MetaKeys::Whitelist ] : ''; 

	set_is_locked( $user_id, $locked );
	set_whitelist( $user_id, $whitelist );
	
}

/**
 * @param int $user_id User ID.
 *
 * @return Boolean
 */
function is_locked( $user_id ) {
	return (Bool) get_user_meta( $user_id, MetaKeys::Locked, true );
}


/**
 * @param int $user_id User ID.
 * @param Boolean $locked
 */
function set_is_locked( $user_id, $locked ) {
	update_user_meta( $user_id, MetaKeys::Locked, $locked );
}

/**
 * @param int $user_id User ID.
 *
 * @return mixed
 */
function whitelist( $user_id ) {
	return get_user_meta( $user_id, MetaKeys::Whitelist, true );
}

/**
 * @param int $user_id User ID.
 * @param string $whitelist
 */
function set_whitelist( $user_id, $whitelist ) {
	
	update_user_meta( $user_id, MetaKeys::Whitelist, $whitelist );
}
