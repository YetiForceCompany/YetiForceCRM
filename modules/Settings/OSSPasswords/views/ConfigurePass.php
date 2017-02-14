<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_OSSPasswords_ConfigurePass_View extends Settings_Vtiger_Index_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \Exception\AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
		}
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);

		$jsFileNames = [
			'modules.OSSPasswords.resources.general'
		];

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($jsScriptInstances, $headerScriptInstances);
		return $headerScriptInstances;
	}

	public function process(Vtiger_Request $request)
	{
		
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');

		// config
		// check if password encode config exists
		$config_path = 'modules/OSSPasswords/config.ini.php';
		$config = '';
		$config_exists = file_exists($config_path);

		// parse ini file
		if ($config_exists) {
			$config = parse_ini_file($config_path);
		} else {
			$config = false;
		}

		// variables
		$save = $request->get('save');
		$pass_length_min = $request->get('pass_length_min');
		$pass_length_max = $request->get('pass_length_max');
		$pass_allow_chars = $request->get('pass_allow_chars');
		$registerChanges = $request->get('register_changes');
		$encryption_pass = $request->get('encryption_pass');
		$encrypt = $request->get('encrypt');
		$pass_key = $request->get('pass_key');
		$oldKey = $request->get('oldKey');
		$newKey = $request->get('newKey');
		$passKey = $request->get('passKey');
		$uninstall_passwords = $request->get('uninstall');
		$status = $request->get('status');
		$moduleName = $request->getModule();

		// communicates
		$info = '';
		$error = '';
		$success = '';

		// get min, max, allow_chars from vtiger_passwords_config
		$passwordConfig = (new \App\Db\Query())->from('vtiger_passwords_config')->one();
		$min = $passwordConfig['pass_length_min'];
		$max = $passwordConfig['pass_length_max'];
		$allow_chars = $passwordConfig['pass_allow_chars'];
		$register = $passwordConfig['register_changes'];

		// if password configuration form was sent
		//if ( isset($_POST['save'],$_POST['pass_length_min'],$_POST['pass_length_max'],$_POST['pass_allow_chars']) ) {
		if (!empty($save) && !empty($pass_length_min) && !empty($pass_length_max) && !empty($pass_allow_chars)) {
			$post_min = intval($pass_length_min) > 0 ? intval($pass_length_min) : 0;
			$post_max = intval($pass_length_max) > 0 ? intval($pass_length_max) : 0;
			$aChars = strlen($pass_allow_chars) > 0 ? urldecode($pass_allow_chars) : '';
			$rChanges = $registerChanges == '' ? 0 : 1;

			// update the configuration data
			if (strlen($error) == 0 && $post_min > 0 && $post_max > 0 && strlen($aChars) > 0) {
				App\Db::getInstance()->createCommand()->update('vtiger_passwords_config', [
					'pass_length_min' => $post_min,
					'pass_length_max' => $post_max,
					'pass_allow_chars' => $adb->sql_escape_string($aChars),
					'pass_length_max' => $rChanges,
				])->execute();
				// update variables
				$min = $post_min;
				$max = $post_max;
				$allow_chars = $aChars;
				$register = $rChanges;

				$success = 'Settings were successfuly saved.';
			}
		} else if (!empty($encryption_pass)) {
			// save new password key
			if (!empty($encrypt) && !empty($pass_key) && $encrypt == "start") {
				// save key pass
				$newPassword = strlen($pass_key) > 0 ? hash('sha256', $pass_key) : false;

				// config already exists, cant create encryption password
				if ($config != false) {
					$info = 'Encryption password is already created.';
				} else if ($newPassword != false) {
					// create new config
					$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);

					$config = ["encode" => ['key' => "$newPassword"]];
					$recordModel->write_php_ini($config, "modules/OSSPasswords/config.ini.php");

					// start transaction
					$adb->startTransaction();

					// now encrypt all osspasswords with given key
					$sql = "UPDATE `vtiger_osspasswords` SET `password` = AES_ENCRYPT( `password`, ? );";
					$result = $adb->pquery($sql, array($newPassword), true);

					$success = 'Encryption password has been successfully saved!';

					// commit transaction
					$adb->completeTransaction();
				} else {
					\App\Log::error('New encryption password incorrect!');
					$error = 'New encryption password is incorrect!';
				}
			}
			// change password key
			else if ($config_exists && $encrypt == "edit") {
				$configKey = isset($config['key']) ? $config['key'] : false;

				// check if given password is correct
				$pass_ok = true;
				if (strcmp($config['key'], hash('sha256', $oldKey)) != 0) { // not equal
					$pass_ok = false;
					$error = 'Old password key is incorrect!';
				} else if (strlen($newKey) == 0) {
					$pass_ok = false;
					$error = 'New password too short!';
				}

				if ($pass_ok && $configKey != false) {
					// start transaction
					$adb->startTransaction();

					// first we are decrypting all the passwords
					$sql = "UPDATE `vtiger_osspasswords` SET `password` = AES_DECRYPT(`password`, ?);";
					$result = $adb->pquery($sql, array($configKey), true);
					$decrypt_aff_rows = $adb->getAffectedRowCount($result);

					// then we are encrypting passwords using new password key
					$sql = "UPDATE `vtiger_osspasswords` SET `password` = AES_ENCRYPT(`password`, ?);";
					$newKey = hash('sha256', $newKey);
					$result = $adb->pquery($sql, array($newKey), true);
					$encrypt_aff_rows = $adb->getAffectedRowCount($result);

					if ($decrypt_aff_rows == $encrypt_aff_rows) {
						// at end we are saving new password key
						$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
						$config = array("encode" => array('key' => "$newKey"));
						$save_ini = $recordModel->write_php_ini($config, "modules/OSSPasswords/config.ini.php");
						$success = 'Your key has been changed correctly.';
					} else {
						\App\Log::error('Changing password encryption keys was unsuccessfull!');
						$error = 'Changing encryption key!';
					}

					// commit transaction
					$adb->completeTransaction();
				}
			}
			// stop encrypting passwords
			else if ($encrypt == "stop") {
				// check if the given password is correct
				$passKey = hash('sha256', $passKey);
				$configKey = $config['key'];

				$pass_ok = true;
				if (strcmp($passKey, $configKey) != 0) {
					$pass_ok = false;
					$error = 'Given password is incorrect!';
				}

				if ($pass_ok) {
					// start transaction
					$adb->startTransaction();

					// decrypt all passwords
					$sql = "UPDATE `vtiger_osspasswords` SET `password` = AES_DECRYPT(`password`, ?);";
					$result = $adb->pquery($sql, array($passKey), true);

					// delete config file
					if ($result != false) {
						@unlink('modules/OSSPasswords/config.ini.php');
						$success = 'Password encryption is stopped.';
					} else {
						$error = 'Errors happened while stopping encryption!';
					}

					// commit transaction
					$adb->completeTransaction();
					$config = false;
				}
			}
		} else if (!empty($uninstall_passwords) && !empty($status)) {
			\App\Log::trace('Uninstallation started...');
			$moduleName = $request->getModule();
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			if ($moduleModel) {
				$moduleModel->delete();
			}
		}

		$registerTxt = $register == 0 ? '' : 'checked="checked"';

		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('ERROR', $error);
		$viewer->assign('INFO', $info);
		$viewer->assign('SUCCESS', $success);
		$viewer->assign('MIN', $min);
		$viewer->assign('MAX', $max);
		$viewer->assign('ALLOWEDCHARS', $allow_chars);
		$viewer->assign('REGISTER', $registerTxt);
		$viewer->assign('MODULENAME', $moduleName);
		$viewer->assign('SAVE', 'Save');
		$viewer->assign('CANCEL', 'Cancel');
		if (\vtlib\Functions::userIsAdministrator($current_user))
			$viewer->assign('ISADMIN', 1);
		else
			$viewer->assign('ISADMIN', 0);

		// encryption variables
		$viewer->assign('CONFIG', (!$config ? false : array('key' => $config['key'])));

		$viewer->view('ConfigurePass.tpl', $moduleName);
	}
}
