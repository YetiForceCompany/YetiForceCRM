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
 * Events Record Model Class
 */
class Events_Record_Model extends Calendar_Record_Model
{

	/**
	 * Function to get the Edit View url for the record
	 * @return string - Record Edit View Url
	 */
	public function getEditViewUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=Calendar&view=' . $module->getEditViewName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action url for the record
	 * @return string - Record Delete Action Url
	 */
	public function getDeleteUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=Calendar&action=' . $module->getDeleteActionName() . '&record=' . $this->getId();
	}

	/**
	 * Funtion to get Duplicate Record Url
	 * @return string
	 */
	public function getDuplicateRecordUrl()
	{
		$module = $this->getModule();
		return 'index.php?module=Calendar&view=' . $module->getEditViewName() . '&record=' . $this->getId() . '&isDuplicate=true';
	}

	public function getInvities()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM u_yf_activity_invitation WHERE activityid=?', [(int) $this->getId()]);
		$invitees = [];
		while ($row = $db->getRow($result)) {
			$invitees[] = $row;
		}
		return $invitees;
	}

	static public function getInvitionStatus($status = false)
	{
		$statuses = [0 => 'LBL_NEEDS-ACTION', 1 => 'LBL_ACCEPTED', 2 => 'LBL_DECLINED'];
		return $status !== false ? $statuses[$status] : $statuses;
	}

	public function getInviteUserMailData()
	{
		$adb = PearDatabase::getInstance();
		return []; // To do
	}

	/**
	 * Add relation
	 * @param \App\Request $request
	 */
	public function addRelationOperation(\App\Request $request)
	{
		if ($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->get('sourceRecord');
			$relatedModule = $this->getModule();
			if ($relatedModule->getName() == 'Events') {
				$relatedModule = Vtiger_Module_Model::getInstance('Calendar');
			}
			$relatedRecordId = $this->getId();
			$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
		}
	}
}
