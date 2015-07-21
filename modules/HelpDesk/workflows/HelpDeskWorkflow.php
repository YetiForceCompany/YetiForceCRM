<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com.
 * All Rights Reserved.
 *************************************************************************************************************************************/

function HeldDeskChangeNotifyContacts($entityData) {
	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];

	$log = vglobal('log');
	$db = PearDatabase::getInstance();
	$log->debug("Entering HeldDeskChangeNotifyContacts");

	$mails = [];
	$sql = 'SELECT `relcrmid` as contactid FROM `vtiger_crmentityrel` WHERE `module` = ? AND `relmodule` = ? AND `crmid` = ?;';
	$params = array( 'HelpDesk', 'Contacts', $entityId );
	$result = $db->pquery( $sql, $params );
	$num = $db->num_rows( $result );

	if ( $num > 0 ) {
		for( $i=0; $i<$num; $i++ ) {
			$contactId = $db->query_result( $result, $i, 'contactid' );

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
		}
	}

	if ( count($mails) > 0 ) {
		$mails = implode(',', $mails);

		$data = array('id' => 41, 'to_email' => $mails, 'module' => 'HelpDesk', 'record' => $entityId);

		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');

		$log->debug("HeldDeskChangeNotifyContacts");

		if ($recordModel->sendMailFromTemplate($data)) {
			return true;
		}
	}

	$log->debug("HeldDeskChangeNotifyContacts");
	return false;
}

function HeldDeskClosedNotifyContacts($entityData) {
	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];

	$log = vglobal('log');
	$db = PearDatabase::getInstance();
	$log->debug("Entering HeldDeskClosedNotifyContacts");

	$mails = [];
	$sql = 'SELECT `relcrmid` as contactid FROM `vtiger_crmentityrel` WHERE `module` = ? AND `relmodule` = ? AND `crmid` = ?;';
	$params = array( 'HelpDesk', 'Contacts', $entityId );
	$result = $db->pquery( $sql, $params );
	$num = $db->num_rows( $result );

	if ( $num > 0 ) {
		for( $i=0; $i<$num; $i++ ) {
			$contactId = $db->query_result( $result, $i, 'contactid' );

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
		}
	}

	if ( count($mails) > 0 ) {
		$mails = implode(',', $mails);

		$data = array('id' => 37, 'to_email' => $mails, 'module' => 'HelpDesk', 'record' => $entityId);

		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');

		$log->debug("HeldDeskClosedNotifyContacts");

		if ($recordModel->sendMailFromTemplate($data)) {
			return true;
		}
	}

	$log->debug("HeldDeskClosedNotifyContacts");
	return false;
}