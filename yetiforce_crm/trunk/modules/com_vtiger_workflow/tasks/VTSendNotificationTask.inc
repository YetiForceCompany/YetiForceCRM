<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

require_once('modules/com_vtiger_workflow/tasks/VTEmailTask.inc');

class VTSendNotificationTask extends VTEmailTask {

	public function doTask($entity) {
		global $adb, $current_user;
		$util = new VTWorkflowUtils();
		$admin = $util->adminUser();
		$module = $entity->getModuleName();

		$taskContents = Zend_Json::decode($this->getContents($entity));
		$fromEmail	= $taskContents['fromEmail'];
		$fromName	= $taskContents['fromName'];
		$toEmail	= $taskContents['toEmail'];
		$cc			= $taskContents['ccEmail'];
		$bcc		= $taskContents['bccEmail'];
		$subject	= $taskContents['subject'];
		$content	= $taskContents['content'];

		//Storing the details of emails
		$entityIdDetails = vtws_getIdComponents($entity->getId());
		$entityId = $entityIdDetails[1];
		$moduleName = 'Emails';
		$userId = $current_user->id;
		$emailFocus = CRMEntity::getInstance($moduleName);
        $dateTime = new DateTimeField(null);
		$emailFieldValues = array(
				'assigned_user_id' => $userId,
				'subject' => $subject,
				'description' => $content,
				'from_email' => $fromEmail,
				'saved_toid' => $toEmail,
				'ccmail' => $cc,
				'bccmail' => $bcc,
				'parent_id' => $entityId."@$userId|",
				'email_flag' => 'SENT',
				'activitytype' => $moduleName,
				'date_start' => date($dateTime->getDisplayDate($current_user)),
				'time_start' => date($dateTime->getDisplayTime($current_user)),
				'mode' => '',
				'id' => ''
		);
		$emailFocus->column_fields = $emailFieldValues;
		$emailFocus->save($moduleName);

		//Including email tracking details
		global $site_URL, $application_unique_key;
		$emailId = $emailFocus->id;
		$trackURL = "$site_URL/modules/Emails/actions/TrackAccess.php?record=$entityId&mailid=$emailId&app_key=$application_unique_key";
		$content = "<img src='$trackURL' alt='' width='1' height='1'>$content";

		if (stripos($content, '<img src="cid:logo" />')) {
			$logo = 1;
		}

		$status = send_mail($module, $toEmail, $fromName, $fromEmail, $subject, $content, $cc, $bcc, '', '', $logo);

		if(!empty($emailId)) {
			$emailFocus->setEmailAccessCountValue($emailId);
		}
		if(!$status) {
			//If mail is not sent then removing the details about email
			$emailFocus->trash($moduleName, $emailId);
		}

		$util->revertUser();
	}

	/**
	 * Function to get contents of this task
	 * @param <Object> $entity
	 * @return <Array> contents
	 */
	public function getContents($entity, $entityCache=false) {
		if (!$this->contents) {
			$util = new VTWorkflowUtils();
			$admin = $util->adminUser();
			$entityCache = new VTEntityCache($admin);

			$entityOwnerId = $entity->get('assigned_user_id');
			$entityId = $entity->getId();
			$entity = $entityCache->forId($entityId);

			list($referenceEntityId, $ownerId) = vtws_getIdComponents($entityOwnerId);

			$referenceEntity = $entityCache->forId($entityOwnerId);
			if($referenceEntity->getModuleName() === 'Groups') {
				require_once('include/utils/GetGroupUsers.php');

				$groupModel = new GetGroupUsers();
				$groupModel->getAllUsersInGroup($ownerId);
				$usersList = $groupModel->group_users;
				if ($usersList) {
					$ownerId = $usersList[0];
				}
			}
			if ($ownerId) {
				$ownerFocus = CRMEntity::getInstance('Users');
				$ownerFocus->retrieve_entity_info($ownerId, 'Users');

				$dateTime = new DateTimeField($entity->get('date_start') .' '. $entity->get('time_start'));
				$entity->set('date_start', $dateTime->getDisplayDate($ownerFocus));
				$entity->set('time_start', $dateTime->getDisplayTime($ownerFocus));

				$dateTime = new DateTimeField($entity->get('due_date') .' '. $entity->get('time_end'));
				$entity->set('due_date', $dateTime->getDisplayDate($ownerFocus));
				$entity->set('time_end', $dateTime->getDisplayTime($ownerFocus));
			}
			$entityCache->cache[$entityId] = $entity;
			$this->contents = parent::getContents($entity, $entityCache);
			$util->revertUser();
		}
		return $this->contents;
	}
}

?>
