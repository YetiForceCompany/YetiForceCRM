<?php
/**
 * OSSMailScanner CRMEntity class
 * @package YetiForce.CRMEntity
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */

/**
 * OSSMailScanner CRMEntity class
 */
class OSSMailScanner
{

	/**
	 * Module handler
	 * @param string $moduleName
	 * @param string $eventType
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		if ($eventType === 'module.postinstall') {
			$this->turn_on($moduleName);
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
			$moduleModel = Settings_Picklist_Module_Model::getInstance('HelpDesk');
			$fieldModel = Settings_Picklist_Field_Model::getInstance('ticketstatus', $moduleModel);
			$moduleModel->addPickListValues($fieldModel, 'Answered');
			$Module = vtlib\Module::getInstance($moduleName);
			$userId = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_InstallModule', 'info' => $moduleName . ' ' . $Module->version, 'user' => $userId])->execute();
		} else if ($eventType === 'module.disabled') {
			$this->turn_off($moduleName);
			$dbCommand->update('vtiger_cron_task', ['status' => 0], ['module' => 'OSSMailScanner'])->execute();
			$userId = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_DisabledModule', 'info' => $moduleName, 'user' => $userId, 'start_time' => date('Y-m-d H:i:s')])->execute();
		} else if ($eventType === 'module.enabled') {
			$dbCommand->update('vtiger_cron_task', ['status' => 1], ['module' => 'OSSMailScanner'])->execute();
			$this->turn_on($moduleName);
			$userId = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_EnabledModule', 'info' => $moduleName, 'user' => $userId, 'start_time' => date('Y-m-d H:i:s')])->execute();
		} else if ($eventType === 'module.preuninstall') {

		} else if ($eventType === 'module.preupdate') {

		} else if ($eventType === 'module.postupdate') {
			$Module = vtlib\Module::getInstance($moduleName);
			if (version_compare($Module->version, '1.21', '>')) {
				$userId = Users_Record_Model::getCurrentUserModel()->get('user_name');
				$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_UpdateModule', 'info' => $moduleName . ' ' . $Module->version, 'user' => $userId, 'start_time' => date('Y-m-d H:i:s')])->execute();
			}
		}
	}

	/**
	 * Turn on
	 * @param string $moduleName
	 */
	public function turn_on($moduleName)
	{
		Settings_Vtiger_Module_Model::addSettingsField('LBL_MAIL', [
			'name' => 'Mail Scanner',
			'iconpath' => 'adminIcon-mail-scanner',
			'description' => 'LBL_MAIL_SCANNER_DESCRIPTION',
			'linkto' => 'index.php?module=OSSMailScanner&parent=Settings&view=Index'
		]);
		Settings_Vtiger_Module_Model::addSettingsField('LBL_SECURITY_MANAGEMENT', [
			'name' => 'Mail Logs',
			'iconpath' => 'adminIcon-mail-download-history',
			'description' => 'LBL_MAIL_LOGS_DESCRIPTION',
			'linkto' => 'index.php?module=OSSMailScanner&parent=Settings&view=logs'
		]);
	}

	/**
	 * Turn off
	 * @param string $moduleName
	 */
	public function turn_off($moduleName)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dbCommand->delete('vtiger_settings_field', ['name' => 'Mail Scanner'])->execute();
		$dbCommand->delete('vtiger_settings_field', ['name' => 'Mail Logs'])->execute();
	}
}
