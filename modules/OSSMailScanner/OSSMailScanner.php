<?php
/**
 * OSSMailScanner CRMEntity class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * OSSMailScanner CRMEntity class.
 */
class OSSMailScanner
{
	/**
	 * Module handler.
	 *
	 * @param string $moduleName
	 * @param string $eventType
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		if ('module.postinstall' === $eventType) {
			$this->turnOn();
			$dbCommand->update('vtiger_tab', ['customized' => 0], ['name' => 'OSSMailScanner'])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'folders', 'parameter' => 'Received'])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'folders', 'parameter' => 'Sent'])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'folders', 'parameter' => 'Spam'])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'folders', 'parameter' => 'Trash'])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'folders', 'parameter' => 'All'])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'emailsearch', 'parameter' => 'fields'])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'cron', 'parameter' => 'email', 'value' => ''])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'cron', 'parameter' => 'time', 'value' => ''])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'emailsearch', 'parameter' => 'changeTicketStatus', 'value' => 'false'])->execute();
			$Module = vtlib\Module::getInstance($moduleName);
			$userId = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_InstallModule', 'info' => $moduleName . ' ' . $Module->version, 'user' => $userId])->execute();
		} elseif ('module.disabled' === $eventType) {
			$this->turnOff();
			\App\Cron::updateStatus(\App\Cron::STATUS_DISABLED, 'LBL_MAIL_SCANNER_ACTION');
			\App\Cron::updateStatus(\App\Cron::STATUS_DISABLED, 'LBL_MAIL_SCANNER_VERIFICATION');
			\App\Cron::updateStatus(\App\Cron::STATUS_DISABLED, 'LBL_MAIL_SCANNER_BIND');
			$userId = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_DisabledModule', 'info' => $moduleName, 'user' => $userId, 'start_time' => date('Y-m-d H:i:s')])->execute();
		} elseif ('module.enabled' === $eventType) {
			\App\Cron::updateStatus(\App\Cron::STATUS_ENABLED, 'LBL_MAIL_SCANNER_ACTION');
			\App\Cron::updateStatus(\App\Cron::STATUS_ENABLED, 'LBL_MAIL_SCANNER_VERIFICATION');
			\App\Cron::updateStatus(\App\Cron::STATUS_ENABLED, 'LBL_MAIL_SCANNER_BIND');
			$this->turnOn();
			$userId = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_EnabledModule', 'info' => $moduleName, 'user' => $userId, 'start_time' => date('Y-m-d H:i:s')])->execute();
		} elseif ('module.postupdate' === $eventType) {
			$Module = vtlib\Module::getInstance($moduleName);
			if (version_compare($Module->version, '1.21', '>')) {
				$userId = Users_Record_Model::getCurrentUserModel()->get('user_name');
				$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_UpdateModule', 'info' => $moduleName . ' ' . $Module->version, 'user' => $userId, 'start_time' => date('Y-m-d H:i:s')])->execute();
			}
		}
	}

	/**
	 * Turn on.
	 */
	public function turnOn()
	{
		Settings_Vtiger_Module_Model::addSettingsField('LBL_MAIL_TOOLS', [
			'name' => 'Mail Scanner',
			'iconpath' => 'adminIcon-mail-scanner',
			'description' => 'LBL_MAIL_SCANNER_DESCRIPTION',
			'linkto' => 'index.php?module=OSSMailScanner&parent=Settings&view=Index',
		]);
		Settings_Vtiger_Module_Model::addSettingsField('LBL_LOGS', [
			'name' => 'Mail Logs',
			'iconpath' => 'adminIcon-mail-download-history',
			'description' => 'LBL_MAIL_LOGS_DESCRIPTION',
			'linkto' => 'index.php?module=OSSMailScanner&parent=Settings&view=Logs',
		]);
	}

	/**
	 * Turn off.
	 */
	public function turnOff()
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dbCommand->delete('vtiger_settings_field', ['name' => 'Mail Scanner'])->execute();
		$dbCommand->delete('vtiger_settings_field', ['name' => 'Mail Logs'])->execute();
		Settings_Vtiger_Menu_Model::clearCache();
	}
}
