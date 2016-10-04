<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************************************************************** */

class OSSMail
{

	public function vtlib_handler($moduleName, $eventType)
	{
		$adb = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {
			$displayLabel = 'OSSMail';
			$adb->pquery("UPDATE vtiger_tab SET customized=0 WHERE name=?", array($displayLabel), true);
			$blockid = $adb->query_result(
				$adb->pquery("SELECT blockid FROM vtiger_settings_blocks WHERE label='LBL_MAIL'", array()), 0, 'blockid');
			$sequence = (int) $adb->query_result(
					$adb->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_field WHERE blockid=?", array($blockid)), 0, 'sequence') + 1;
			$fieldid = $adb->getUniqueId('vtiger_settings_field');
			$adb->pquery("INSERT INTO vtiger_settings_field (fieldid,blockid,sequence,name,iconpath,description,linkto)
				VALUES (?,?,?,?,?,?,?)", array($fieldid, $blockid, $sequence, 'Mail', '', 'LBL_OSSMAIL_DESCRIPTION', 'index.php?module=OSSMail&parent=Settings&view=index'));
			$adb->query("CREATE TABLE IF NOT EXISTS `vtiger_ossmails_logs` (
					  `id` int(19) NOT NULL AUTO_INCREMENT,
					  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					  `end_time` timestamp NULL DEFAULT NULL,
					  `action` varchar(100) DEFAULT NULL,
					  `status` tinyint(3) DEFAULT NULL,
					  `user` varchar(100) DEFAULT NULL,
					  `count` int(10) DEFAULT NULL,
					  `stop_user` varchar(100) DEFAULT NULL,
					  `info` varchar(100) DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8");
			$Module = vtlib\Module::getInstance($moduleName);
			$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$adb->pquery("INSERT INTO vtiger_ossmails_logs (`action`, `info`, `user`) VALUES (?, ?,?);", array('Action_InstallModule', $moduleName . ' ' . $Module->version, $user_id), false);
		} else if ($eventType == 'module.disabled') {
			$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$adb->pquery("INSERT INTO vtiger_ossmails_logs (`action`, `info`, `user`) VALUES (?, ?,?);", array('Action_DisabledModule', $moduleName, $user_id), false);
		} else if ($eventType == 'module.enabled') {
			if (Settings_ModuleManager_Library_Model::checkLibrary('roundcube')) {
				throw new \Exception\NotAllowedMethod(vtranslate('ERR_NO_REQUIRED_LIBRARY', 'Settings:Vtiger', 'roundcube'));
			}
			$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
			$adb->pquery("INSERT INTO vtiger_ossmails_logs (`action`, `info`, `user`) VALUES (?, ?, ?);", array('Action_EnabledModule', $moduleName, $user_id), false);
		} else if ($eventType == 'module.preuninstall') {
			
		} else if ($eventType == 'module.preupdate') {
			
		} else if ($eventType == 'module.postupdate') {
			$adb = PearDatabase::getInstance();
			$OSSMail = vtlib\Module::getInstance('OSSMail');
			if (version_compare($OSSMail->version, '1.39', '>')) {
				$user_id = Users_Record_Model::getCurrentUserModel()->get('user_name');
				$adb->pquery("INSERT INTO vtiger_ossmails_logs (`action`, `info`, `user`) VALUES (?, ?, ?);", array('Action_UpdateModule', $moduleName . ' ' . $Module->version, $user_id), false);
			}
		}
	}
}
