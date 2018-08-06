<?php

/**
 * SocialMedia Module Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */
class SocialMedia_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Checking whether social media are available for the module.
	 *
	 * @param string $moduleName
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	public static function isEnableForModule($moduleName)
	{
		$socialMediaConfig = AppConfig::module($moduleName, 'ENABLE_SOCIAL');
		if (false===$socialMediaConfig || empty($socialMediaConfig)) {
			return false;
		}
		if (!is_array($socialMediaConfig)) {
			throw new \App\Exceptions\AppException("Incorrect data type in $moduleName:ENABLE_SOCIAL");
		}
		return true;
	}
}
