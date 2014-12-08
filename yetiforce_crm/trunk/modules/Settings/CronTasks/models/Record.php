<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_CronTasks_Record_Model extends Settings_Vtiger_Record_Model {

	static $STATUS_DISABLED = 0;
    static $STATUS_ENABLED = 1;
    static $STATUS_RUNNING = 2;
	static $STATUS_COMPLETED = 3;

	/**
	 * Function to get Id of this record instance
	 * @return <Integer> id
	 */
	public function getId() {
		return $this->get('id');
	}

	/**
	 * Function to get Name of this record
	 * @return <String>
	 */
	public function getName() {
		return $this->get('name');
	}

	/**
	 * Function to get module instance of this record
	 * @return <type>
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Function to set module to this record instance
	 * @param <Settings_CronTasks_Module_Model> $moduleModel
	 * @return <Settings_CronTasks_Record_Model> record model
	 */
	public function setModule($moduleModel) {
		$this->module = $moduleModel;
		return $this;
	}

    public function isDisabled() {
        if($this->get('status') == self::$STATUS_DISABLED){
            return true;
        }
        return false;
    }
    
    public function isRunning() {
        if($this->get('status') == self::$STATUS_RUNNING){
            return true;
        }
        return false;
    }
    
    public function isCompleted() {
        if($this->get('status') == self::$STATUS_COMPLETED){
            return true;
        }
        return false;
    }
    
    public function isEnabled() {
        if($this->get('status') == self::$STATUS_ENABLED){
            return true;
        }
        return false;
    }
    
    /**
     * Detect if the task was started by never finished.
     */
    function hadTimedout() {
        if($this->get('lastend') === 0 && $this->get('laststart') != 0)
        return intval($this->get('lastend'));
    }
    
    /**
     * Get the user datetimefeild
     */
    function getLastEndDateTime() {
        if($this->get('lastend') != NULL) {
		    $lastScannedTime = Vtiger_Datetime_UIType::getDisplayDateTimeValue(date('Y-m-d H:i:s', $this->get('lastend')));
		    $userModel = Users_Record_Model::getCurrentUserModel();
			$hourFormat = $userModel->get('hour_format');
		    if($hourFormat == '24') {
				return $lastScannedTime;
		    } else {
				$dateTimeList = explode(" ", $lastScannedTime);
                return $dateTimeList[0]." ".date('g:i:sa', strtotime($dateTimeList[1]));
			}
		} else {
			return '';
		}
    }
    
    /**
     * Get Time taken to complete task
     */
    function getTimeDiff() {
        $lastStart = intval($this->get('laststart'));
        $lastEnd   = intval($this->get('lastend'));
        $timeDiff  = $lastEnd - $lastStart;
        return $timeDiff;
    }

	/**
	 * Function to get display value of every field from this record
	 * @param <String> $fieldName
	 * @return <String>
	 */
	public function getDisplayValue($fieldName) {
		$fieldValue = $this->get($fieldName);
		switch ($fieldName) {
			case 'frequency'	: $fieldValue = intval($fieldValue);
								  $hours	= str_pad((int)(($fieldValue/(60*60))),2,0,STR_PAD_LEFT);
								  $minutes	= str_pad((int)(($fieldValue%(60*60))/60),2,0,STR_PAD_LEFT);
								  $fieldValue = $hours.':'.$minutes;
								  break;
			case 'status'		: $fieldValue = intval($fieldValue);
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
								  $fieldValue = vtranslate($fieldLabel, $moduleModel->getParentName().':'.$moduleModel->getName());
								  break;
			case 'laststart'	:
			case 'lastend'		: $fieldValue = intval($fieldValue);
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
	public function getEditViewUrl() {
		return 'module=CronTasks&parent=Settings&view=EditAjax&record='.$this->getId();
	}

	/**
	 * Function to save the record
	 */
	public function save() {
		$db = PearDatabase::getInstance();

		$updateQuery = "UPDATE vtiger_cron_task SET frequency = ?, status = ? WHERE id = ?";
		$params = array($this->get('frequency'), $this->get('status'), $this->getId());
		$db->pquery($updateQuery, $params);
	}

	/**
	 * Function to get record instance by using id and moduleName
	 * @param <Integer> $recordId
	 * @param <String> $qualifiedModuleName
	 * @return <Settings_CronTasks_Record_Model> RecordModel
	 */
	static public function getInstanceById($recordId, $qualifiedModuleName) {
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT * FROM vtiger_cron_task WHERE id = ?", array($recordId));
		if ($db->num_rows($result)) {
			$recordModelClass = Vtiger_Loader::getComponentClassName('Model', 'Record', $qualifiedModuleName);
			$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
			$rowData = $db->query_result_rowdata($result, 0);
			$recordModel = new $recordModelClass();
			$recordModel->setData($rowData)->setModule($moduleModel);
			return $recordModel;
		}
		return false;
	}
	
    public static function getInstanceByName($name) {
        $db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT * FROM vtiger_cron_task WHERE name = ?", array($name));
		if ($db->num_rows($result)) {
			$moduleModel = new Settings_CronTasks_Module_Model();
			$rowData = $db->query_result_rowdata($result, 0);
			$recordModel = new self();
			$recordModel->setData($rowData)->setModule($moduleModel);
			return $recordModel;
		}
		return false;
    }


		/**
	 * Function to get the list view actions for the record
	 * @return <Array> - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks() {

		$links = array();

		$recordLinks = array(
			array(
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => "javascript:Settings_CronTasks_List_Js.triggerEditEvent('".$this->getEditViewUrl()."')",
				'linkicon' => 'icon-pencil'
			)
		);
		foreach($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}
	
	public function getMinimumFrequency() {
		return getMinimumCronFrequency()*60;
	}
}