<?php
/**
 * Settings MailIntegration Activate model file.
 *
 * @package   Module
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings MailIntegration Activate model class.
 */
class Settings_MailIntegration_Activate_Model
{
	/**
	 * Function checks if user is active..
	 *
	 * @param string $source
	 *
	 * @return bool
	 */
	public static function isActive(string $source): bool
	{
		if ('outlook' === $source) {
			return \in_array('https://appsforoffice.microsoft.com', \Config\Security::$allowedScriptDomains) &&
				\in_array('https://ajax.aspnetcdn.com', \Config\Security::$allowedScriptDomains) &&
				'parent' === \Config\Security::$csrfFrameBreakerWindow &&
				false === \Config\Security::$cookieForceHttpOnly &&
				'None' === \Config\Security::$cookieSameSite;
		}
		return false;
	}
}
