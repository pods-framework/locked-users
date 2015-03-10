<?php
namespace LockedUsers;

abstract class SettingsFields {

	// Newline delimited list of URLs allowed for all users (regex accepted)
	const GlobalWhitelistID = 'locked_users_global_whitelist';
	const GlobalWhitelistTitle = 'Global Whitelist';

	// Authentication message on unauthorized login attempt
	const AuthenticationMessageID = 'locked_users_authentication_message';
	const AuthenticationMessageTitle = 'Authentication Message';

	// Page to redirect to on unauthorized page view attempts
	const RedirectURLID = 'locked_users_redirect_url';
	const RedirectURLTitle = 'Redirect URL';
}
