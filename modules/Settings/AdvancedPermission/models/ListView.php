<?php

/**
 * ListView Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_AdvancedPermission_ListView_Model extends Settings_Vtiger_ListView_Model
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
				'linkurl' => $moduleModel->getCreateRecordUrl(),
				'linkicon' => 'btn-light',
			];
		}
		$basicLinks[] = [
			'linktype' => 'LISTVIEWBASIC',
			'linklabel' => 'LBL_RECALCULATE_PERMISSION_BTN',
			'linkurl' => 'javascript:app.showModalWindow(null, \'index.php?module=AdvancedPermission&parent=Settings&view=RecalculatePermission\')',
			'linkicon' => 'yfi-advenced-custom-view-conditionsmk',
		];
		return $basicLinks;
	}
}
