<?php

/**
 * Notification List View Class
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Home_NotificationsList_View extends Vtiger_Index_View
{

	public function preProcessTplName(Vtiger_Request $request)
	{
		return 'NotificationsListPreProcess.tpl';
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$notification = Home_Notification_Model::getInstance();
		$types = [];
		if ($request->has('types')) {
			$types = $request->get('types');
		}
		$notificationEntries = [];
		if (!empty($types)) {
			$notificationEntries = $notification->getEntries(AppConfig::module($moduleName, 'MAX_NUMBER_NOTIFICATIONS'), 'AND `type` IN (' . implode(',', $types) . ')', false, false);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('NOTIFICATION_ENTRIES', $notificationEntries);
		$viewer->view('NotificationsListView.tpl', $moduleName);
	}

	public function postProcess(Vtiger_Request $request)
	{
		$userModel = Users_Privileges_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$notification = Home_Notification_Model::getInstance();
		$viewer->assign('LEFT_PANEL_HIDE', $userModel->get('leftpanelhide'));
		$viewer->assign('NOTIFICATION_TYPES', \includes\utils\Json::encode($notification->getTypesForTree()));
		$viewer->view('NotificationsListPostProcess.tpl', $moduleName);
		parent::postProcess($request);
	}

	public function getBreadcrumbTitle(Vtiger_Request $request)
	{
		return vtranslate('LBL_NOTIFICATIONS');
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		$jsFileNames = [
			'~libraries/jquery/gridster/jquery.gridster.js',
			'~libraries/jquery/jstree/jstree.js',
			'~libraries/jquery/datatables/media/js/jquery.dataTables.js',
			'~libraries/jquery/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.js',
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	public function getHeaderCss(Vtiger_Request $request)
	{
		$parentHeaderCssScriptInstances = parent::getHeaderCss($request);
		$headerCss = [
			'~libraries/jquery/gridster/jquery.gridster.css',
			'~libraries/jquery/jstree/themes/proton/style.css',
			'~libraries/jquery/datatables/media/css/jquery.dataTables_themeroller.css',
			'~libraries/jquery/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.css',
		];
		$cssScripts = $this->checkAndConvertCssStyles($headerCss);
		$headerCssScriptInstances = array_merge($parentHeaderCssScriptInstances, $cssScripts);
		return $headerCssScriptInstances;
	}
}
