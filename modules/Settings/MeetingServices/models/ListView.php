<?php
/**
 * List view file for MeetingServices module.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * List view model class for MeetingServices.
 */
class Settings_MeetingServices_ListView_Model extends Settings_Vtiger_ListView_Model
{
	/** {@inheritdoc} */
	public function getBasicLinks()
	{
		$basicLinks = [];
		$moduleModel = $this->getModule();
		if ($moduleModel->hasCreatePermissions()) {
			$basicLinks[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => 'LBL_ADD_RECORD',
				'linkdata' => ['url' => $moduleModel->getCreateRecordUrl()],
				'linkclass' => 'btn-light js-add-record-modal',
				'linkicon' => 'fas fa-plus',
				'showLabel' => 1
			];
		}
		return $basicLinks;
	}
}
