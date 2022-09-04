<?php

/**
 * Manage widgets view.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Kon <a.kon@yetiforce.com>
 */
/**
 * Settings_WidgetsManagement_ManageWidgets_View class.
 */
class Settings_WidgetsManagement_ManageWidgets_View extends App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_MANAGE_WIDGETS';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-clone';

	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule(false);
		$sourceModule = $request->getByType('sourceModule', 2);
		if (empty($sourceModule)) {
			$sourceModule = 'Home';
		}
		$widgetsManagementModel = new Settings_WidgetsManagement_Module_Model();

		//authorized albo block id
		$viewer->assign('ALL_AUTHORIZATION', Settings_Roles_Record_Model::getAll());
		$viewer->assign('ALL_SERVERS', Settings_WebserviceApps_Module_Model::getServers());
		$viewer->assign('WIDGETS', $widgetsManagementModel->getDashboardForModule($sourceModule));
		$viewer->assign('DASHBOARD_BLOCK_ID', $request->getByType('blockId', App\Purifier::INTEGER));
		$viewer->assign('AUTHORIZED', $request->getByType('authorized', App\Purifier::ALNUM));
		$viewer->assign('QUALIFIED_MODULE', $moduleName);
		$viewer->view('ManageWidgets.tpl', $moduleName);
	}
}
