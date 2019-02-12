<?php

/**
 * Configuration file.
 * This file is auto-generated.
 *
 * @package Config
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

namespace Config;

/**
 * Configuration Class.
 */
class Security
{
	/**
	 * Password encrypt algorithmic cost. Numeric values - we recommend values greater than 10.
	 * The greater the value, the longer it takes to encrypt the password.
	 */
	public static $USER_ENCRYPT_PASSWORD_COST = 10;

	/** Possible to reset the password while logging in (true/false) */
	public static $RESET_LOGIN_PASSWORD = false;

	/** Show my preferences */
	public static $SHOW_MY_PREFERENCES = true;

	/** Changing the settings by the user is possible true/false */
	public static $CHANGE_LOGIN_PASSWORD = true;

	/** Permitted by roles. */
	public static $PERMITTED_BY_ROLES = true;

	/** Permitted by sharing. */
	public static $PERMITTED_BY_SHARING = true;

	/** Permitted by shared owners. */
	public static $PERMITTED_BY_SHARED_OWNERS = true;

	/** Permitted by record hierarchy. */
	public static $PERMITTED_BY_RECORD_HIERARCHY = true;

	/** Permitted by advanced permission. */
	public static $PERMITTED_BY_ADVANCED_PERMISSION = true;

	/** Permitted by private field. */
	public static $PERMITTED_BY_PRIVATE_FIELD = false;

	/**
	 * Configuration of the permission mechanism on records list.
	 * true - Permissions based on the users column in vtiger_crmentity.
	 * 		Permissions are not verified in real time. They are updated via cron.
	 * 		We do not recommend using this option in production environments.
	 * false - Permissions based on adding tables with permissions to query (old mechanism).
	 */
	public static $CACHING_PERMISSION_TO_RECORD = false;

	/**
	 * Restricted domains allow you to block saving an email address from a given domain in the system.
	 * Restricted domains work only for email address type fields.
	 */
	public static $EMAIL_FIELD_RESTRICTED_DOMAINS_ACTIVE = false;

	/** Restricted domains */
	public static $EMAIL_FIELD_RESTRICTED_DOMAINS_VALUES = [];

	/** List of modules where restricted domains are enabled, if empty it will be enabled everywhere. */
	public static $EMAIL_FIELD_RESTRICTED_DOMAINS_ALLOWED = [];

	/** List of modules excluded from restricted domains validation. */
	public static $EMAIL_FIELD_RESTRICTED_DOMAINS_EXCLUDED = [];

	/** Remember user credentials */
	public static $LOGIN_PAGE_REMEMBER_CREDENTIALS = false;

	/** Interdependent reference fields */
	public static $FIELDS_REFERENCES_DEPENDENT = false;

	/**
	 * HTTP Public-Key-Pins (HPKP) pin-sha256 For HPKP to work properly at least 2 keys are needed.
	 * https://scotthelme.co.uk/hpkp-http-public-key-pinning/, https://sekurak.pl/mechanizm-http-public-key-pinning/.
	 */
	public static $HPKP_KEYS = [];

	/** Content Security Policy */
	public static $CSP_ACTIVE = true;

	/** List of allowed domains for fields with HTML support */
	public static $PURIFIER_ALLOWED_DOMAINS = [];

	/** Lifetime session (in seconds) */
	public static $MAX_LIFETIME_SESSION = 21600;

	/**
	 * User authentication mode.
	 *
	 * @see \Users_Totp_Authmethod::ALLOWED_USER_AUTHY_MODE Available values.
	 */
	public static $USER_AUTHY_MODE = 'TOTP_OPTIONAL';

	/** Cache lifetime for SensioLabs security checker. */
	public static $CACHE_LIFETIME_SENSIOLABS_SECURITY_CHECKER = 3600;
}
