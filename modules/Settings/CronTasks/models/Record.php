<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_CronTasks_Record_Model extends Settings_Vtiger_Record_Model
{

	static $STATUS_DISABLED = 0;
	static $STATUS_ENABLED = 1;
	static $STATUS_RUNNING = 2;
	static $STATUS_COMPLETED = 3;

	/**
	 * Function to get Id of this record instance
	 * @return <Integer> id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get Name of this record
	 * @return string
	 */
	public function getName()
	{
		return $this->get('name');
	}

	/**
	 * Function to get module instance of this record
	 * @return <type>
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to set module to this record instance
	 * @param <Settings_CronTasks_Module_Model> $moduleModel
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
	 * Detect if the task was started by never finished.
	 */
	public function hadTimedout()
	{
		if ($this->get('lastend') === 0 && $this->get('laststart') != 0)
			return intval($this->get('lastend'));
	}

	/**
	 * Get the user datetimefeild
	 */
	public function getLastEndDateTime()
	{
		if ($this->get('lastend') != NULL) {
			$lastScannedTime = Vtiger_Datetime_UIType::getDisplayDateTimeValue(date('Y-m-d H:i:s', $this->get('lastend')));
			$userModel = Users_Record_Model::getCurrentUserModel();
			$hourFormat = $userModel->get('hour_format');
			if ($hourFormat == '24') {
				return $lastScannedTime;
			} else {
				$dateTimeList = explode(" ", $lastScannedTime);
				return $dateTimeList[0] . " " . date('g:i:sa', strtotime($dateTimeList[1]));
			}
		} else {
			return '';
		}
	}

	/**
	 * Get Time taken to complete task
	 */
	public function getTimeDiff()
	{
		$lastStart = intval($this->get('laststart'));
		$lastEnd = intval($this->get('lastend'));
		$timeDiff = $lastEnd - $lastStart;
		return $timeDiff;
	}

	/**
	 * Function to get display value of every field from this record
	 * @param string $fieldName
	 * @return string
	 */
	public function getDisplayValue($fieldName)
	{
		$fieldValue = $this->get($fieldName);
		switch ($fieldName) {
			case 'frequency' : $fieldValue = intval($fieldValue);
				$hours = str_pad((int) (($fieldValue / (60 * 60))), 2, 0, STR_PAD_LEFT);
				$minutes = str_pad((int) (($fieldValue % (60 * 60)) / 60), 2, 0, STR_PAD_LEFT);
				$fieldValue = $hours . ':' . $minutes;
				break;
			case 'status' : $fieldValue = intval($fieldValue);
				$moduleModel = $this->getModule();
				if ($fieldValue === Settings_CronTasks_Record_Model::$STATUS_COMPLETED) {
					$fieldLabel = 'LBL_COMPLETED';
				} else if ($fieldValue === Settings_CronTasks_Record_Model::$STATUS_RUNNING) {
					$fieldLabel = 'LBL_RUNNING';
				} else if ($fieldValue === Settings_CronTasks_Record_Model::$STATUS_ENABLED) {
					$fieldLabel = 'LBL_ACTIVE';
				} else {
					$fieldLabel = 'LBL_INACTIVE';
				}
				$fieldValue = vtranslate($fieldLabel, $moduleModel->getParentName() . ':' . $moduleModel->getName());
				break;
			case 'laststart' :
			case 'lastend' : $fieldValue = intval($fieldValue);
				if ($fieldValue) {
					$fieldValue = dateDiffAsString($fieldValue, time());
				} else {
					$fieldValue = '';
				}
				break;
		}
		return $fieldValue;
	}
	/*
	 * Function to get Edit view url 
	 */

	public function getEditViewUrl()
	{
		return 'module=CronTasks&parent=Settings&view=EditAjax&record=' . $this->getId();
	}

	/**
	 * Function to save the record
	 */
	public function save()
	{
		\App\Db::getInstance()->createCommand()->update('vtiger_cron_task', ['frequency' => $this->get('frequency'), 'status' => $this->get('status')], ['id' => $this->getId()])
			->execute();
	}

	/**
	 * Function to get record instance by using id and moduleName
	 * @param <Integer> $recordId
	 * @param string $qualifiedModuleName
	 * @return <Settings_CronTasks_Record_Model> RecordModel
	 */
	static public function getInstanceById($recordId, $qualifiedModuleName)
	{
		if (empty($recordId))
			return false;
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

	/**
	 * Function to get the list view actions for the record
	 * @return <Array> - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{

		$links = array();

		$recordLinks = array(
			array(
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => "javascript:Settings_CronTasks_List_Js.triggerEditEvent('" . $this->getEditViewUrl() . "')",
				'linkicon' => 'glyphicon glyphicon-pencil'
			)
		);
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	public function getMinimumFrequency()
	{
		$frequency = AppConfig::main('MINIMUM_CRON_FREQUENCY');
		if (!empty($frequency)) {
			return $frequency * 60;
		}
		return 60;
	}
}
