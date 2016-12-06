<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
require_once('modules/com_vtiger_workflow/VTEntityCache.php');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');

class VTSendNotificationTask extends VTTask
{

	// Sending email takes more time, this should be handled via queue all the time.
	public $executeImmediately = true;

	public function getFieldNames()
	{
		return array("template");
	}

	/**
	 * Execute task
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$db = PearDatabase::getInstance();
		$util = new VTWorkflowUtils();
		$admin = $util->adminUser();
		$module = $recordModel->getModuleName();
		$entityId = $recordModel->getId();

		if (is_numeric($this->template) && $this->template > 0) {
			$fieldName = $entityId . '_invitation.ics';
			$fieldNameUrl = 'storage/' . $fieldName;
			$attachment = [];

			$sql = 'SELECT vtiger_activity.*, vtiger_crmentity.description, vtiger_crmentity.smownerid as assigned_user_id,  vtiger_crmentity.modifiedtime, vtiger_crmentity.createdtime, vtiger_activity_reminder.reminder_time FROM vtiger_activity INNER JOIN vtiger_crmentity ON vtiger_activity.activityid = vtiger_crmentity.crmid LEFT JOIN vtiger_activity_reminder ON vtiger_activity_reminder.activity_id = vtiger_activity.activityid AND vtiger_activity_reminder.recurringid = 0 WHERE vtiger_crmentity.deleted = 0 AND vtiger_activity.activityid = ?';
			$result = $db->pquery($sql, array($entityId));

			$moduleModel = $recordModel->getModule();
			$moduleModel->setEventFieldsForExport();
			$moduleModel->setTodoFieldsForExport();
			$exportData = new Calendar_Export_Model();
			$iCal = $exportData->output('', $result, $moduleModel, $fieldNameUrl, true);
			file_put_contents($fieldNameUrl, $iCal);
			$attachment[] = array(
				'string' => $iCal,
				'filename' => 'invite.ics',
				'filenameurl' => $fieldNameUrl,
				'encoding' => 'base64',
				'type' => 'application/ics; charset=utf-8; method=REQUEST'
			);
			$result_invitees = $db->pquery('SELECT * FROM u_yf_activity_invitation WHERE activityid = ?', array($entityId));
			while ($recordinfo = $db->fetch_array($result_invitees)) {
				$userModel = Users_Record_Model::getInstanceById($recordinfo['inviteeid'], 'Users');
				$email = $userModel->get('email1');
				$language = $userModel->get('language');
				if ($userModel->get('status') == 'Active') {
					$data = array(
						'id' => $this->template,
						'to_email' => $email,
						'to_email_mod' => 'Users',
						'notifilanguage' => $language,
						'module' => $module,
						'record' => $entityId,
						'attachment_src' => $attachment,
					);
					$mailRecordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
					$mailRecordModel->sendMailFromTemplate($data);
				}
			}
			unlink($fieldNameUrl);
		}
	}
}
