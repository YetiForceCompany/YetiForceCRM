<?php

/**
 * OSSPasswords module model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
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
