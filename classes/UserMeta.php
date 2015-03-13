<?php
namespace LockedUsers;

abstract class UserMeta {

	const USER_STATUS = 'locked_users_user_status';
	const ACCESS_HASH = 'locked_users_access_hash';

	const WHITELIST = 'locked_users_whitelist';
	const WHITELIST_TITLE = 'List of allowed URLs, one per line (regex allowed)';

}