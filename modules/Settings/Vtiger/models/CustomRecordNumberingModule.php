<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Vtiger_CustomRecordNumberingModule_Model extends Vtiger_Module_Model
{

	/**
	 * Function to get focus of this object
	 * @return <type>
	 */
	public function getFocus()
	{
		if (!isset($this->focus)) {
			$this->focus = CRMEntity::getInstance($this->getName());
		}
		return $this->focus;
	}

	/**
	 * Function to get Instance of this module
	 * @param string $moduleName
	 * @return <Settings_Vtiger_CustomRecordNumberingModule_Model> $moduleModel
	 */
	public static function getInstance($moduleName, $tabId = false)
	{
		$moduleModel = new self();
		$moduleModel->name = $moduleName;
		if ($tabId) {
			$moduleModel->id = $tabId;
		}
		return $moduleModel;
	}

	/**
	 * Function to ger Supported modules for Custom record numbering
	 * @return <Array> list of supported modules Vtiger_Module_Model
	 */
	public static function getSupportedModules()
	{
		$db = PearDatabase::getInstance();

		$sql = 'SELECT tabid, name FROM vtiger_tab WHERE isentitytype = ? AND presence = ? AND tabid IN (SELECT DISTINCT tabid FROM vtiger_field WHERE uitype = ?);';
		$result = $db->pquery($sql, [1, 0, 4]);
		$numOfRows = $db->num_rows($result);

		for ($i = 0; $i < $numOfRows; $i++) {
			$tabId = $db->query_result($result, $i, 'tabid');
			$modulesModels[$tabId] = Settings_Vtiger_CustomRecordNumberingModule_Model::getInstance($db->query_result($result, $i, 'name'), $tabId);
		}

		return $modulesModels;
	}

	/**
	 * Function to set Module sequence
	 * @return <Array> result of success
	 */
	public function setModuleSequence()
	{
		$prefix = $this->get('prefix');
		$postfix = $this->get('postfix');
		$tabId = \App\Module::getModuleId($this->getName());
		$status = \App\Fields\RecordNumber::setNumber($tabId, $prefix, $this->get('sequenceNumber'), $postfix);
		$success = ['success' => $status];
		if (!$status) {
			$success['sequenceNumber'] = (new App\Db\Query())->select(['cur_id'])
				->from('vtiger_modentity_num')
				->where(['tabid' => $tabId, 'prefix' => $prefix, 'postfix' => $postfix])
				->scalar();
		}
		return $success;
	}

	/**
	 * Function to update record sequences which are under this module
	 * @return <Array> result of success
	 */
	public function updateRecordsWithSequence()
	{
		return $this->getFocus()->updateMissingSeqNumber($this->getName());
	}
}
