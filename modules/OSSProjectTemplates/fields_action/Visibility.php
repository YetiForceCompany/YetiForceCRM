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

class Field_Model_Visibility extends Field_Model_Select
{

	protected $tplAddress = 'fields_tpl/visibility.tpl';

	protected function getOptions($fieldName)
	{
		$profileRecordModel = new Settings_Profiles_Record_Model();
		$profileList = $profileRecordModel->getAll();

		$list = array();

		if (count($profileList) > 0) {
			foreach ($profileList as $key => $value) {
				$profileName = $value->get('profilename');
				$list[$profileName] = $value->get('profileid');
			}

			return $list;
		}

		return false;
	}

	public function getFieldLabel($fieldName, $moduleName)
	{
		return vtranslate('TPL_VISIBILITY', 'OSSProjectTemplates');
	}
}
