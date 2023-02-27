<?php
/**
 * List file for WooCommerce integration model.
 *
 * @package Settings.Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * List class for WooCommerce integration model.
 */
class Settings_WooCommerce_ListView_Model extends Settings_Vtiger_ListView_Model
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
				'linkclass' => 'btn-primary mr-2 js-add-record-modal',
				'linkicon' => 'fas fa-plus',
				'showLabel' => 1
			];
			$basicLinks[] = [
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => \App\Language::translate('LBL_LOGS_VIEWER', 'Settings:Log'),
				'linkurl' => 'index.php?parent=Settings&module=Log&view=LogsViewer&type=woocommerce',
				'linkclass' => 'btn-info',
				'linkicon' => 'yfi yfi-view-logs',
				'showLabel' => 1
			];
		}
		return $basicLinks;
	}
}
