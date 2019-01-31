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
	 * Vtiger_SocialMedia_Model constructor.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 */
	public function __construct(\Vtiger_Record_Model $recordModel)
	{
		parent::__construct();
		$this->recordModel = $recordModel;
	}

	/**
	 * Function to get instance of this object.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 *
	 * @return self
	 */
	public static function getInstanceByRecordModel(\Vtiger_Record_Model $recordModel)
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
	public function isEnableForRecord()
	{
		if (!\App\SocialMedia::isEnableForModule($this->recordModel->getModuleName())) {
			return false;
		}
		foreach (\App\SocialMedia::ALLOWED_UITYPE as $uiType => $socialMediaType) {
			$allFieldModel = $this->recordModel->getModule()->getFieldsByUiType($uiType);
			foreach ($allFieldModel as $twitterField) {
				if (!empty($this->recordModel->get($twitterField->getColumnName()))) {
					return true;
				}
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
	public function getAllSocialMediaAccount(string $socialType)
	{
		$uiType = \App\SocialMedia::getUitypeFromParam($socialType)[0];
		$socialAccount = [];
		foreach ($this->recordModel->getModule()->getFieldsByUiType($uiType) as $socialField) {
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
		foreach (\App\SocialMedia::ALLOWED_UITYPE as $uiType => $socialMediaType) {
			foreach ($this->recordModel->getModule()->getFieldsByUiType($uiType) as $socialField) {
				$columnNames[$uiType] = $socialField->getColumnName();
			}
		}
		return $columnNames;
	}

	/**
	 * Get all social media records.
	 *
	 * @param int $start
	 * @param int $limit
	 *
	 * @return \Generator
	 */
	public function getAllRecords(int $start = 0, int $limit = 50)
	{
		$dataReader = \App\SocialMedia\Twitter::getQueryList($this->getAllSocialMediaAccount('twitter'))
			->orderBy(['created' => SORT_DESC])
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
