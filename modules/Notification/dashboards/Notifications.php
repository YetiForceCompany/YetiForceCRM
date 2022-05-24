<?php

/**
 * Notifications Dashboard Class.
 *
 * @package Dashboard
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Notification_Notifications_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request)
	{
		$currentUserId = \App\User::getCurrentUserId();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUserId);
		$limit = $widget->get('limit');
		if (empty($limit)) {
			$limit = 10;
		}
		$condition = [];
		if (!$request->isEmpty('type', true)) {
			$condition = ['u_#__notification.notification_type' => $request->getByType('type', 'Text')];
		}
		$notificationModel = Notification_Module_Model::getInstance($moduleName);
		$notifications = $notificationModel->getEntriesInstance($limit, $condition);
		$typesNotification = $notificationModel->getTypes();
		array_unshift($typesNotification, \App\Language::translate('All'));
		$viewer->assign('TYPES_NOTIFICATION', $typesNotification);
		$viewer->assign('NOTIFICATIONS', $notifications);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		if ($request->has('content')) {
			$viewer->view('dashboards/NotificationsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/Notifications.tpl', $moduleName);
		}
	}
}
