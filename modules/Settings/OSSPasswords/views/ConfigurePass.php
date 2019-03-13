<?php

/**
 * Settings OSSPasswords ConfigurePass view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_OSSPasswords_ConfigurePass_View extends Settings_Vtiger_Index_View
{
	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'modules.OSSPasswords.resources.general',
		]), parent::getFooterScripts($request));
	}

	public function process(App\Request $request)
	{
		$db = App\Db::getInstance();
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
		if (!empty($save) && !empty($pass_length_min) && !empty($pass_length_max) && !empty($pass_allow_chars)) {
			$post_min = (int) $pass_length_min > 0 ? (int) $pass_length_min : 0;
			$post_max = (int) $pass_length_max > 0 ? (int) $pass_length_max : 0;
			$aChars = strlen($pass_allow_chars) > 0 ? urldecode($pass_allow_chars) : '';
			$rChanges = '' == $registerChanges ? 0 : 1;

			// update the configuration data
			if (0 === strlen($error) && $post_min > 0 && $post_max > 0 && strlen($aChars) > 0) {
				$db->createCommand()->update('vtiger_passwords_config', [
					'pass_length_min' => $post_min,
					'pass_length_max' => $post_max,
					'pass_allow_chars' => $aChars,
					'register_changes' => $rChanges,
				])->execute();
				// update variables
				$min = $post_min;
				$max = $post_max;
				$allow_chars = $aChars;
				$register = $rChanges;

				$success = 'Settings were successfuly saved.';
			}
		} elseif (!empty($encryption_pass)) {
			// save new password key
			if (!empty($encrypt) && !empty($pass_key) && 'start' === $encrypt) {
				// save key pass
				$newPassword = strlen($pass_key) > 0 ? hash('sha256', $pass_key) : false;

				// config already exists, cant create encryption password
				if (false !== $config) {
					$info = 'Encryption password is already created.';
				} elseif (false !== $newPassword) {
					// create new config
					$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);

					$config = ['encode' => ['key' => "$newPassword"]];
					$recordModel->writePhpIni($config, 'modules/OSSPasswords/config.ini.php');

					// start transaction
					$transaction = $db->beginTransaction();
					try {
						// now encrypt all osspasswords with given key
						$db->createCommand()
							->update('vtiger_osspasswords', [
								'password' => new \yii\db\Expression('AES_ENCRYPT(`password`,:newPass)', [':newPass' => $newPassword])
							])->execute();
						$success = 'Encryption password has been successfully saved!';
						// commit transaction
						$transaction->commit();
					} catch (\Throwable  $e) {
						$transaction->rollBack();
						throw $e;
					}
				} else {
					\App\Log::error('New encryption password incorrect!');
					$error = 'New encryption password is incorrect!';
				}
			} // change password key
			elseif ($config_exists && 'edit' === $encrypt) {
				$configKey = $config['key'] ?? false;

				// check if given password is correct
				$pass_ok = true;
				if (0 !== strcmp($config['key'], hash('sha256', $oldKey))) { // not equal
					$pass_ok = false;
					$error = 'Old password key is incorrect!';
				} elseif (0 === strlen($newKey)) {
					$pass_ok = false;
					$error = 'New password too short!';
				}

				if ($pass_ok && false !== $configKey) {
					// start transaction
					$transaction = $db->beginTransaction();
					try {
						// first we are decrypting all the passwords
						$decrypt_aff_rows = $db->createCommand()->update(
							'vtiger_osspasswords',
							['password' => new \yii\db\Expression('AES_DECRYPT(`password`, :param)', [':param' => $configKey])]
						)->execute();

						// then we are encrypting passwords using new password key
						$newKey = hash('sha256', $newKey);
						$encrypt_aff_rows = $db->createCommand()->update(
							'vtiger_osspasswords',
							['password' => new \yii\db\Expression('AES_ENCRYPT(`password`, :param)', [':param' => $newKey])]
						)->execute();

						if ($decrypt_aff_rows === $encrypt_aff_rows) {
							// at end we are saving new password key
							$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
							$config = ['encode' => ['key' => "$newKey"]];
							$recordModel->writePhpIni($config, 'modules/OSSPasswords/config.ini.php');
							$success = 'Your key has been changed correctly.';
						} else {
							\App\Log::error('Changing password encryption keys was unsuccessfull!');
							$error = 'Changing encryption key!';
						}

						// commit transaction
						$transaction->commit();
					} catch (\Throwable  $e) {
						$transaction->rollBack();
						throw $e;
					}
				}
			} // stop encrypting passwords
			elseif ('stop' === $encrypt) {
				// check if the given password is correct
				$passKey = hash('sha256', $passKey);
				$configKey = $config['key'];

				$pass_ok = true;
				if (0 !== strcmp($passKey, $configKey)) {
					$pass_ok = false;
					$error = 'Given password is incorrect!';
				}

				if ($pass_ok) {
					// start transaction
					$transaction = $db->beginTransaction();
					try {
						// decrypt all passwords
						$result = $db->createCommand()->update(
							'vtiger_osspasswords',
							['password' => new \yii\db\Expression('AES_DECRYPT(`password`, :param)', [':param' => $passKey])]
						)->execute();

						// delete config file
						if (false !== $result) {
							@unlink('modules/OSSPasswords/config.ini.php');
							$success = 'Password encryption is stopped.';
						} else {
							$error = 'Errors happened while stopping encryption!';
						}

						// commit transaction
						$transaction->commit();
						$config = false;
					} catch (\Throwable  $e) {
						$transaction->rollBack();
						throw $e;
					}
				}
			}
		} elseif (!empty($uninstall_passwords) && !empty($status)) {
			\App\Log::trace('Uninstallation started...');
			$moduleName = $request->getModule();
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			if ($moduleModel) {
				$moduleModel->delete();
			}
		}

		$registerTxt = 0 === $register ? '' : 'checked="checked"';

		$moduleName = $request->getModule();
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
		if (\App\User::getCurrentUserModel()->isAdmin()) {
			$viewer->assign('ISADMIN', 1);
		} else {
			$viewer->assign('ISADMIN', 0);
		}

		// encryption variables
		$viewer->assign('CONFIG', (!$config ? false : ['key' => $config['key'] ?? '']));

		$viewer->view('ConfigurePass.tpl', $moduleName);
	}
}
