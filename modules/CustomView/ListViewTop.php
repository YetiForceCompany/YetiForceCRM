<?php
/*********************************************************************************
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
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header$
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

      /** to get the details of a KeyMetrics on Home page 
        * @returns  $customviewlist Array in the following format
	* $values = Array('Title'=>Array(0=>'image name',
	*				 1=>'Key Metrics',
	*			 	 2=>'home_metrics'
	*			 	),
	*		  'Header'=>Array(0=>'Metrics',
	*	  			  1=>'Count'
	*			  	),
	*		  'Entries'=>Array($cvid=>Array(
	*			  			0=>$customview name,
	*						1=>$no of records for the view
	*					       ),
	*				   $cvid=>Array(
        *                                               0=>$customview name,
        *                                               1=>$no of records for the view
        *                                              ),
	*					|
	*					|
        *				   $cvid=>Array(
        *                                               0=>$customview name,
        *                                               1=>$no of records for the view
        *                                              )	
	*				  )
	*
       */
function getKeyMetrics($maxval,$calCnt)
{
	require_once("data/Tracker.php");
	require_once('modules/CustomView/CustomView.php');
	require_once('include/logging.php');
	require_once('include/ListView/ListView.php');

	global $app_strings;
	global $adb;
	global $log;
	global $current_language;
	$metricviewnames = "'Hot Leads'";

	$current_module_strings = return_module_language($current_language, "CustomView");
	$log = LoggerManager::getLogger('metrics');

	$metriclists = getMetricList();
	
	// Determine if the KeyMetrics widget should appear or not?
	if($calCnt == 'calculateCnt') {
		return count($metriclists);
	}
	
	$log->info("Metrics :: Successfully got MetricList to be displayed");
	if(isset($metriclists))
	{
		global $current_user;
		foreach ($metriclists as $key => $metriclist) {
			if($metriclist['module'] == "Calendar") {
				$listquery = getListQuery($metriclist['module']);
				$oCustomView = new CustomView($metriclist['module']);
				$metricsql = $oCustomView->getModifiedCvListQuery($metriclist['id'],$listquery,$metriclist['module']);
				$metricsql = Vtiger_Functions::mkCountQuery($metricsql);
				$metricresult = $adb->query($metricsql);
				if($metricresult)
				{
					$rowcount = $adb->fetch_array($metricresult);
					$metriclists[$key]['count'] = $rowcount['count'];
				}
				
			} else {
				$queryGenerator = new QueryGenerator($metriclist['module'], $current_user);
				$queryGenerator->initForCustomViewById($metriclist['id']);
				$metricsql = $queryGenerator->getQuery();
				$metricsql = Vtiger_Functions::mkCountQuery($metricsql);
				$metricresult = $adb->query($metricsql);
				if($metricresult)
				{
					$rowcount = $adb->fetch_array($metricresult);
					$metriclists[$key]['count'] = $rowcount['count'];
				}
			}
		}
		$log->info("Metrics :: Successfully build the Metrics");
	}
	$title=array();
	$title[]='keyMetrics.gif';
	$title[]=$app_strings['LBL_HOME_KEY_METRICS'];
	$title[]='home_metrics';
	$header=Array();
	$header[]=$app_strings['LBL_HOME_METRICS'];
	$header[]=$app_strings['LBL_MODULE'];
	$header[]=$app_strings['LBL_HOME_COUNT'];
	$entries=Array();
	if(isset($metriclists))
	{
		$oddRow = true;
		foreach($metriclists as $metriclist)
		{
			$value=array();
			$CVname = (strlen($metriclist['name']) > 20) ? (substr($metriclist['name'],0,20).'...') : $metriclist['name'];
			$value[]='<a href="index.php?action=ListView&module='.$metriclist['module'].'&viewname='.$metriclist['id'].'">'.$CVname . '</a> <font style="color:#6E6E6E;">('. $metriclist['user'] .')</font>';
			$value[]='<a href="index.php?action=ListView&module='.$metriclist['module'].'&viewname='.$metriclist['id'].'">'.getTranslatedString($metriclist['module']). '</a>';
			$value[]='<a href="index.php?action=ListView&module='.$metriclist['module'].'&viewname='.$metriclist['id'].'">'.$metriclist['count'].'</a>';
			$entries[$metriclist['id']]=$value;
		}

	}
	$values=Array('Title'=>$title,'Header'=>$header,'Entries'=>$entries);
	if ( ($display_empty_home_blocks ) || (count($value)!= 0) )
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
function getMetricList()
{
	global $adb, $current_user;
	require('user_privileges/user_privileges_'.$current_user->id.'.php');
	
	$ssql = "select vtiger_customview.* from vtiger_customview inner join vtiger_tab on vtiger_tab.name = vtiger_customview.entitytype";
	$ssql .= " where vtiger_customview.setmetrics = 1 ";
	$sparams = array();
	
	if($is_admin == false){
	      $ssql .= " and (vtiger_customview.status=0 or vtiger_customview.userid = ? or vtiger_customview.status =3 or vtiger_customview.userid in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '".$current_user_parent_role_seq."::%'))";
	      array_push($sparams, $current_user->id);
	}
	$ssql .= " order by vtiger_customview.entitytype";
	$result = $adb->pquery($ssql, $sparams);

	$metriclists = array();

	while($cvrow=$adb->fetch_array($result))
	{
		$metricslist = Array();
		
		if(vtlib_isModuleActive($cvrow['entitytype'])){
			$metricslist['id'] = $cvrow['cvid'];
			$metricslist['name'] = $cvrow['viewname'];
			$metricslist['module'] = $cvrow['entitytype'];
			$metricslist['user'] = getUserFullName($cvrow['userid']);
			$metricslist['count'] = '';
			if(isPermitted($cvrow['entitytype'],"index") == "yes"){
				$metriclists[] = $metricslist;
			}
		}
	}

	return $metriclists;
}

?>
