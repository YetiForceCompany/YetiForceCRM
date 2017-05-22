<?php

/**
 * Settings TreesManager module model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_TreesManager_Module_Model extends Settings_Vtiger_Module_Model
{

	public $baseTable = 'vtiger_trees_templates';
	public $baseIndex = 'id';
	public $nameFields = array('name');
	public $listFields = array('name' => 'LBL_NAME', 'module' => 'LBL_MODULE');
	public $name = 'TreesManager';

	public static function getSupportedModules()
	{
		$supportedModuleModels = Vtiger_Module_Model::getAll(array(0, 2));
		return $supportedModuleModels;
	}

	/**
	 * Function to get Create view url
	 * @return string Url
	 */
	public static function getCreateRecordUrl()
	{
		return "javascript:Settings_TreesManager_List_Js.triggerCreate('index.php?module=TreesManager&parent=Settings&view=Edit')";
	}

	/**
	 * Function to get List view url
	 * @return string Url
	 */
	public function getListViewUrl()
	{
		return "index.php?module=" . $this->getName() . "&parent=" . $this->getParentName() . "&view=List";
	}
}
