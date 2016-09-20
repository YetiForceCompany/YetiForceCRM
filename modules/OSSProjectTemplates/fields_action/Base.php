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

class Field_Model_Base
{

	public function getFieldInfo($fieldName, $moduleName, $column)
	{
		$moduleTabId = \includes\Modules::getModuleId($moduleName);
		$db = PearDatabase::getInstance();

		$sql = "SELECT * FROM vtiger_field WHERE tabid = $moduleTabId && fieldname = '$fieldName'";
		$result = $db->query($sql, true);

		return $db->query_result($result, 0, $column);
	}

	public function getFieldLabel($fieldName, $moduleName)
	{
		return $this->getFieldInfo($fieldName, $moduleName, 'fieldlabel');
	}

	public function fieldIsRequired($fieldName, $moduleName)
	{
		$result = $this->getFieldInfo($fieldName, $moduleName, 'typeofdata');
		$pos = strpos($result, 'M');
		if ($pos === false) {
			return false;
		} else {
			return true;
		}
	}

	public function getValue($fieldName, $relId, $templateId, $baseRecord = NULL, $parentTplId = NULL)
	{
		$db = PearDatabase::getInstance();
		$sql = "SELECT fld_val FROM vtiger_oss_project_templates WHERE fld_name = ? && id_tpl = ?";

		if (NULL !== $baseRecord && $parentTplId !== NULL) {
			$templateId = $parentTplId;
		}

		$result = $db->pquery($sql, array($fieldName, $templateId), TRUE);
		return $db->query_result($result, 0, 'fld_val');
	}
}
