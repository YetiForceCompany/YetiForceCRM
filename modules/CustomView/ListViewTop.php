<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ****************************************************************************** */
/* * *******************************************************************************
 * $Header$
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ****************************************************************************** */

/** to get the details of a customview Entries
 * @returns  $metriclists Array in the following format
 * $customviewlist []= Array('id'=>custom view id,
 *                         'name'=>custom view name,
 *                         'module'=>modulename,
  'count'=>''
  )
 */
function getMetricList($filters = [])
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
			if (Users_Privileges_Model::isPermitted($row['entitytype'])) {
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
