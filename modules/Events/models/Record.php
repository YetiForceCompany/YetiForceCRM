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
		$return_id = $this->getId();
		$cont_qry = "select * from vtiger_cntactivityrel where activityid=?";
		$cont_res = $adb->pquery($cont_qry, array($return_id));
		$noofrows = $adb->num_rows($cont_res);
		$cont_id = array();
		if ($noofrows > 0) {
			for ($i = 0; $i < $noofrows; $i++) {
				$cont_id[] = $adb->query_result($cont_res, $i, "contactid");
			}
		}
		$cont_name = '';
		foreach ($cont_id as $key => $id) {
			if ($id != '') {
				$contact_name = \App\Record::getLabel($id);
				$cont_name .= $contact_name . ', ';
			}
		}

		$parentId = $this->get('parent_id');
		$parentName = '';
		if ($parentId != '') {
			$parentName = \App\Record::getLabel($parentId);
		}

		$cont_name = trim($cont_name, ', ');
		$mail_data = Array();
		$mail_data['user_id'] = $this->get('assigned_user_id');
		$mail_data['subject'] = $this->get('subject');
		$moduleName = $this->getModuleName();
		$mail_data['status'] = $this->get('activitystatus');
		$mail_data['activity_mode'] = (($moduleName == 'Calendar') ? ('Task') : ('Events'));
		$mail_data['taskpriority'] = $this->get('taskpriority');
		$mail_data['relatedto'] = $parentName;
		$mail_data['contact_name'] = $cont_name;
		$mail_data['description'] = $this->get('description');
		$mail_data['assign_type'] = $this->get('assigntype');
		$mail_data['group_name'] = \App\Fields\Owner::getGroupName($this->get('assigned_user_id'));
		$mail_data['mode'] = $this->get('mode');

		$value = getaddEventPopupTime(AppRequest::get('time_start'), AppRequest::get('time_end'), '24');
		$start_hour = $value['starthour'] . ':' . $value['startmin'] . '' . $value['startfmt'];
		if (AppRequest::get('activity_mode') != 'Task')
			$end_hour = $value['endhour'] . ':' . $value['endmin'] . '' . $value['endfmt'];
		$startDate = new DateTimeField(AppRequest::get('date_start') . ' ' . $start_hour);
		$endDate = new DateTimeField(AppRequest::get('due_date') . ' ' . $end_hour);
		$mail_data['st_date_time'] = $startDate->getDBInsertDateTimeValue();
		$mail_data['end_date_time'] = $endDate->getDBInsertDateTimeValue();
		$mail_data['location'] = $this->get('location');
		return $mail_data;
	}

	/**
	 * Add relation
	 * @param Vtiger_Request $request
	 */
	public function addRelationOperation(Vtiger_Request $request)
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
