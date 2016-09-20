<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSMailTemplates_Field_Model extends Vtiger_Field_Model
{

	public function isAjaxEditable()
	{
		return false;
	}

	/**
	 * Function to get all the available picklist values for the current field
	 * @return <Array> List of picklist values if the field is of type picklist or multipicklist, null otherwise.
	 */
	public function getModulesListValues($onlyActive = true)
	{
		$adb = PearDatabase::getInstance();
		$modules = [];
		$params = [];
		$where = '';
		if ($onlyActive) {
			$where = ' WHERE (presence = ? && isentitytype = ? ) or name = ?';
			array_push($params, 0, 1, 'Users');
		}
		$query = sprintf('SELECT tabid, name, ownedby FROM vtiger_tab %s', $where);
		$result = $adb->pquery($query, $params);
		while ($row = $adb->fetch_array($result)) {
			$modules[$row['tabid']] = ['name' => $row['name'], 'label' => vtranslate($row['name'], $row['name'])];
		}
		if ($this->getName() == 'oss_module_list') {
			$modules[0] = ['name' => 'System', 'label' => vtranslate('PLL_SYSTEM', $this->getModuleName())];
		}
		return $modules;
	}
}
