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
		//TODO: App/SocialMEdia/Abstract
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
		foreach ($this->recordModel->getModule()->getFieldsByUiType($uitype) as $socialField) {
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
	public static function getAllColumnName()
	{
		$columnNames = [];
		foreach (\App\SocialMedia\SocialMedia::ALLOWED_UITYPE as $uiType => $socialMediaType) {
			if (in_array($socialMediaType, $this->moduleConfig)) {
				foreach ($this->recordModel->getModule()->getFieldsByUiType($uiType) as $socialField) {
					$columnNames[] = $socialField->getColumnName();
				}
			}
		}
		return $columnNames;
	}
}
