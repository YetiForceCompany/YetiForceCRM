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

class OSSMailTemplates_Field_Model extends Vtiger_Field_Model
{

	function isAjaxEditable()
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
		$modules = array();
		$params = array();
		if ($onlyActive) {
			$where .= ' WHERE (presence = ? AND isentitytype = ? ) or name = ?';
			array_push($params, 0);
			array_push($params, 1);
			array_push($params, 'Users');
		}
		$result = $adb->pquery('SELECT tabid, name, ownedby FROM vtiger_tab' . $where, $params);
		while ($row = $adb->fetch_array($result)) {
			$modules[$row['tabid']] = array('name' => $row['name'], 'label' => vtranslate($row['name'], $row['name']));
		}
		return $modules;
	}
}
