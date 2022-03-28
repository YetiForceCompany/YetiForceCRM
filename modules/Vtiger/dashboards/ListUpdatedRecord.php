<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

Vtiger_Loader::includeOnce('modules.Vtiger.helpers.ListUpdatedRecord');

class Vtiger_ListUpdatedRecord_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		if (!$request->isEmpty('number')) {
			$number = $request->getInteger('number');
		} else {
			$number = 'all';
		}
		$linkId = $request->getInteger('linkid');
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		$data = $request->getAll();

		$columnList = ['LBL_NAME' => 'label', 'LBL_MODULE_NAME' => 'setype', 'Last Modified By' => 'modifiedtime', 'LBL_OWNER' => 'smownerid'];

		$recordList = ListUpdatedRecord::getListRecord(null, $columnList, $number);

		$viewer->assign('COLUMN_LIST', $columnList);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('LIST', $recordList);
		$viewer->assign('DATA', $data);
		if ($request->has('content')) {
			$viewer->view('dashboards/ListUpdatedRecordContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/ListUpdatedRecord.tpl', $moduleName);
		}
	}
}
