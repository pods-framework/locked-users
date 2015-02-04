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

// Abort if this file is called directly
if ( !defined( 'WPINC' ) ) {
	die;
}

define( 'LOCKED_USERS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LOCKED_USERS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
