<?php

/**
 * OSSMailScanner CRMEntity class
 * @package YetiForce.CRMEntity
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class OSSMailScanner
{

	public function vtlib_handler($moduleName, $eventType)
	{
		$adb = PearDatabase::getInstance();

		if ($eventType == 'module.postinstall') {
			$this->turn_on($moduleName);
			$adb->query("UPDATE vtiger_tab SET customized=0 WHERE name='OSSMailScanner'");
			$adb->pquery("INSERT INTO vtiger_ossmailscanner_config (conf_type,parameter) VALUES (?,?)", array('folders', 'Received'));
			$adb->pquery("INSERT INTO vtiger_ossmailscanner_config (conf_type,parameter) VALUES (?,?)", array('folders', 'Sent'));
			$adb->pquery("INSERT INTO vtiger_ossmailscanner_config (conf_type,parameter) VALUES (?,?)", array('folders', 'Spam'));
			$adb->pquery("INSERT INTO vtiger_ossmailscanner_config (conf_type,parameter) VALUES (?,?)", array('folders', 'Trash'));
			$adb->pquery("INSERT INTO vtiger_ossmailscanner_config (conf_type,parameter) VALUES (?,?)", array('folders', 'All'));
			$adb->pquery("INSERT INTO vtiger_ossmailscanner_config (conf_type,parameter) VALUES (?,?)", array('emailsearch', 'fields'));
			$adb->pquery("INSERT INTO vtiger_ossmailscanner_config (conf_type,parameter,value) VALUES (?,?,?)", array('cron', 'email', ''));
			$adb->pquery("INSERT INTO vtiger_ossmailscanner_config (conf_type,parameter,value) VALUES (?,?,?)", array('cron', 'time', ''));
			$adb->pquery("INSERT INTO vtiger_ossmailscanner_config (conf_type,parameter,value) VALUES (?,?,?)", array('emailsearch', 'changeTicketStatus', 'false'));
			$moduleModel = Settings_Picklist_Module_Model::getInstance('HelpDesk');
			$fieldModel = Settings_Picklist_Field_Model::getInstance('ticketstatus', $moduleModel);
			$moduleModel->addPickListValues($fieldModel, 'Answered');
			$Module = vtlib\Module::getInstance($moduleName);
			$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$adb->pquery("INSERT INTO vtiger_ossmails_logs (`action`, `info`, `user`) VALUES (?, ?, ?);", array('Action_InstallModule', $moduleName . ' ' . $Module->version, $user_id), false);
		} else if ($eventType == 'module.disabled') {
			$this->turn_off($moduleName);
			$adb->pquery('UPDATE vtiger_cron_task SET status=0 WHERE module=?', array('OSSMailScanner'));
			$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
			App\Db::getInstance()->createCommand()->insert('vtiger_ossmails_logs', ['action' => 'Action_DisabledModule', 'info' => $moduleName, 'user' => $user_id, 'start_time' => date('Y-m-d H:i:s')])->execute();
		} else if ($eventType == 'module.enabled') {
			$adb->pquery('UPDATE vtiger_cron_task SET status=1 WHERE module=?', array('OSSMailScanner'));
			$this->turn_on($moduleName);
			$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
			App\Db::getInstance()->createCommand()->insert('vtiger_ossmails_logs', ['action' => 'Action_EnabledModule', 'info' => $moduleName, 'user' => $user_id, 'start_time' => date('Y-m-d H:i:s')])->execute();
		} else if ($eventType == 'module.preuninstall') {
			
		} else if ($eventType == 'module.preupdate') {
			
		} else if ($eventType == 'module.postupdate') {
			$adb = PearDatabase::getInstance();
			$Module = vtlib\Module::getInstance($moduleName);
			if (version_compare($Module->version, '1.21', '>')) {
				$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
				App\Db::getInstance()->createCommand()->insert('vtiger_ossmails_logs', ['action' => 'Action_UpdateModule', 'info' => $moduleName . ' ' . $Module->version, 'user' => $user_id, 'start_time' => date('Y-m-d H:i:s')])->execute();
			}
		}
	}

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

	public function turn_off($moduleName)
	{
		$adb = PearDatabase::getInstance();
		$adb->pquery("DELETE FROM vtiger_settings_field WHERE name=?", array('Mail Scanner'));
		$adb->pquery("DELETE FROM vtiger_settings_field WHERE name=?", array('Mail Logs'));
	}
}
