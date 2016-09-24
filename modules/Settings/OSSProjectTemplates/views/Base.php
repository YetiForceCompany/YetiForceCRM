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

class Settings_OSSProjectTemplates_Base_View extends Settings_Vtiger_Index_View
{

	public function getFieldHtmp($moduleName, $editView = FALSE)
	{
		$output = array();

		$settingsModuleModel = Settings_Vtiger_Module_Model::getInstance('Settings:OSSProjectTemplates');
		$fieldTab = $settingsModuleModel->getConfigurationForModule($moduleName);

		if ($fieldTab && count($fieldTab)) {
			foreach ($fieldTab as $key => $value) {
				require_once 'modules/OSSProjectTemplates/fields_action/' . $value . '.php';
				$modelClassName = 'Field_Model_' . $value;
				$fieldModel = new $modelClassName();
				$output[$key]['html'] = $fieldModel->process($key, $moduleName, $editView);
				$output[$key]['label'] = $fieldModel->getFieldLabel($key, $moduleName);
				$output[$key]['mandatory'] = $fieldModel->fieldIsRequired($key, $moduleName);
			}
		}

		return $output;
	}
}
