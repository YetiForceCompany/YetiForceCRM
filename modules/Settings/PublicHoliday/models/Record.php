<?php

/**
 * Settings PublicHoliday record model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_PublicHoliday_Record_Model extends Settings_Vtiger_Record_Model
{
    /**
     * Module model instance
     * 
     * @var Settings_PublicHoliday_Module_Model
     */
    protected $module = null;

    /**
	 * Returns record id
	 *
     * @param none
	 * @return int
	 */
	public function getId()
	{
		return (int) $this->get('publicholidayid');
	}

	/**
	 * Returns holiday name
	 *
     * @param none
	 * @return string
	 */
	public function getName()
	{
		return $this->get('holidayname');
    }
    
    /**
	 * Returns holiday type
	 *
     * @param none
	 * @return string
	 */
	public function getType()
	{
		return $this->get('holidaytype');
    }

    /**
	 * Returns holiday date
	 *
     * @param none
	 * @return string
	 */
	public function getDate()
	{
		return $this->get('holidaydate');
    }

    /**
	 * Sets and returns module model instance
	 *
     * @param none
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
	 * Returns a clean instance
	 *
	 * @param none
	 * @return \self
	 */
	public static function getCleanInstance()
	{
		$instance = new self();
		return $instance;
	}

    /**
	 * {@inheritdoc}
	 */
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
	 * Return day of week for holiday date
	 * 
	 * @param none
	 * @return string
	 */
	public function getDayOfWeek()
	{
		$moduleModel = $this->getModule();
        $moduleName = $moduleModel->getName();
		$date = $this->getDate();
		$dow = date('l', strtotime($date));
		return $dow;
	}

    /**
	 * Returns display value
	 *
	 * @param string
	 * @return string
	 */
	public function getDisplayValue(string $key)
	{
        $moduleModel = $this->getModule();
        $moduleName = $moduleModel->getName();
		$value = $this->get($key);
		switch ($key) {
            case 'holidaydate':
				$displayValue = DateTimeField::convertToUserFormat($value);
                break;
                
			case 'holidaytype':
				$displayValue = \App\Language::translate($value, $moduleName);
				break;
			
            default:
				$displayValue = $value;
				break;
		}
		return $displayValue;
	}

    /**
	 * Updates / inserts record
	 *
     * @param none
	 * @return int
	 */
	public function save()
	{
		$moduleModel = $this->getModule();
        $moduleTable = $moduleModel->getBaseTable();
        $tableIndex = $moduleModel->getBaseIndex();
		$publicholidayid = $this->getId();
		$recordValues = [
			'holidaydate' => $this->getDate(),
			'holidayname' => $this->getName(),
			'holidaytype' => $this->getType(),
		];
		$result = 0;
		$db = \App\Db::getInstance('admin');
		if ($publicholidayid) {
			$result = $db->createCommand()
						->update($moduleTable, $recordValues, [$tableIndex => $publicholidayid])
						->execute();
		} else {
			$result = $db->createCommand()
						->insert($moduleTable, $recordValues)
						->execute();
			$this->set($tableIndex, $db->getLastInsertID($moduleTable));
		}
		return $result;
	}

    /**
	 * Deletes record
     * 
     * @param none
     * @return int
	 */
	public function delete()
	{
        $moduleModel = $this->getModule();
        $moduleTable = $moduleModel->getBaseTable();
        $tableIndex = $moduleModel->getBaseIndex();
		$publicholidayid = $this->getId();
		$result = 0;
		if ($publicholidayid) {
			$db = \App\Db::getInstance('admin');
			$result = $db->createCommand()
						->delete($moduleTable, [$tableIndex => $publicholidayid])
						->execute();
		}
		return $result;
	}
}
