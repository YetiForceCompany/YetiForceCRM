<?php

/**
 * OSSPasswords module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSPasswords_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function returns Settings Links.
	 *
	 * @return array
	 */
	public function getSettingLinks()
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$settingLinks = parent::getSettingLinks();

		if ($currentUserModel->isAdminUser()) {
			$settingLinks[] = [
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_PASS_CONFIGURATION',
				'linkurl' => 'index.php?module=OSSPasswords&view=ConfigurePass&parent=Settings',
				'linkicon' => 'adminIcon-passwords-encryption',
			];
		}
		return $settingLinks;
	}

	public function isSummaryViewSupported()
	{
		return false;
	}
}
