<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

require_once 'modules/Emails/mail.php';

class HelpDeskHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		$adb = PearDatabase::getInstance(); $log = vglobal('log');

		if($eventName == 'vtiger.entity.aftersave.final') {
			$moduleName = $entityData->getModuleName();
			if ($moduleName == 'HelpDesk') {
				$ticketId = $entityData->getId();
				$sql = 'UPDATE `vtiger_troubletickets` SET `from_portal` = 0 WHERE `ticketid` = ?';
				$params = array($ticketId);
				$adb->pquery( $sql, $params );
			}
		}
		else if ( $eventName == 'vtiger.entity.link.after' ) {
			if ( $entityData['destinationModule'] == 'Contacts' && $entityData['sourceModule'] == 'HelpDesk' && isRecordExists($entityData['destinationRecordId']) ) {
				$ticketId = $entityData['sourceRecordId'];
				$contactId = $entityData['destinationRecordId'];
				$log->debug("Entering HelpDeskHandler:vtiger.entity.link.after");

				$ticketRecord = Vtiger_Record_Model::getInstanceById( $ticketId, 'HelpDesk' );
				if ( $ticketRecord->get('ticketstatus') == 'Closed' )
					return true;

				$mails = [];
				if ( isRecordExists( $contactId ) ) {
					$contactRecord = Vtiger_Record_Model::getInstanceById( $contactId, 'Contacts' );
					$primaryEmail = $contactRecord->get('email');
					$secondaryEmail = $contactRecord->get('secondary_email');

					if ( $contactRecord->get('emailoptout') == 1 ) {
						if (!empty($primaryEmail)) {
							$mails[] = $primaryEmail;
						}
						else {
							if (!empty($secondaryEmail)) {
								$mails[] = $secondaryEmail;
							}
						}
					}
				}

				if ( count($mails) > 0 ) {
					$mails = implode(',', $mails);

					$data = array('id' => 39, 'to_email' => $mails, 'module' => 'HelpDesk', 'record' => $ticketId);

					$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');

					$log->debug("HelpDeskHandler:vtiger.entity.link.after");

					if ($recordModel->sendMailFromTemplate($data)) {
						return true;
					}
				}

				$log->debug("HelpDeskHandler:vtiger.entity.link.after");

				return false;
			}
		}
	}
}