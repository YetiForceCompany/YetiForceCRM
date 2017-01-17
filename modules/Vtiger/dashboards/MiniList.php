<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_MiniList_Dashboard extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request, $widget = NULL)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$data = $request->getAll();

		// Initialize Widget to the right-state of information
		if ($widget && !$request->has('widgetid')) {
			$widgetId = $widget->get('id');
		} else {
			$widgetId = $request->get('widgetid');
		}

		$widget = Vtiger_Widget_Model::getInstanceWithWidgetId($widgetId, $currentUser->getId());
		if (!$request->has('owner'))
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		else
			$owner = $request->get('owner');

		$minilistWidgetModel = new Vtiger_MiniList_Model();
		$minilistWidgetModel->setWidgetModel($widget);

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('OWNER', $owner);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('MINILIST_WIDGET_MODEL', $minilistWidgetModel);
		$viewer->assign('BASE_MODULE', $minilistWidgetModel->getTargetModule());
		$viewer->assign('SCRIPTS', $this->getFooterScripts($request));
		$viewer->assign('DATA', $data);

		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/MiniListContents.tpl', $moduleName);
			$viewer->view('dashboards/MiniListFooter.tpl', $moduleName);
		} else {
			$widget->set('title', $minilistWidgetModel->getTitle());
			$viewer->view('dashboards/MiniList.tpl', $moduleName);
		}
	}
}
