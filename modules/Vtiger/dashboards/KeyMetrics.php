<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_KeyMetrics_Dashboard extends Vtiger_IndexAjax_View
{

	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->getInteger('linkid');
		$data = $request->getAll();
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		$keyMetrics = $this->getKeyMetricsWithCount();
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('KEYMETRICS', $keyMetrics);
		$viewer->assign('DATA', $data);
		if ($request->has('content')) {
			$viewer->view('dashboards/KeyMetricsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/KeyMetrics.tpl', $moduleName);
		}
	}

	// NOTE: Move this function to appropriate model.
	protected function getKeyMetricsWithCount()
	{
		$current_user = Users_Record_Model::getCurrentUserModel();
		vglobal('current_user', $current_user);
		$metriclists = $this->getMetricList();
		foreach ($metriclists as $key => &$metriclist) {
			$queryGenerator = new \App\QueryGenerator($metriclist['module']);
			$queryGenerator->initForCustomViewById($metriclist['id']);
			$metriclists[$key]['count'] = $queryGenerator->createQuery()->count();
		}
		return $metriclists;
	}

	/**
	 * To get the details of a customview entries
	 * @returns  $metriclists Array in the following format
	 * $customviewlist []= Array('id'=>custom view id, 
	 * 							'name'=>custom view name,
	 * 							'module'=>modulename,
	 * 							'count'=>''
	 * 							)
	 */
	public function getMetricList($filters = [])
	{
		$db = PearDatabase::getInstance();
		$privilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$ssql = 'select vtiger_customview.* from vtiger_customview inner join vtiger_tab on vtiger_tab.name = vtiger_customview.entitytype where vtiger_customview.setmetrics = 1 ';
		$sparams = [];

		if ($privilegesModel->isAdminUser()) {
			$ssql .= " and (vtiger_customview.status=0 or vtiger_customview.userid = ? or vtiger_customview.status =3 or vtiger_customview.userid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $privilegesModel->getId('parent_role_seq') . "::%'))";
			array_push($sparams, $privilegesModel->getId());
		}
		if ($filters) {
			$ssql .= ' && vtiger_customview.cvid IN (' . $db->generateQuestionMarks($filters) . ')';
			$sparams[] = $filters;
		}
		$ssql .= ' order by vtiger_customview.entitytype';

		$result = $db->pquery($ssql, $sparams);

		$metriclists = [];
		while ($row = $db->getRow($result)) {
			if (\App\Module::isModuleActive($row['entitytype'])) {
				if (\App\Privilege::isPermitted($row['entitytype'])) {
					$metriclists[] = [
						'id' => $row['cvid'],
						'name' => $row['viewname'],
						'module' => $row['entitytype'],
						'user' => \App\Fields\Owner::getUserLabel($row['userid']),
						'count' => '',
					];
				}
			}
		}
		return $metriclists;
	}
}
