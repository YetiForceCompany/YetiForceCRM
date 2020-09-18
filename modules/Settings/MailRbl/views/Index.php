<?php
/**
 * Index view file for Mail RBL module.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Index view class for Mail RBL module.
 */
class Settings_MailRbl_Index_View extends Settings_Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$activeTab = 'request';
		if ($request->has('tab')) {
			$activeTab = $request->getByType('tab');
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('ACTIVE_TAB', $activeTab);
		$viewer->assign('RBL_STATUS_LIST', [0  => 'LBL_FOR_VERIFICATION', 1 => 'LBL_ACCEPTED', 2 => 'LBL_REJECTED']);
		$viewer->assign('RBL_TYPE_LIST', [0  => 'LBL_WHITE_LIST', 1 => 'LBL_BLACK_LIST']);
		$viewer->view('Index.tpl', $request->getModule(false));
	}
}
