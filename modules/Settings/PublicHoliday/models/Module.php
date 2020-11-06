<?php

/**
 * Settings PublicHoliday module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * Return info about module paging feature
	 * 
	 * @param none
	 * @return boolean - true: pageable, false: not pageable
	 */
	public function isPagingSupported()
	{
		return false;
	}

	/**
	 * Returns instance of Settings module model
	 *
	 * @param mixed $name
	 * @return Settings_PublicHoliday_Module_Model
	 */
	public static function getInstance($name = 'Settings:PublicHoliday')
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $name);
		return new $modelClassName();
	}

	/**
	 * Returns holiday by date
	 * 
	 * @param string
	 * @return array Settings_PublicHoliday_Record_Model
	 */
	public function getHolidayByDate($date)
	{
		$holidays = [];
		if (\App\Cache::has('PublicHoliday::getHolidayByDate', $date)) {
			$holidays = \App\Cache::get('PublicHoliday::getHolidayByDate', $date);
		} else {
			$query = new \App\Db\Query();
			$query->select($this->getBaseIndex());
			$query->from($this->getBaseTable());
			$query->where(['holidaydate' => $date]);
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$id = $row[$this->getBaseIndex()];
				$holidays[$id] = Settings_PublicHoliday_Record_Model::getInstanceById($id);
			}
			$dataReader->close();
			\App\Cache::save('PublicHoliday::getHolidayByDate', $date, $holidays);
		}
		return $holidays;
	}

	/**
	 * Returns holidays into date range
	 *
	 * @param array
	 * @param string SORT_ASC|SORT_DESC
	 * @return array Settings_PublicHoliday_Record_Model
	 */
	public function getHolidaysByRange($range = [], $sorting = SORT_ASC)
	{
		$start = is_array($range) && count($range) == 2 ? $range[0] : '';
		$end = is_array($range) && count($range) == 2 ? $range[1] : '';
		if (!in_array($sorting, [SORT_ASC, SORT_DESC])) {
			$sorting = SORT_ASC;
		}
		$holidays = [];
		if (\App\Cache::has('PublicHoliday::getHolidaysByRange', $start . $end)) {
			$holidays = \App\Cache::get('PublicHoliday::getHolidaysByRange', $start . $end);
		} else {
			$query = new \App\Db\Query();
			$query->select($this->getBaseIndex());
			$query->from($this->getBaseTable());
			if ($start && $end) {
				$query->where(['between', 'holidaydate', $start, $end]);
			}
			$query->orderBy(['holidaydate' => $sorting]);
			$dataReader = $query->createCommand()->query();
			while ($row = $dataReader->read()) {
				$id = $row[$this->getBaseIndex()];
				$holidays[$id] = Settings_PublicHoliday_Record_Model::getInstanceById($id);
			}
			$dataReader->close();
			\App\Cache::save('PublicHoliday::getHolidaysByRange', $start . $end, $holidays);
		}
		return $holidays;
	}
}
