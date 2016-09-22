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

include_once 'modules/Vtiger/CRMEntity.php';

class OSSProjectTemplates extends Vtiger_CRMEntity
{

	public function vtlib_handler($moduleName, $eventType)
	{

		$db = PearDatabase::getInstance();

		if ($eventType == 'module.postinstall') {

			$this->addLink($moduleName);
			$this->addWidgetToListView('Project', 'LBL_GENERATE_FROM_TEMPLATE');

			$db->query("UPDATE vtiger_tab SET customized=0 WHERE name='$moduleName'");
		} else if ($eventType == 'module.disabled') {
			$this->removeRecords();
		} else if ($eventType == 'module.enabled') {
			$this->addLink($moduleName);
			$this->addWidgetToListView('Project', 'LBL_GENERATE_FROM_TEMPLATE');
		} else if ($eventType == 'module.preuninstall') {

		} else if ($eventType == 'module.preupdate') {

		} else if ($eventType == 'module.postupdate') {

		}
	}

	private function addLink($moduleName)
	{
		$adb = PearDatabase::getInstance();

		$blockid = $adb->query_result(
			$adb->pquery("SELECT blockid FROM vtiger_settings_blocks WHERE label='LBL_OTHER_SETTINGS'", array()), 0, 'blockid');
		$sequence = (int) $adb->query_result(
				$adb->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_field WHERE blockid=?", array($blockid)), 0, 'sequence') + 1;
		$fieldid = $adb->getUniqueId('vtiger_settings_field');
		$adb->pquery("INSERT INTO vtiger_settings_field (fieldid,blockid,sequence,name,iconpath,description,linkto)
				VALUES (?,?,?,?,?,?,?)", array($fieldid, $blockid, $sequence, 'Project Templates', '', 'LBL_PROJECT_TEMPLATES_DESCRIPTION', 'index.php?module=' . $moduleName . '&parent=Settings&view=Index'));
	}

	private function addWidgetToListView($moduleNames, $widgetName, $widgetType = 'LIST_VIEW_HEADER')
	{
		if (empty($moduleNames))
			return;

		if (is_string($moduleNames)) {
			$moduleNames = array($moduleNames);
		}

		foreach ($moduleNames as $moduleName) {
			$module = vtlib\Module::getInstance($moduleName);
			if ($module) {
				$module->addLink($widgetType, $widgetName, "module=OSSProjectTemplates&view=GenerateProject", '', '', '');
			}
		}
	}

	private function removeRecords()
	{
		$db = PearDatabase::getInstance();

		$db->query("DELETE FROM vtiger_links WHERE linktype='LISTVIEWSIDEBARWIDGET' && linklabel='LBL_GENERATE_FROM_TEMPLATE'", true);
		$db->query("DELETE FROM `vtiger_settings_field` WHERE `name` = 'Project Templates' && `linkto` = 'index.php?module=OSSProjectTemplates&parent=Settings&view=Index'", true);
	}
}
