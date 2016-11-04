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

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$data = $request->getAll();
		$moduleName = $request->getModule();
		$type = $request->get('type');
		$page = $request->get('page');
		$linkId = $request->get('linkid');
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
		$viewer->assign('NEXTPAGE', (count($history) < $limit) ? 0 : $page + 1);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);
		$viewer->assign('DATA', $data);

		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/HistoryContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/History.tpl', $moduleName);
		}
	}
}
