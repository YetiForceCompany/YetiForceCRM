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
require_once 'modules/OSSProjectTemplates/fields_action/Base.php';

class Field_Model_Date extends Field_Model_Base
{

	private $tplAddress = 'fields_tpl/date.tpl';

	public function process($fieldName, $moduleName)
	{
		$viewer = new Vtiger_Viewer();
		$viewer->assign('FLD_ID', $fieldName);
		$viewer->assign('FLD_NAME', $fieldName);
		$viewer->assign('FLD_REQUIRED', $this->fieldIsRequired($fieldName, $moduleName));
		return $viewer->view($this->tplAddress, "Settings:OSSProjectTemplates", true);
	}

	public function getValue($fieldName, $relId, $templateId, $baseRecord = NULL, $partentTplId = NULL)
	{
		$val = parent::getValue($fieldName, $relId, $templateId, $baseRecord, $partentTplId);

		if ('create_date' === $val) {
			return date('Y-m-d');
		} else if ('num_day' === $val) {
			$db = PearDatabase::getInstance();
			$dayFielddName = $fieldName . '_day';

			if (NULL !== $baseRecord && NULL !== $partentTplId) {
				$templateId = $partentTplId;
			}

			$numDaySql = "SELECT fld_val FROM vtiger_oss_project_templates WHERE fld_name = ? && id_tpl = ?";

			$numDayResult = $db->pquery($numDaySql, array($dayFielddName, $templateId), true);
			$numDay = $db->query_result($numDayResult, 0, 'fld_val');

			$typeFielddName = $fieldName . '_day_type';
			$onlyBusinessDaySql = "SELECT fld_val FROM vtiger_oss_project_templates WHERE fld_name = ? && id_tpl = ? ";
			$onlyBusinessDayResult = $db->pquery($onlyBusinessDaySql, array($typeFielddName, $templateId), TRUE);
			$dayType = $db->query_result($onlyBusinessDayResult, 0, 'fld_val');

			$date = new DateTime();
			if (!!$dayType) {
				$date->modify("+ $numDay weekdays");
			} else {
				$date->modify("+ $numDay days");
			}
			return $date->format('Y-m-d');
		} else {
			return '';
		}
	}
}
