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
