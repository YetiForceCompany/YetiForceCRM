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

class Settings_ModTracker_Module_Model extends Settings_Vtiger_Module_Model
{

	public function getModTrackerModules($active = false)
	{
		$restrictedModules = ['Integration', 'Dashboard', 'PBXManager'];
		$query = (new \App\Db\Query())->select(['vtiger_tab.name', 'vtiger_tab.tabid', 'vtiger_modtracker_tabs.visible'])
			->from('vtiger_tab')
			->leftJoin('vtiger_modtracker_tabs', 'vtiger_tab.tabid = vtiger_modtracker_tabs.tabid')
			->where(['vtiger_tab.presence' => [0, 2], 'vtiger_tab.isentitytype' => 1])
			->andWhere(['NOT IN', 'vtiger_tab.name', $restrictedModules]);
		if ($active) {
			$query->andWhere(['tiger_modtracker_tabs.visible' => 1]);
		}
		$dataReader = $query->createCommand()->query();
		$modules = [];
		while ($row = $dataReader->read()) {
			$modules[] = array(
				'id' => $row['tabid'],
				'module' => $row['name'],
				'active' => $row['visible'] == 1 ? true : false,
			);
		}
		return $modules;
	}

	public function changeActiveStatus($tabid, $status)
	{
		if ($status) {
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule($tabid);
		} else {
			CRMEntity::getInstance('ModTracker')->disableTrackingForModule($tabid);
		}
	}
}
