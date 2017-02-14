<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Vtiger Edit View Record Structure Model
 */
class Reports_RecordStructure_Model extends Vtiger_RecordStructure_Model
{

	protected $moduleName = false;

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure()
	{
		$moduleName = $this->moduleName;
		if (!empty($this->structuredValues[$moduleName])) {
			return $this->structuredValues[$moduleName];
		}
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if ($moduleName === 'Calendar') {
			$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
			$moduleRecordStructure = array();
			$calendarRecordStructure = $recordStructureInstance->getStructure();

			$eventsModel = Vtiger_Module_Model::getInstance('Events');
			$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($eventsModel);
			$eventRecordStructure = $recordStructureInstance->getStructure();

			$blockLabel = 'LBL_CUSTOM_INFORMATION';
			if ($eventRecordStructure[$blockLabel]) {
				if ($calendarRecordStructure[$blockLabel]) {
					$calendarRecordStructure[$blockLabel] = array_merge($calendarRecordStructure[$blockLabel], $eventRecordStructure[$blockLabel]);
				} else {
					$calendarRecordStructure[$blockLabel] = $eventRecordStructure[$blockLabel];
				}
			}
			$moduleRecordStructure = $calendarRecordStructure;
		} else {
			$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
			$moduleRecordStructure = $recordStructureInstance->getStructure();
		}
		$this->structuredValues[$moduleName] = $moduleRecordStructure;
		return $moduleRecordStructure;
	}

	/**
	 * Function returns the Primary Module Record Structure
	 * @return <Vtiger_RecordStructure_Model>
	 */
	public function getPrimaryModuleRecordStructure()
	{
		$this->moduleName = $this->getRecord()->getPrimaryModule();
		$primaryModuleRecordStructure = $this->getStructure();
		return $primaryModuleRecordStructure;
	}

	/**
	 * Function returns the Secondary Modules Record Structure
	 * @return <Array of Vtiger_RecordSructure_Models>
	 */
	public function getSecondaryModuleRecordStructure()
	{
		$recordStructureInstances = array();

		$secondaryModule = $this->getRecord()->getSecondaryModules();
		if (!empty($secondaryModule)) {
			$moduleList = explode(':', $secondaryModule);

			foreach ($moduleList as $moduleName) {
				if (!empty($moduleName)) {
					$this->moduleName = $moduleName;
					$recordStructureInstances[$moduleName] = $this->getStructure();
				}
			}
		}
		return $recordStructureInstances;
	}
}
