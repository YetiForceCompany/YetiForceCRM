<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_ModuleManager_Module_Model extends Vtiger_Module_Model {

    public static function getNonVisibleModulesList() {
        return array('ModTracker','Portal','Users','Mobile','Integration','WSAPP',
                     'ConfigEditor','FieldFormulas','VtigerBackup','CronTasks','Import' ,'Tooltip',
                    'CustomerPortal', 'Home');
    }


    /**
     * Function to get the url of new module import
     */
    public static function getNewModuleImportUrl() {
		return 'index.php?module=ModuleManager&parent=Settings&view=ModuleImport';
    }
	
	 /**
     * Function to get the url of new module import 
     */
    public static function getUserModuleImportUrl() {
		return 'index.php?module=ModuleManager&parent=Settings&view=ModuleImport&mode=importUserModuleStep1';
	}
	
	/**
     * Function to disable a module 
     * @param type $moduleName - name of the module
     */
    public function disableModule($moduleName) {
		//Handling events after disable module
		vtlib_toggleModuleAccess($moduleName, false);
    }

    /**
     * Function to enable the module
     * @param type $moduleName -- name of the module
     */
    public function enableModule($moduleName) {
		//Handling events after enable module
		vtlib_toggleModuleAccess($moduleName, true);
    }


	/**
	 * Static Function to get the instance of Vtiger Module Model for all the modules
	 * @return <Array> - List of Vtiger Module Model or sub class instances
	 */
	public static function getAll() {
		 return parent::getAll(array(0,1), self::getNonVisibleModulesList());
	}

    /**
     * Function which will get count of modules
     * @param <Boolean> $onlyActive - if true get count of only active modules else all the modules
     * @return <integer> number of modules
     */
    public static function getModulesCount($onlyActive = false) {
        $db = PearDatabase::getInstance();

        $query = 'SELECT * FROM vtiger_tab';
		$params = array();
		if($onlyActive) {
            $presence = array(0);
            $nonVisibleModules = self::getNonVisibleModulesList();
			$query .= ' WHERE presence IN ('. generateQuestionMarks($presence) .')';
            $query .= ' AND name NOT IN ('.generateQuestionMarks($nonVisibleModules).')';
			array_push($params, $presence,$nonVisibleModules);
		}
        $result = $db->pquery($query, $params);
		return $db->num_rows($result);
    }

	/**
	 * Function that returns all those modules that support Module Sequence Numbering
	 * @global PearDatabase $db - database connector
	 * @return <Array of Vtiger_Module_Model>
	 */
	public static function getModulesSupportingSequenceNumbering() {
		$db = PearDatabase::getInstance();
		$sql="SELECT tabid, name FROM vtiger_tab WHERE isentitytype = 1 AND presence = 0 AND tabid IN
			(SELECT DISTINCT tabid FROM vtiger_field WHERE uitype = '4')";
		$result = $db->pquery($sql, array());

		$moduleModels = array();
		for($i=0; $i<$db->num_rows($result); ++$i) {
			$row =  $db->query_result_rowdata($result, $i);
			$moduleModels[$row['name']] = self::getInstanceFromArray($row);
		}
		return $moduleModels;
	}

	/**
	 * Function to get restricted modules list
	 * @return <Array> List module names
	 */
	public static function getActionsRestrictedModulesList() {
		return array('Home', 'Emails');
	}
}
