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

	/**
	 * Function return link to record.
	 *
	 * @return bool|\Vtiger_Record_Model
	 */
	public function getLinkedRecord()
	{
		$targetId = $this->get('targetid');
		$targetModule = $this->get('targetmodule');
		$row = (new \App\Db\Query())->from('vtiger_crmentity')->where(['crmid' => $targetId])->one();
		if ($row) {
			$moduleModel = Vtiger_Module_Model::getInstance($targetModule);
			$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $targetModule);
			$recordInstance = new $modelClassName();
			$recordInstance->setData($row)->setModuleFromInstance($moduleModel);
			$recordInstance->setId($row['crmid']);

			return $recordInstance;
		}
		return false;
	}

	/**
	 * Function adds records to task queue that updates reviewing changes in records.
	 *
	 * @param array  $data   - List of records to update
	 * @param string $module - Module name
	 */
	public static function reviewChangesQueue($data, $module)
	{
		$db = \App\Db::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$id = (new \App\Db\Query())->from('u_#__reviewed_queue')->max('id') + 1;
		$db->createCommand()->insert('u_#__reviewed_queue', [
			'id' => $id,
			'userid' => $currentUserModel->getRealId(),
			'tabid' => \App\Module::getModuleId($module),
			'data' => \App\Json::encode($data),
			'time' => date('Y-m-d H:i:s'),
		])->execute();
	}

	/**
	 * Function marks forwarded records as reviewed.
	 *
	 * @param array $recordsList - List of records to update
	 * @param int   $userId      - User id
	 */
	public static function reviewChanges($recordsList, $userId = false)
	{
		foreach ($recordsList as $record) {
			$result = ModTracker_Record_Model::setLastReviewed($record);
			ModTracker_Record_Model::unsetReviewed($record, $userId, $result);
		}
	}
}
