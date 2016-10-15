<?php

/**
 * 
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Notifications_Module_Model extends Settings_Vtiger_Module_Model
{

	public function getModulesList()
	{
		$presence = [0, 2];
		$restrictedModules = ['SMSNotifier', 'Emails', 'Integration', 'Dashboard', 'ModComments', 'vtmessages', 'vttwitter', 'Notification'];
		$modulesList = [];
		$dataReader = (new App\Db\Query())->select(['name'])
				->from('vtiger_tab')
				->where(['presence' => $presence, 'isentitytype' => 1])
				->andWhere(['NOT IN', 'name', $restrictedModules])
				->createCommand()->query();
		while ($moduleName = $dataReader->readColumn(0)) {
			$modulesList[$moduleName] = $moduleName;
		}
		if (!array_key_exists('Calendar', $modulesList)) {
			unset($modulesList['Events']);
		}
		return $modulesList;
	}
}
