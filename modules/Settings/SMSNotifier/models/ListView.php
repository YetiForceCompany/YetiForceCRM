<?php
/**
 * SMSNotifier ListView Model Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * SMSNotifier ListView Model Class.
 */
class Settings_SMSNotifier_ListView_Model extends Settings_Vtiger_ListView_Model
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
				'linkdata' => ['url' => $moduleModel->getCreateRecordUrl()],
				'linkicon' => 'fas fa-plus',
				'linkclass' => 'btn-light addRecord showModal',
				'showLabel' => 1,
				'modalView' => true,
			];
		}
		return $basicLinks;
	}
}
