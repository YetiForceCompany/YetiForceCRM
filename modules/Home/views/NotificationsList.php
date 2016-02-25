<?php

/**
 * Notification List View Class
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Home_NotificationsList_View extends Vtiger_Index_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

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
}
