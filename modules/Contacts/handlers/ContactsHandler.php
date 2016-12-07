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

function Contacts_createPortalLoginDetails($recordModel)
{
	vimport('modules.Settings.CustomerPortal.helpers.CustomerPortalPassword');
	$db = \App\Db::getInstance();
	$recordId = $recordModel->getId();
	$email = $recordModel->get('email');

	if (( $recordModel->get('portal') === 'on' || $recordModel->get('portal') === 1 ) && $recordModel->get('contactstatus') !== 'Inactive') {

		$user = (new \App\Db\Query)->select(['id', 'user_name', 'user_password', 'isactive'])->from('vtiger_portalinfo')->where(['id' => $recordId])->one();
		$insert = false;
		if (!$user) {
			$insert = true;
		} else {
			$password = \vtlib\Functions::generateRandomPassword();
			$truePassword = $password;
			$password = CustomerPortalPassword::encryptPassword($password, $email);
			if ($email === $user['user_name'] && $user['isactive'] === 1 && !$recordModel->isNew()) {
				$update = false;
			} else if ($recordModel->get('portal') === 'on' || $recordModel->get('portal') === 1) {
				$db->createCommand()->update('vtiger_portalinfo', ['user_name' => $email, 'user_password' => $password, 'isactive' => 1, 'password_sent' => $truePassword], ['id' => $recordId])->execute();
				$password = $user['user_password'];
				$update = true;
			} else {
				$db->createCommand()->update('vtiger_portalinfo', ['user_name' => $email, 'isactive' => 0, ['id' => $recordId]])->execute();
				$update = false;
			}
		}

		if ($insert) {
			$password = \vtlib\Functions::generateRandomPassword();
			$truePassword = $password;
			$password = CustomerPortalPassword::encryptPassword($password, $email);
			$db->createCommand()->insert('vtiger_portalinfo', ['id' => $recordId, 'user_name' => $email, 'user_password' => $password, 'type' => 'C',
				'isactive' => 1, 'crypt_type' => CustomerPortalPassword::getCryptType(), 'password_sent' => $truePassword
			])->execute();
		}
	} else {
		$db->createCommand()->update('vtiger_portalinfo', ['user_name' => $email, 'isactive' => 0], ['id' => $recordId])->execute();
	}
}

function Contacts_markPasswordSent($recordModel)
{
	\App\Db::getInstance()->createCommand()->update('vtiger_portalinfo', ['password_sent' => 1], ['id' => $recordModel->getId()]);
}
