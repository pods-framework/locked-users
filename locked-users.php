<?php
namespace LockedUsers;

/*
Plugin Name: Locked Users
Description: Locked users lets you create user accounts that will have no ability to login, reset password, or access any pages on a site -- except for a whitelist of pages and any added to the specific user -- access to the site will be explicitly given through function calls that get sent via e-mail with a unique keyed link
Version: 0.0.1
Author: Pods Framework Team
Author URI: http://pods.io
License:     GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Copyright 2015  Pods Foundation, Inc  (email : contact@podsfoundation.org)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

define( 'LOCKED_USERS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LOCKED_USERS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once 'classes/UserStatuses.php';
require_once 'classes/UserMeta.php';
require_once 'classes/SettingsMeta.php';
require_once 'classes/SettingsFields.php';
require_once 'classes/QueryArgs.php';

require_once 'classes/PersistenceInterface.php';
require_once 'classes/Persistence.php';
require_once 'classes/Plugin.php';

// Hook plugins_loaded where needed and we're done
if ( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	add_action( 'plugins_loaded', array( __NAMESPACE__ . '\\Plugin', 'init' ) );
	add_action( 'plugins_loaded', array( __NAMESPACE__ . '\\Persistence', 'init' ) );
}