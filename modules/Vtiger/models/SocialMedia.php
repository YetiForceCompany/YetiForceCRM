<?php

/**
 * SocialMedia Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Vtiger_SocialMedia_Model extends Vtiger_Module_Model
{
	/**
	 * Checking whether social media are available for the module.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	public static function isEnableForModule($recordModel)
	{
		$socialMediaConfig = \AppConfig::module($recordModel->getModuleName(), 'ENABLE_SOCIAL');
		if (false === $socialMediaConfig || empty($socialMediaConfig)) {
			return false;
		}
		if (!is_array($socialMediaConfig)) {
			throw new \App\Exceptions\AppException('Incorrect data type in ' . $recordModel->getModuleName() . ':ENABLE_SOCIAL');
		}
		if (!in_array('TWITTER', $socialMediaConfig)) {
			return false;
		}
		$allFieldModel = $recordModel->getModule()->getFieldsByUiType(313);
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
		$allFieldModel = $recordModel->getModule()->getFieldsByUiType(313);
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
	 * @param string[] $twitterLogin
	 * @param int      $start
	 * @param int      $limit
	 *
	 * @return \SocialMedia_Record_Model[]
	 */
	public static function getAllRecords($twitterLogin = [], $start = 0, $limit = 50)
	{
		$query = (new \App\Db\Query())->from('u_#__social_media_twitter');
		if (empty($twitterLogin)) {
			$query->where(['twitter_login' => $twitterLogin]);
		}
		$dataReader = $query->orderBy(['created' => SORT_DESC])
			->limit($limit)
			->offset($start)
			->createCommand()
			->query();
		while (($row = $dataReader->read())) {
			$recordModel = SocialMedia_Record_Model::getCleanInstance();
			$recordModel->setData($row);
			yield $recordModel;
		}
		$dataReader->close();
	}
}
