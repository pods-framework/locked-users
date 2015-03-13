<?php
namespace LockedUsers;

abstract class UserStatuses {

	const NORMAL = 'normal';
	const LOCKED = 'locked';
	const DISABLED = 'disabled';

	/**
	 * @param string $status The user status to be checked
	 *
	 * @return bool
	 */
	static function user_status_exists( $status ) {

		$reflection = new \ReflectionClass( __CLASS__ );
		$class_constants = $reflection->getConstants();

		foreach( $class_constants as $this_constant => $this_value ) {

			if ( $this_value === $status ) {

				return true;
			}
		}

		return false;

	}

	static function get_status_by_value( $target_value ) {

		$reflection = new \ReflectionClass( __CLASS__ );
		$class_constants = $reflection->getConstants();

		foreach( $class_constants as $this_constant => $this_value ) {

			if ( $this_value === $target_value ) {

				return true;
			}
		}

		return false;
	}

}