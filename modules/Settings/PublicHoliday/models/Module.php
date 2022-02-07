<?php

/**
 * Settings PublicHoliday module model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_PublicHoliday_Module_Model extends Settings_Vtiger_Module_Model
{
	public $baseTable = 'vtiger_publicholiday';
	public $baseIndex = 'publicholidayid';
	public $listFields = [
		'holidayname' => 'Name',
		'holidaydate' => 'Date',
		'holidaytype' => 'Type',
	];
	public $nameFields = ['holidayname'];
	public $name = 'PublicHoliday';

	/**
	 * Return info about module paging feature.
	 *
	 * @return bool - true: pageable, false: not pageable
	 */
	public function isPagingSupported()
	{
		return false;
	}

	/**
	 * Returns instance of Settings module model.
	 *
	 * @param string $name
	 *
	 * @return Settings_PublicHoliday_Module_Model
	 */
	public static function getInstance($name = 'Settings:PublicHoliday')
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $name);
		return new $modelClassName();
	}

	/**
	 * Returns holidays into date range.
	 *
	 * @param array $range
	 * @param int   $sorting SORT_ASC|SORT_DESC
	 *
	 * @return Settings_PublicHoliday_Record_Model[]
	 */
	public function getHolidaysByRange(array $range, int $sorting = SORT_ASC)
	{
		$holidays = [];
		$query = (new \App\Db\Query())->select($this->getBaseIndex())->from($this->getBaseTable());
		if ($range) {
			$query->where(['between', 'holidaydate', $range[0], $range[1]]);
		}
		$query->orderBy(['holidaydate' => $sorting]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$id = $row[$this->getBaseIndex()];
			$holidays[$id] = Settings_PublicHoliday_Record_Model::getInstanceById($id);
		}
		$dataReader->close();
		return $holidays;
	}
}
