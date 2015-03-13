<?php
namespace LockedUsers;

abstract class SettingsFields {

	// Newline delimited list of URLs allowed for all users (regex accepted)
	const GLOBAL_WHITELIST_ID = 'locked_users_global_whitelist';
	const GLOBAL_WHITELIST_TITLE = 'Global Whitelist';

	// Authentication message on unauthorized login attempt
	const AUTHENTICATION_MESSAGE_ID = 'locked_users_authentication_message';
	const AUTHENTICATION_MESSAGE_TITLE = 'Authentication Message';

	// Page to redirect to on unauthorized page view attempts for locked users
	const LOCKED_REDIRECT_URL_ID = 'locked_users_locked_redirect_url';
	const LOCKED_REDIRECT_URL_TITLE = 'Redirect URL for locked users';

	// Page to redirect to on unauthorized page view attempts for disabled users
	const DISABLED_REDIRECT_URL_ID = 'locked_users_disabled_redirect_url';
	const DISABLED_REDIRECT_URL_TITLE = 'Redirect URL for disabled users';

}