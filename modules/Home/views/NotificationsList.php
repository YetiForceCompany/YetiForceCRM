<?php

/**
 * Notification List View Class
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Home_NotificationsList_View extends Vtiger_Index_View
{

	function preProcessTplName(Vtiger_Request $request)
	{
		return 'NotificationsListPreProcess.tpl';
	}

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$notification = Home_Notification_Model::getInstance();
		$viewer->assign('NOTIFICATION_MODEL', $notification);
		$viewer->view('NotificationsListView.tpl', $moduleName);
	}

	function getBreadcrumbTitle(Vtiger_Request $request)
	{
		return vtranslate('LBL_NOTIFICATIONS');
	}

	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();
		$jsFileNames = [
			'~libraries/jquery/gridster/jquery.gridster.js',
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
		];
		$cssScripts = $this->checkAndConvertCssStyles($headerCss);
		$headerCssScriptInstances = array_merge($parentHeaderCssScriptInstances, $cssScripts);
		return $headerCssScriptInstances;
	}
}
