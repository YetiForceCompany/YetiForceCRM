<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class ModTracker_Relation_Model extends Vtiger_Record_Model
{

	public function getValue()
	{
		return $this->getLinkedRecord()->getName();
	}

	public function setParent($parent)
	{
		$this->parent = $parent;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function getLinkedRecord()
	{
		$db = PearDatabase::getInstance();

		$targetId = $this->get('targetid');
		$targetModule = $this->get('targetmodule');

		$query = 'SELECT * FROM vtiger_crmentity WHERE crmid = ?';
		$result = $db->pquery($query, [$targetId]);
		$noOfRows = $db->num_rows($result);
		$moduleModels = [];
		if ($noOfRows) {
			$moduleModel = Vtiger_Module_Model::getInstance($targetModule);
			$row = $db->getRow($result);
			$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $targetModule);
			$recordInstance = new $modelClassName();
			$recordInstance->setData($row)->setModuleFromInstance($moduleModel);
			$recordInstance->set('id', $row['crmid']);
			return $recordInstance;
		}
		return false;
	}

	/**
	 * Function adds records to task queue that updates reviewing changes in records
	 * @param <array> $data - List of records to update
	 * @param <string> $module - Module name
	 */
	public static function reviewChangesQueue($data, $module)
	{
		$db = \PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$id = $db->getUniqueID('u_yf_reviewed_queue');
		$db->insert('u_yf_reviewed_queue', [
			'id' => $id,
			'userid' => $currentUserModel->getRealId(),
			'tabid' => \vtlib\Functions::getModuleId($module),
			'data' => \includes\utils\Json::encode($data),
			'time' => date('Y-m-d H:i:s')
		]);
	}

	/**
	 * Function marks forwarded records as reviewed
	 * @param <array> $recordsList - List of records to update
	 * @param <integer> $userId - User id
	 */
	public static function reviewChanges($recordsList, $userId = false)
	{
		foreach ($recordsList as $record) {
			$result = ModTracker_Record_Model::setLastReviewed($record);
			ModTracker_Record_Model::unsetReviewed($record, $userId, $result);
		}
	}
}
