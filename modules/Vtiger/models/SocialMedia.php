<?php

/**
 * SocialMedia Model.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Vtiger_SocialMedia_Model extends \App\Base
{
	/**
	 * Temporary record object.
	 *
	 * @var \Vtiger_Record_Model
	 */
	private $recordModel;
	/**
	 * Social media module configuration.
	 *
	 * @var string[]
	 */
	private $moduleConfig;

	/**
	 * Vtiger_SocialMedia_Model constructor.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public function __construct($recordModel)
	{
		parent::__construct();
		$this->recordModel = $recordModel;
		$this->moduleConfig = \AppConfig::module($this->recordModel->getModuleName(), 'enable_social');
	}

	/**
	 * Function to get instance of this object.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return self
	 */
	public static function getInstanceByRecordModel($recordModel)
	{
		return new self($recordModel);
	}

	/**
	 * Checking whether social media are available for the record.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	public static function isEnableForRecord($recordModel)
	{
		if (!static::isEnableForModule($recordModel)) {
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
	 * Checking whether social media are available for the module.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return bool
	 */
	public static function isEnableForModule($recordModel)
	{
		$socialMediaConfig = \AppConfig::module($recordModel->getModuleName(), 'enable_social');
		if (false === $socialMediaConfig || empty($socialMediaConfig)) {
			return false;
		}
		if (!is_array($socialMediaConfig)) {
			throw new \App\Exceptions\AppException('Incorrect data type in ' . $recordModel->getModuleName() . ':ENABLE_SOCIAL');
		}
		if (!in_array('twitter', $socialMediaConfig)) {
			return false;
		}
	}

	/**
	 * Get all social media account names by socialType.
	 *
	 * @param string $socialType
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string[]
	 */
	public function getAllSocialMediaAccount($socialType)
	{
		$uitype = null;
		switch ($socialType) {
			case 'twitter':
				$uitype = 313;
				break;
			default:
				throw new \App\Exceptions\AppException('Incorrect data type in ' . $socialType);
		}
		$socialAccount = [];
		$allFieldModel = $this->recordModel->getModule()->getFieldsByUiType($uitype);
		foreach ($allFieldModel as $socialField) {
			$val = $this->recordModel->get($socialField->getColumnName());
			if (!empty($val) && $this->recordModel->isViewable()) {
				$socialAccount[$socialField->getColumnName()] = $val;
			}
		}
		return $socialAccount;
	}

	/**
	 * Get all available social media columns name.
	 *
	 * @return string[]
	 */
	public function getAllColumnName()
	{
		$columnNames = [];
		foreach (\App\SocialMedia\SocialMedia::ALLOWED_UITYPE as $key => $uitype) {
			if (in_array($key, $this->moduleConfig)) {
				$allFieldModel = $this->recordModel->getModule()->getFieldsByUiType($uitype);
				foreach ($allFieldModel as $socialField) {
					$columnNames[] = $socialField->getColumnName();
				}
			}
		}
		return $columnNames;
	}

	/**
	 * Get all records by twitter account.
	 *
	 * @param int $start
	 * @param int $limit
	 *
	 * @return \SocialMedia_Record_Model[]
	 */
	public function getAllRecords($start = 0, $limit = 50)
	{
		$twitterLogin = $this->getAllSocialMediaAccount('twitter');
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
			yield $row;
		}
		$dataReader->close();
	}
}
