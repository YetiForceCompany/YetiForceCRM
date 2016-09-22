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

/** to get the details of a KeyMetrics on Home page 
 * @returns  $customviewlist Array in the following format
 * $values = Array('Title'=>Array(0=>'image name',
 * 				 1=>'Key Metrics',
 * 			 	 2=>'home_metrics'
 * 			 	),
 * 		  'Header'=>Array(0=>'Metrics',
 * 	  			  1=>'Count'
 * 			  	),
 * 		  'Entries'=>Array($cvid=>Array(
 * 			  			0=>$customview name,
 * 						1=>$no of records for the view
 * 					       ),
 * 				   $cvid=>Array(
 *                                               0=>$customview name,
 *                                               1=>$no of records for the view
 *                                              ),
 * 					|
 * 					|
 * 				   $cvid=>Array(
 *                                               0=>$customview name,
 *                                               1=>$no of records for the view
 *                                              )	
 * 				  )
 *
 */
function getKeyMetrics($maxval, $calCnt)
{
	require_once("include/Tracker.php");
	require_once('modules/CustomView/CustomView.php');
	require_once('include/logging.php');
	require_once('include/ListView/ListView.php');

	global $app_strings;
	$adb = PearDatabase::getInstance();
	$log = vglobal('log');
	$metricviewnames = "'Hot Leads'";
	$current_language = vglobal('current_language');
	$current_module_strings = return_module_language($current_language, "CustomView");
	$log = LoggerManager::getLogger('metrics');

	$metriclists = getMetricList();

	// Determine if the KeyMetrics widget should appear or not?
	if ($calCnt == 'calculateCnt') {
		return count($metriclists);
	}

	$log->info("Metrics :: Successfully got MetricList to be displayed");
	if (isset($metriclists)) {
		$current_user = vglobal('current_user');
		foreach ($metriclists as $key => $metriclist) {
			if ($metriclist['module'] == "Calendar") {
				$listquery = getListQuery($metriclist['module']);
				$oCustomView = new CustomView($metriclist['module']);
				$metricsql = $oCustomView->getModifiedCvListQuery($metriclist['id'], $listquery, $metriclist['module']);
				$metricsql = vtlib\Functions::mkCountQuery($metricsql);
				$metricresult = $adb->query($metricsql);
				if ($metricresult) {
					$rowcount = $adb->fetch_array($metricresult);
					$metriclists[$key]['count'] = $rowcount['count'];
				}
			} else {
				$queryGenerator = new QueryGenerator($metriclist['module'], $current_user);
				$queryGenerator->initForCustomViewById($metriclist['id']);
				$metricsql = $queryGenerator->getQuery();
				$metricsql = vtlib\Functions::mkCountQuery($metricsql);
				$metricresult = $adb->query($metricsql);
				if ($metricresult) {
					$rowcount = $adb->fetch_array($metricresult);
					$metriclists[$key]['count'] = $rowcount['count'];
				}
			}
		}
		$log->info("Metrics :: Successfully build the Metrics");
	}
	$title = [];
	$title[] = 'keyMetrics.gif';
	$title[] = $app_strings['LBL_HOME_KEY_METRICS'];
	$title[] = 'home_metrics';
	$header = [];
	$header[] = $app_strings['LBL_HOME_METRICS'];
	$header[] = $app_strings['LBL_MODULE'];
	$header[] = $app_strings['LBL_HOME_COUNT'];
	$entries = [];
	if (isset($metriclists)) {
		$oddRow = true;
		foreach ($metriclists as $metriclist) {
			$value = [];
			$CVname = (strlen($metriclist['name']) > 20) ? (substr($metriclist['name'], 0, 20) . '...') : $metriclist['name'];
			$value[] = '<a href="index.php?action=ListView&module=' . $metriclist['module'] . '&viewname=' . $metriclist['id'] . '">' . $CVname . '</a> <font style="color:#6E6E6E;">(' . $metriclist['user'] . ')</font>';
			$value[] = '<a href="index.php?action=ListView&module=' . $metriclist['module'] . '&viewname=' . $metriclist['id'] . '">' . \includes\Language::translate($metriclist['module']) . '</a>';
			$value[] = '<a href="index.php?action=ListView&module=' . $metriclist['module'] . '&viewname=' . $metriclist['id'] . '">' . $metriclist['count'] . '</a>';
			$entries[$metriclist['id']] = $value;
		}
	}
	$values = Array('Title' => $title, 'Header' => $header, 'Entries' => $entries);
	if (($display_empty_home_blocks ) || (count($value) != 0))
		return $values;
}

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
		if (\includes\Modules::isModuleActive($row['entitytype'])) {
			if (Users_Privileges_Model::isPermitted($row['entitytype'])) {
				$metriclists[] = [
					'id' => $row['cvid'],
					'name' => $row['viewname'],
					'module' => $row['entitytype'],
					'user' => \includes\fields\Owner::getUserLabel($row['userid']),
					'count' => '',
				];
			}
		}
	}
	return $metriclists;
}
