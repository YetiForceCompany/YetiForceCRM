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
require_once 'modules/OSSProjectTemplates/fields_action/Select.php';

class Field_Model_BaseOnOtherModule extends Field_Model_Select
{

	protected $tplAddress = 'fields_tpl/base_on_other_module.tpl';

	protected function getOptions($fieldName)
	{

		$output = array('none', 'base_on_record');

		return $output;
	}

	public function process($fieldName, $moduleName, $editView)
	{
		$viewer = new Vtiger_Viewer();
		$viewer->assign('OPTION_LIST', $this->getOptions($fieldName));
		$viewer->assign('FLD_ID', $fieldName);
		$viewer->assign('FLD_NAME', $fieldName);
		$viewer->assign('FLD_REQUIRED', $this->fieldIsRequired($fieldName, $moduleName));
		return $viewer->view($this->tplAddress, "Settings:OSSProjectTemplates", true);
	}

	public function getValue($fieldName, $relId, $templateId, $baseRecord = NULL, $parentTplId = NULL)
	{
		$val = parent::getValue($fieldName, $relId, $templateId, $baseRecord, $parentTplId);

		if (isRecordExists($relId) && 'base_on_record' === $val) {
			$recodeModel = Vtiger_Record_Model::getInstanceById($relId);
			return $recodeModel->get('forecast_amount');
		} else {
			return '';
		}
	}
}
