<?php

/**
 * PBX ListView Model Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PBX_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/**
	 * Function to get Basic links.
	 *
	 * @return array of Basic links
	 */
	public function getBasicLinks()
	{
		$basicLinks = [];
		$moduleModel = $this->getModule();
		if ($moduleModel->hasCreatePermissions()) {
			$basicLinks[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_RECORD',
				'linkdata' => ['url' => 'index.php?module=PBX&parent=Settings&view=EditModal'],
				'linkicon' => 'fas fa-plus',
				'linkclass' => 'btn-light addRecord',
				'showLabel' => 1,
				'modalView' => true,
			];
		}
		return $basicLinks;
	}
}
