<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Notebook_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request, $widget = null)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		// Initialize Widget to the right-state of information
		if ($widget && !$request->has('widgetid')) {
			$widgetId = $widget->get('id');
		} else {
			$widgetId = $request->getInteger('widgetid');
		}

		$widget = Vtiger_Notebook_Model::getUserInstance($widgetId);

		$mode = $request->getMode();
		if ('save' == $mode) {
			$content = $request->getByType('contents', 'Text');
			$dataValue = [];
			$dataValue['contents'] = strip_tags($content);
			$dataValue['lastSavedOn'] = date('Y-m-d H:i:s');
			$data = \App\Json::encode((object) $dataValue);
			$widget->set('data', $data);
			$widget->save();
		}
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		if ($request->has('content')) {
			$viewer->view('dashboards/NotebookContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/Notebook.tpl', $moduleName);
		}
	}
}
