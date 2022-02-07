<?php

/**
 * Settings PublicHoliday record model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_PublicHoliday_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Module model instance.
	 *
	 * @var Settings_PublicHoliday_Module_Model
	 */
	protected $module;

	/**
	 * Returns record id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return (int) $this->get('publicholidayid');
	}

	/**
	 * Returns holiday name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('holidayname');
	}

	/**
	 * Returns holiday type.
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->get('holidaytype');
	}

	/**
	 * Returns holiday date.
	 *
	 * @return string
	 */
	public function getDate()
	{
		return $this->get('holidaydate');
	}

	/**
	 * Sets and returns module model instance.
	 *
	 * @return Settings_Companies_Module_Model
	 */
	public function getModule()
	{
		if (!isset($this->module)) {
			$this->module = Settings_PublicHoliday_Module_Model::getInstance();
		}
		return $this->module;
	}

	/**
	 * Returns a clean instance.
	 *
	 * @return \self
	 */
	public static function getCleanInstance()
	{
		return new self();
	}

	/** {@inheritdoc} */
	public static function getInstanceById($id)
	{
		$moduleModel = Settings_PublicHoliday_Module_Model::getInstance();
		$tableName = $moduleModel->getBaseTable();
		$tableIndex = $moduleModel->getBaseIndex();
		$query = new App\Db\Query();
		$row = $query->from($tableName)
			->where([$tableIndex => $id])
			->createCommand()->queryOne();
		if ($row) {
			$instance = new self();
			$instance->setData($row);
			return $instance;
		}
		return null;
	}

	/**
	 * Return day of week for holiday date.
	 *
	 * @return string
	 */
	public function getDayOfWeek()
	{
		return date('l', strtotime($this->getDate()));
	}

	/**
	 * Returns display value.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getDisplayValue(string $key)
	{
		$value = $this->get($key);
		switch ($key) {
			case 'holidaydate':
				$displayValue = DateTimeField::convertToUserFormat($value);
				break;
			case 'holidaytype':
				$displayValue = \App\Language::translate($value, 'Settings:PublicHoliday');
				break;
			default:
				$displayValue = $value;
				break;
		}
		return $displayValue;
	}

	/**
	 * Updates / inserts record.
	 *
	 * @return int
	 */
	public function save()
	{
		$moduleModel = $this->getModule();
		$moduleTable = $moduleModel->getBaseTable();
		$tableIndex = $moduleModel->getBaseIndex();
		$publicHolidayId = $this->getId();
		$recordValues = [
			'holidaydate' => $this->getDate(),
			'holidayname' => $this->getName(),
			'holidaytype' => $this->getType(),
		];
		$result = 0;
		$db = \App\Db::getInstance('admin');
		if ($publicHolidayId) {
			$result = $db->createCommand()
				->update($moduleTable, $recordValues, [$tableIndex => $publicHolidayId])
				->execute();
		} else {
			$result = $db->createCommand()
				->insert($moduleTable, $recordValues)
				->execute();
			$this->set($tableIndex, $db->getLastInsertID("{$moduleTable}_publicholidayid_seq"));
		}
		return $result;
	}

	/**
	 * Check if is duplicated.
	 *
	 * @return bool
	 */
	public function isDuplicate()
	{
		$query = (new \App\Db\Query())->from($this->getModule()->getBaseTable())->where(['holidaydate' => $this->getDate()]);
		if ($this->getId()) {
			$query->andWhere(['<>', 'publicholidayid', $this->getId()]);
		}
		return $query->exists();
	}

	/**
	 * Deletes record.
	 *
	 * @return int
	 */
	public function delete()
	{
		$result = 0;
		if ($this->getId()) {
			$result = \App\Db::getInstance('admin')->createCommand()
				->delete($this->getModule()->getBaseTable(), [$this->getModule()->getBaseIndex() => $this->getId()])
				->execute();
		}
		return $result;
	}
}
