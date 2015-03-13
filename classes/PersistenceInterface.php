<?php
namespace LockedUsers;

interface PersistenceInterface {

	/**
	 *
	 */
	static function init();

	/**
	 * @return string
	 */
	static function get_global_whitelist();

	/**
	 * @return string
	 */
	static function get_authentication_message();

	/**
	 * @return string
	 */
	static function get_locked_redirect_url();

	/**
	 * @return string
	 */
	static function get_disabled_redirect_url();

	/**
	 * @param int $user_id User ID.
	 *
	 * @return mixed
	 */
	static function get_user_whitelist ( $user_id );

	/**
	 * @param int $user_id User ID.
	 * @param string $whitelist
	 */
	static function set_user_whitelist ( $user_id, $whitelist );

	/**
	 * @param int $user_id User ID.
	 *
	 * @return mixed
	 */
	static function get_user_access_hash ( $user_id );

	/**
	 * @param int $user_id User ID.
	 * @param string $access_hash The hash code to save
	 */
	static function set_user_access_hash ( $user_id, $access_hash );

	/**
	 * @param int $user_id
	 *
	 * @return mixed
	 */
	static function get_user_status( $user_id );

	/**
	 * @param int $user_id
	 * @param mixed $new_status
	 */
	static function set_user_status( $user_id, $new_status );
}