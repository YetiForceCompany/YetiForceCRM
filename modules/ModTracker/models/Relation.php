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

class ModTracker_Relation_Model extends Vtiger_Record_Model
{
	protected $linkedRecordCache = [];

	public function getValue()
	{
		return \App\Record::getLabel($this->get('targetid'));
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
	 * @return string
	 */
	public function getDetailViewUrl()
	{
		$moduleModel = Vtiger_Module_Model::getInstance($this->get('targetmodule'));
		return 'index.php?module=' . $this->get('targetmodule') . '&view=' . $moduleModel->getDetailViewName() . '&record=' . $this->get('targetid');
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
