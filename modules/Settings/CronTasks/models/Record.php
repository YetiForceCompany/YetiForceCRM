<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Settings_CronTasks_Record_Model extends Settings_Vtiger_Record_Model
{
	public static $STATUS_DISABLED = 0;
	public static $STATUS_ENABLED = 1;
	public static $STATUS_RUNNING = 2;
	public static $STATUS_COMPLETED = 3;

	/**
	 * Function to get Id of this record instance.
	 *
	 * @return <Integer> id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get Name of this record.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * Function to get module instance of this record.
	 *
	 * @return <type>
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to set module to this record instance.
	 *
	 * @param <Settings_CronTasks_Module_Model> $moduleModel
	 *
	 * @return <Settings_CronTasks_Record_Model> record model
	 */
	public function setModule($moduleModel)
	{
		$this->module = $moduleModel;
		return $this;
	}

	public function isDisabled()
	{
		if ($this->get('status') == self::$STATUS_DISABLED) {
			return true;
		}
		return false;
	}

	public function isRunning()
	{
		if ($this->get('status') == self::$STATUS_RUNNING) {
			return true;
		}
		return false;
	}

	public function isCompleted()
	{
		if ($this->get('status') == self::$STATUS_COMPLETED) {
			return true;
		}
		return false;
	}

	public function isEnabled()
	{
		if ($this->get('status') == self::$STATUS_ENABLED) {
			return true;
		}
		return false;
	}

	/**
	 * Detect if the task was started and never finished.
	 *
	 * @return bool
	 */
	public function hadTimedout()
	{
		$maxTaskTime = $this->get('max_exe_time');
		$lastEnd = (int) $this->get('lastend');
		$lastStart = (int) $this->get('laststart');
		if ($lastEnd < $lastStart && !$this->isRunning()) {
			return true;
		}
		if ($lastEnd < $lastStart && $this->isRunning()) {
			if (time() > ($lastStart + \App\Cron::getMaxExecutionTime())) {
				return true;
			}
			if (!empty($maxTaskTime) && time() > ($lastStart + ($maxTaskTime * 60))) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get the user datetimefeild.
	 */
	public function getLastEndDateTime()
	{
		if (null !== $this->get('lastend')) {
			$lastScannedTime = App\Fields\DateTime::formatToDisplay(date('Y-m-d H:i:s', $this->get('lastend')));
			$hourFormat = \App\User::getCurrentUserModel()->getDetail('hour_format');
			if ('24' == $hourFormat) {
				return $lastScannedTime;
			}
			$dateTimeList = explode(' ', $lastScannedTime);

			return $dateTimeList[0] . ' ' . date('g:i:sa', strtotime($dateTimeList[1]));
		}
		return '';
	}

	/**
	 * Get Time taken to complete task.
	 *
	 * @return int seconds
	 */
	public function getTimeDiff()
	{
		return (int) ($this->get('lastend')) - (int) ($this->get('laststart'));
	}

	/**
	 * Get cron operation duration.
	 *
	 * @return string duration string or 'running','timeout'
	 */
	public function getDuration()
	{
		$lastStart = (int) $this->get('laststart');
		if (!$lastStart) {
			$duration = '-';
		} elseif ($this->isRunning() && !$this->hadTimedout()) {
			$duration = 'running';
		} elseif ($this->hadTimedout()) {
			$duration = 'timeout';
		} else {
			$duration = \App\Fields\RangeTime::displayElapseTime((int) $this->get('lastend') - $lastStart, 's', 's');
		}
		return $duration;
	}

	/**
	 * Function to get display value of every field from this record.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getDisplayValue(string $key)
	{
		$fieldValue = $this->get($key);
		switch ($key) {
			case 'frequency':
				$fieldValue = (int) $fieldValue;
				$hours = str_pad((int) (($fieldValue / (60 * 60))), 2, 0, STR_PAD_LEFT);
				$minutes = str_pad((int) (($fieldValue % (60 * 60)) / 60), 2, 0, STR_PAD_LEFT);
				$fieldValue = $hours . ':' . $minutes;
				break;
			case 'status':
				$fieldValue = (int) $fieldValue;
				$moduleModel = $this->getModule();
				if ($fieldValue === self::$STATUS_COMPLETED) {
					$fieldLabel = 'LBL_COMPLETED';
				} elseif ($fieldValue === self::$STATUS_RUNNING) {
					$fieldLabel = 'LBL_RUNNING';
				} elseif ($fieldValue === self::$STATUS_ENABLED) {
					$fieldLabel = 'LBL_ACTIVE';
				} else {
					$fieldLabel = 'LBL_INACTIVE';
				}
				$fieldValue = \App\Language::translate($fieldLabel, $moduleModel->getName(true));
				break;
			case 'laststart':
			case 'last_update':
			case 'lastend':
				$fieldValue = (int) $fieldValue;
				if ($fieldValue) {
					$fieldValue = \App\Fields\DateTime::formatToViewDate(date('Y-m-d H:i:s', $fieldValue));
				} else {
					$fieldValue = '';
				}
				break;
			case 'name':
				$fieldValue = \App\Language::translate($fieldValue, $this->getModule()->getName(true));
				break;
			case 'duration':
				$fieldValue = $this->getDuration();
				break;
			default:
				break;
		}
		return $fieldValue;
	}

	// Function to get Edit view url

	public function getEditViewUrl()
	{
		return 'module=CronTasks&parent=Settings&view=EditAjax&record=' . $this->getId();
	}

	/**
	 * Function to save the record.
	 */
	public function save()
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_cron_task', ['frequency' => $this->get('frequency'), 'status' => $this->get('status')], ['id' => $this->getId()])
			->execute();
	}

	/**
	 * Function to get record instance by using id and moduleName.
	 *
	 * @param <Integer> $recordId
	 * @param string    $qualifiedModuleName
	 *
	 * @return <Settings_CronTasks_Record_Model> RecordModel
	 */
	public static function getInstanceById($recordId, $qualifiedModuleName)
	{
		if (empty($recordId)) {
			return false;
		}
		$row = (new \App\Db\Query())
			->from('vtiger_cron_task')
			->where(['id' => $recordId])
			->one();
		if ($row) {
			$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);
			$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
			$recordModel = new $recordModelClass();
			$recordModel->setData($row)->setModule($moduleModel);

			return $recordModel;
		}
		return false;
	}

	public static function getInstanceByName($name)
	{
		$query = (new \App\Db\Query())
			->from('vtiger_cron_task')
			->where(['name' => $name]);
		$row = $query->createCommand()->queryOne();
		if ($row) {
			$moduleModel = new Settings_CronTasks_Module_Model();
			$recordModel = new self();
			$recordModel->setData($row)->setModule($moduleModel);

			return $recordModel;
		}
		return false;
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];

		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => "javascript:Settings_CronTasks_List_Js.triggerEditEvent('" . $this->getEditViewUrl() . "')",
				'linkicon' => 'yfi yfi-full-editing-view',
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	public function getMinimumFrequency()
	{
		$frequency = App\Config::main('MINIMUM_CRON_FREQUENCY');
		if (!empty($frequency)) {
			return $frequency * 60;
		}
		return 60;
	}
}
