<?php

/**
 * Settings TreesManager module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_TreesManager_Module_Model extends Settings_Vtiger_Module_Model
{
	public $baseTable = 'vtiger_trees_templates';
	public $baseIndex = 'id';
	public $nameFields = ['name'];
	public $listFields = ['name' => 'LBL_NAME', 'tabid' => 'LBL_MODULE'];
	public $name = 'TreesManager';

	/**
	 * Get supported modules.
	 *
	 * @return Vtiger_Module_Model[]
	 */
	public function getSupportedModules(): array
	{
		return Vtiger_Module_Model::getAll([0], [], true);
	}

	/**
	 * Function to get Create view url.
	 *
	 * @return string Url
	 */
	public static function getCreateRecordUrl()
	{
		return "javascript:Settings_TreesManager_List_Js.triggerCreate('index.php?module=TreesManager&parent=Settings&view=Edit')";
	}

	/**
	 * Function to get List view url.
	 *
	 * @return string Url
	 */
	public function getListViewUrl()
	{
		return 'index.php?module=' . $this->getName() . '&parent=' . $this->getParentName() . '&view=List';
	}
}
