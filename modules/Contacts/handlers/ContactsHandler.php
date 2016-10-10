<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

function Contacts_createPortalLoginDetails($entityData)
{
	vimport('modules.Settings.CustomerPortal.helpers.CustomerPortalPassword');

	$adb = PearDatabase::getInstance();
	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];

	$email = $entityData->get('email');

	if (( $entityData->get('portal') == 'on' || $entityData->get('portal') == '1' ) && $entityData->get('contactstatus') != 'Inactive') {
		$sql = "SELECT id, user_name, user_password, isactive FROM vtiger_portalinfo WHERE id=?";
		$result = $adb->pquery($sql, array($entityId));
		$insert = false;
		if ($adb->num_rows($result) == 0) {
			$insert = true;
		} else {
			$dbusername = $adb->query_result($result, 0, 'user_name');
			$isactive = $adb->query_result($result, 0, 'isactive');
			$password = \vtlib\Functions::generateRandomPassword();
			$truePassword = $password;
			$password = CustomerPortalPassword::encryptPassword($password, $email);

			if ($email == $dbusername && $isactive == 1 && !$entityData->isNew()) {
				$update = false;
			} else if ($entityData->get('portal') == 'on' || $entityData->get('portal') == '1') {
				$sql = "UPDATE `vtiger_portalinfo` SET `user_name` = ?, `user_password` = ?, `isactive` = 1, `password_sent` = ? WHERE id = ?";
				$adb->pquery($sql, array($email, $password, $truePassword, $entityId));
				$password = $adb->query_result($result, 0, 'user_password');
				$update = true;
			} else {
				$sql = "UPDATE `vtiger_portalinfo` SET `user_name` = ?, `isactive` = ? WHERE id = ?";
				$adb->pquery($sql, array($email, 0, $entityId));
				$update = false;
			}
		}

		if ($insert == true) {
			$password = \vtlib\Functions::generateRandomPassword();
			$truePassword = $password;

			$password = CustomerPortalPassword::encryptPassword($password, $email);
			$params = array($entityId, $email, $password, 'C', 1, CustomerPortalPassword::getCryptType(), $truePassword);
			$sql = "INSERT INTO vtiger_portalinfo(`id`, `user_name`, `user_password`, `type`, `isactive`, `crypt_type`, `password_sent`) VALUES(" . generateQuestionMarks($params) . ")";

			$adb->pquery($sql, $params);
		}
	} else {
		$sql = "UPDATE vtiger_portalinfo SET user_name=?,isactive=0 WHERE id=?";
		$adb->pquery($sql, array($email, $entityId));
	}
}

function Contacts_markPasswordSent($entityData)
{
	$db = PearDatabase::getInstance();
	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];

	$sql = 'UPDATE `vtiger_portalinfo` SET `password_sent` = 1 WHERE `id` = ? LIMIT 1;';
	$params = array($entityId);
	$db->pquery($sql, $params);
}
