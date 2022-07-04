<?php
/**
 * OSSMail CRMEntity class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * OSSMail CRMEntity class.
 */
class OSSMail
{
	/**
	 * Module name.
	 *
	 * @param string $moduleName
	 * @param string $eventType
	 *
	 * @throws \App\Exceptions\NotAllowedMethod
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		$dbCommand = App\Db::getInstance()->createCommand();
		if ('module.postinstall' === $eventType) {
			$displayLabel = 'OSSMail';
			$dbCommand->update('vtiger_tab', ['customized' => 0], ['name' => $displayLabel])->execute();
			Settings_Vtiger_Module_Model::addSettingsField('LBL_MAIL_TOOLS', [
				'name' => 'Mail',
				'iconpath' => 'adminIcon-mail-download-history',
				'description' => 'LBL_OSSMAIL_DESCRIPTION',
				'linkto' => 'index.php?module=OSSMail&parent=Settings&view=index',
			]);
			$Module = vtlib\Module::getInstance($moduleName);
			$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_InstallModule', 'info' => $moduleName . ' ' . $Module->version, 'user' => $user_id])->execute();
		} elseif ('module.disabled' === $eventType) {
			$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_DisabledModule', 'info' => $moduleName, 'user' => $user_id, 'start_time' => date('Y-m-d H:i:s')])->execute();
		} elseif ('module.enabled' === $eventType) {
			if (Settings_ModuleManager_Library_Model::checkLibrary('roundcube')) {
				throw new \App\Exceptions\NotAllowedMethod(\App\Language::translateArgs('ERR_NO_REQUIRED_LIBRARY_FEATURES_DOWNLOAD', 'Settings:Base', 'roundcube', \App\Language::translate('VTLIB_LBL_MODULE_MANAGER', 'Settings:Base')));
			}
			$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_EnabledModule', 'info' => $moduleName, 'user' => $user_id, 'start_time' => date('Y-m-d H:i:s')])->execute();
		} elseif ('module.postupdate' === $eventType) {
			$OSSMail = vtlib\Module::getInstance('OSSMail');
			if (version_compare($OSSMail->version, '1.39', '>')) {
				$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
				$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_UpdateModule', 'info' => $moduleName . ' ' . $OSSMail->version, 'user' => $user_id, 'start_time' => date('Y-m-d H:i:s')])->execute();
			}
		}
	}
}
