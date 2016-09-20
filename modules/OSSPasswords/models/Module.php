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

class OSSPasswords_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function returns Settings Links
	 * @return Array
	 */
	public function getSettingLinks()
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$settingLinks = parent::getSettingLinks();

		if ($currentUserModel->isAdminUser()) {
			$settingLinks[] = array(
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_PASS_CONFIGURATION',
				'linkurl' => 'index.php?module=OSSPasswords&view=ConfigurePass&parent=Settings',
				'linkicon' => ''
			);
		}
		return $settingLinks;
	}

	public function isSummaryViewSupported()
	{
		return false;
	}
}
