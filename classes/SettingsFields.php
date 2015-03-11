<?php
namespace LockedUsers;

abstract class SettingsFields {

	// Newline delimited list of URLs allowed for all users (regex accepted)
	const GlobalWhitelistID = 'locked_users_global_whitelist';
	const GlobalWhitelistTitle = 'Global Whitelist';

	// Authentication message on unauthorized login attempt
	const AuthenticationMessageID = 'locked_users_authentication_message';
	const AuthenticationMessageTitle = 'Authentication Message';

	// Page to redirect to on unauthorized page view attempts for locked users
	const LockedRedirectURLID = 'locked_users_locked_redirect_url';
	const LockedRedirectURLTitle = 'Redirect URL for locked users';

	// Page to redirect to on unauthorized page view attempts for disabled users
	const DisabledRedirectURLID = 'locked_users_disabled_redirect_url';
	const DisabledRedirectURLTitle = 'Redirect URL for disabled users';

}