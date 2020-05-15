<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_History_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$data = $request->getAll();
		$moduleName = $request->getModule();
		$type = $request->getByType('type');
		$page = $request->getInteger('page');
		$linkId = $request->getInteger('linkid');
		$widget = Vtiger_Widget_Model::getInstance($linkId, \App\User::getCurrentUserId());
		$limit = (int) $widget->get('limit');

		if (empty($limit)) {
			$limit = 10;
		}
		if (empty($page)) {
			$page = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', $limit);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$history = $moduleModel->getHistory($pagingModel, $type);
		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('HISTORIES', $history);
		$viewer->assign('PAGE', $page);
		$viewer->assign('NEXTPAGE', (\count($history) < $limit) ? 0 : $page + 1);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);
		$viewer->assign('DATA', $data);
		if ($request->has('content')) {
			$viewer->view('dashboards/HistoryContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/History.tpl', $moduleName);
		}
	}
}
