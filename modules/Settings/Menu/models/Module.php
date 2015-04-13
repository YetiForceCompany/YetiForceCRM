<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class Settings_Menu_Module_Model{
	protected $types = [
		0 => 'Module',
		1 => 'Shortcut',
		2 => 'Label',
		3 => 'Separator',
		4 => 'Script',
		5 => 'QuickCreate',
		6 => 'HomeIcon',
		//7 => 'CustomFilter',
	];

	/**
	 * Function to get instance
	 * @param <Boolean> true/false
	 * @return <Settings_Menu_Module_Model>
	 */
	public static function getInstance() {
		$instance = new self();
		return $instance;
	}
	
	public function getMenuTypes($key = false) {
		if($key === false)
			return $this->types;
		return $this->types[$key];
	}
	
	public function getMenuTypeKey($val) {
		return array_search($val, $this->types);
	}
	
	public function getMenuName($row, $settings = false) {
		switch ($row['type']) {
			case 0: $name = $row['name'];
				break;
			case 3: $name = 'LBL_SEPARATOR';
				break;
			case 5: 
				if($row['label'] != ''){
					$name = $row['label'];
				}elseif($settings){
					$name = vtranslate('LBL_QUICK_CREATE_MODULE', 'Menu') . ': ' . vtranslate('SINGLE_'.$row['name'], $row['name']);
				}
				break;
			case 6: $name = 'LBL_HOME';
				break;
			default: $name = $row['label'];
				break;
		}
		return $name;
	}
	
	public function getMenuUrl($row) {
		switch ($row['type']) {
			case 0: 
				$moduleModel = Vtiger_Module_Model::getInstance($row['module']);
				$url = $moduleModel->getDefaultUrl();
				break;
			case 1: $url = $row['dataurl'];break;
			case 4: $url = addslashes($row['dataurl']);break;
			default: $url = null;break;
		}
		return $url;
	}
	
	public function getModulesList() {
		$db = PearDatabase::getInstance();
		$modules = $db->query( "SELECT tabid,name FROM vtiger_tab WHERE name NOT "
				. "IN ('Users','ModComments') AND isentitytype = '1' ORDER BY name;" );
		return $modules->GetArray();
	}
	
	public function getLastId() {
		$db = PearDatabase::getInstance();
		$result = $db->query('SELECT MAX(id) AS max FROM yetiforce_menu;');
		return (int)$db->query_result_raw($result, 0, 'max');
	}
}