<?php

/**
 * Settings ModTracker module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_ModTracker_Module_Model extends Settings_Vtiger_Module_Model
{
	public function getModTrackerModules($active = false)
	{
		$restrictedModules = ['Integration', 'Dashboard'];
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
			$modules[] = [
				'id' => $row['tabid'],
				'module' => $row['name'],
				'active' => 1 == $row['visible'] ? true : false,
			];
		}
		$dataReader->close();

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
