<?php

/**
 * RecycleBin module model Class.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class RecycleBin_Module_Model extends Vtiger_Module_Model
{
	public function getAllModuleList()
	{
		$moduleModels = parent::getEntityModules();
		$restrictedModules = ['ProjectMilestone', 'ModComments', 'Rss', 'Portal', 'Integration', 'PBXManager', 'Dashboard', 'Home'];
		foreach ($moduleModels as $key => $moduleModel) {
			if (in_array($moduleModel->getName(), $restrictedModules) || $moduleModel->get('isentitytype') != 1) {
				unset($moduleModels[$key]);
			}
		}
		return $moduleModels;
	}

	/**
	 * Funxtion to identify if the module supports quick search or not.
	 */
	public function isQuickSearchEnabled()
	{
		return false;
	}
}
