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
 * Events Record Model Class.
 */
class Events_Record_Model extends Calendar_Record_Model
{
	/**
	 * Function to get the Edit View url for the record.
	 *
	 * @return string - Record Edit View Url
	 */
	public function getEditViewUrl()
	{
		$module = $this->getModule();

		return 'index.php?module=Calendar&view=' . $module->getEditViewName() . '&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action url for the record.
	 *
	 * @return string - Record Delete Action Url
	 */
	public function getDeleteUrl()
	{
		$module = $this->getModule();

		return 'index.php?module=Calendar&action=' . $module->getDeleteActionName() . '&record=' . $this->getId();
	}

	/**
	 * Funtion to get Duplicate Record Url.
	 *
	 * @return string
	 */
	public function getDuplicateRecordUrl()
	{
		$module = $this->getModule();

		return 'index.php?module=Calendar&view=' . $module->getEditViewName() . '&record=' . $this->getId() . '&isDuplicate=true';
	}

	/**
	 * Get invities.
	 *
	 * @return array
	 */
	public function getInvities()
	{
		return (new \App\Db\Query())->from('u_#__activity_invitation')->where(['activityid' => (int) $this->getId()])->all();
	}

	/**
	 * Get invition status.
	 *
	 * @param int $status
	 *
	 * @return string
	 */
	public static function getInvitionStatus($status = false)
	{
		$statuses = [0 => 'LBL_NEEDS-ACTION', 1 => 'LBL_ACCEPTED', 2 => 'LBL_DECLINED'];

		return $status !== false ? $statuses[$status] : $statuses;
	}

	/**
	 * Get invite user mail data.
	 *
	 * @return array
	 */
	public function getInviteUserMailData()
	{
		return []; // To do
	}

	/**
	 * Add relation.
	 *
	 * @param \App\Request $request
	 */
	public function addRelationOperation(\App\Request $request)
	{
		if ($request->getBoolean('relationOperation')) {
			$parentModuleName = $request->getByType('sourceModule', 2);
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
