<?php
namespace LockedUsers;

abstract class UserMeta {

	const Locked = 'locked_user_locked';
	const LockedTitle = 'Lock this user';
	
	const Whitelist = 'locked_user_whitelist';
	const WhitelistTitle = 'List of allowed URLs, one per line';
}