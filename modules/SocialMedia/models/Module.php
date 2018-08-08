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

	/**
	 * Check if there is Twitter available.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	public static function isTwitterAvailable($recordModel)
	{
		$allFieldModel = $recordModel->getModule()->getFieldsByUiType(359);
		foreach ($allFieldModel as $twitterField) {
			if (!empty($recordModel->get($twitterField->getColumnName()))) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get all twitter account names.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return string[]
	 */
	public static function getAllTwitterAccount($recordModel)
	{
		$twitterAccount = [];
		$allFieldModel = $recordModel->getModule()->getFieldsByUiType(359);
		foreach ($allFieldModel as $twitterField) {
			$val = $recordModel->get($twitterField->getColumnName());
			if (!empty($val)) {
				$twitterAccount[] = $val;
			}
		}
		return $twitterAccount;
	}

	/**
	 * Get all records by twitter account.
	 *
	 * @param string $twitterLogin
	 *
	 * @return \SocialMedia_Record_Model[]
	 */
	public static function getAllRecordsByName($twitterLogin)
	{
		//TODO: Get data from the database
	}
}
