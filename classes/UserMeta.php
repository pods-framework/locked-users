<?php
namespace LockedUsers;

abstract class UserMeta {

	const MemberStatus = 'locked_user_member_status';
	
	const Whitelist = 'locked_user_whitelist';
	const WhitelistTitle = 'List of allowed URLs, one per line (regex allowed)';
}