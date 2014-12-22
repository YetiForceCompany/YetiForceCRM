<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
require_once('modules/Calendar/CalendarCommon.php');
require_once('include/utils/CommonUtils.php');
require_once('include/utils/UserInfoUtil.php');
require_once('include/database/PearDatabase.php');
require_once('modules/Calendar/Activity.php');
class Appointment
{
	var $start_time;
	var $end_time;
	var $subject;
	var $participant;
	var $participant_state;
	var $contact_name;
	var $account_id;
	var $account_name;
	var $creatorid;
	var $creator;
	var $owner;
	var $ownerid;
	var $assignedto;
	var $eventstatus;
	var $priority;
	var $activity_type;
	var $description;
	var $record;
	var $temphour;
	var $tempmin;
	var $image_name;
	var $formatted_datetime;
	var $duration_min;
	var $duration_hour;
	var $shared = false;
	var $recurring;
	var $dur_hour;

	function Appointment()
	{
		$this->participant = Array();
		$this->participant_state = Array();
		$this->description = "";
	}
	
	/** To get the events of the specified user and shared events
	  * @param $userid -- The user Id:: Type integer
          * @param $from_datetime -- The start date Obj :: Type Array
          * @param $to_datetime -- The end date Obj :: Type Array
          * @param $view -- The calendar view :: Type String
	  * @returns $list :: Type Array
	 */
	
	function readAppointment($userid, &$from_datetime, &$to_datetime, $view)
	{
		global $current_user,$adb;
		require('user_privileges/user_privileges_'.$current_user->id.'.php');
		require('user_privileges/sharing_privileges_'.$current_user->id.'.php');
		$and = "AND (
					(
						(
							(CAST(CONCAT(date_start,' ',time_start) AS DATETIME) >= ? AND CAST(CONCAT(date_start,' ',time_start) AS DATETIME) <= ?)
							OR	(CAST(CONCAT(due_date,' ',time_end) AS DATETIME) >= ? AND CAST(CONCAT(due_date,' ',time_end) AS DATETIME) <= ? )
							OR	(CAST(CONCAT(date_start,' ',time_start) AS DATETIME) <= ? AND CAST(CONCAT(due_date,' ',time_end) AS DATETIME) >= ?)
						)
						AND vtiger_recurringevents.activityid is NULL
					)
				OR (
						(CAST(CONCAT(vtiger_recurringevents.recurringdate,' ',time_start) AS DATETIME) >= ?
							AND CAST(CONCAT(vtiger_recurringevents.recurringdate,' ',time_start) AS DATETIME) <= ?)
						OR	(CAST(CONCAT(due_date,' ',time_end) AS DATETIME) >= ? AND CAST(CONCAT(due_date,' ',time_end) AS DATETIME) <= ?)
						OR	(CAST(CONCAT(vtiger_recurringevents.recurringdate,' ',time_start) AS DATETIME) <= ?
							AND CAST(CONCAT(due_date,' ',time_end) AS DATETIME) >= ?)
					)
				)";
		
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		
        	$q= "select vtiger_activity.*, vtiger_crmentity.*,
					case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name
					FROM vtiger_activity
						inner join vtiger_crmentity on vtiger_activity.activityid = vtiger_crmentity.crmid
						left join vtiger_recurringevents on vtiger_activity.activityid=vtiger_recurringevents.activityid
						left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
					WHERE vtiger_crmentity.deleted = 0 and vtiger_activity.activitytype not in ('Emails','Task') $and ";

		// User Select Customization: Changes should made also in (calendayLaout getEventList) and one more BELOW
		$query_filter_prefix = calendarview_getSelectedUserFilterQuerySuffix(); 
		$q .= $query_filter_prefix; 
		// END
		$h = $from_datetime->z_hour;
		$m = $from_datetime->min;
		if(empty($m)) {
			$m = '00';
		}
		$startDate = new DateTimeField($from_datetime->year."-".$from_datetime->z_month."-".
				$from_datetime->z_day." $h:$m");
		$h = '23';
		$m = '59';
		$endDate = new DateTimeField($to_datetime->year."-".$to_datetime->z_month."-".
				$to_datetime->z_day." $h:$m");
		$params = array(
			$startDate->getDBInsertDateTimeValue(), $endDate->getDBInsertDateTimeValue(),
			$startDate->getDBInsertDateTimeValue(), $endDate->getDBInsertDateTimeValue(),
			$startDate->getDBInsertDateTimeValue(), $endDate->getDBInsertDateTimeValue(),
			$startDate->getDBInsertDateTimeValue(), $endDate->getDBInsertDateTimeValue(),
			$startDate->getDBInsertDateTimeValue(), $endDate->getDBInsertDateTimeValue(),
			$startDate->getDBInsertDateTimeValue(), $endDate->getDBInsertDateTimeValue()
		);
		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[16] == 3)
		{
			//Added for User Based Custom View for Calendar
			$sec_parameter=getCalendarViewSecurityParameter();
			$q .= $sec_parameter;
		}
									
        $q .= " AND vtiger_recurringevents.activityid is NULL ";
        $q .= " group by vtiger_activity.activityid ORDER by vtiger_activity.date_start,vtiger_activity.time_start";

		$r = $adb->pquery($q, $params);
		$n = $adb->getRowCount($r);
        $a = 0;
		$list = Array();
		
        while ( $a < $n )
        {
			
			$result = $adb->fetchByAssoc($r);
			$from = strtotime($result['date_start']);
			$to = strtotime($result['due_date']. ' '. $result["time_end"]);
			$windowTo = strtotime($endDate->getDBInsertDateTimeValue());
			for($j = $from; $j <= $to; $j=$j+(60*60*24))
			{

				$obj = &new Appointment();
				$temp_start = date("Y-m-d",$j);
				$endTime = strtotime($temp_start. ' '.  $result['time_start']);
				if($endTime > $windowTo) {
					break;
				}
			
				$result["date_start"]= $temp_start ;
				list($obj->temphour,$obj->tempmin) = explode(":",$result["time_start"]);
				if($start_timestamp != $end_timestamp && $view == 'day'){
					if($j == $start_timestamp){
						$result["duration_hours"] = 24 - $obj->temphour;
					}elseif($j > $start_timestamp && $j < $end_timestamp){
						list($obj->temphour,$obj->tempmin)= $current_user->start_hour !=''?explode(":",$current_user->start_hour):explode(":","08:00");
						$result["duration_hours"] = 24 - $obj->temphour;
					}elseif($j == $end_timestamp){
						list($obj->temphour,$obj->tempmin)= $current_user->start_hour !=''?explode(":",$current_user->start_hour):explode(":","08:00");
						list($ehr,$emin) = explode(":",$result["time_end"]);
						$result["duration_hours"] = $ehr - $obj->temphour;
					}
				}
				$obj->readResult($result, $view);
				$list[] = $obj;
				unset($obj);

			}
			$a++;
			
        }
		//Get Recurring events
		$q = "SELECT vtiger_activity.*, vtiger_crmentity.*, case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name , vtiger_recurringevents.recurringid, vtiger_recurringevents.recurringdate as date_start ,vtiger_recurringevents.recurringtype,vtiger_groups.groupname from vtiger_activity inner join vtiger_crmentity on vtiger_activity.activityid = vtiger_crmentity.crmid inner join vtiger_recurringevents on vtiger_activity.activityid=vtiger_recurringevents.activityid left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";
		$q .= getNonAdminAccessControlQuery('Calendar',$current_user);
        $q.=" where vtiger_crmentity.deleted = 0 and vtiger_activity.activitytype not in ('Emails','Task') AND (cast(concat(recurringdate, ' ', time_start) as datetime) between ? and ?) ";
		
		// User Select Customization
		$q .= $query_filter_prefix;
		// END

		$params = array($startDate->getDBInsertDateTimeValue(), $endDate->getDBInsertDateTimeValue());
													
        $q .= " ORDER by vtiger_recurringevents.recurringid";
		$r = $adb->pquery($q, $params);
        $n = $adb->getRowCount($r);
        $a = 0;
		while ( $a < $n )
                {
			$obj = &new Appointment();
                        $result = $adb->fetchByAssoc($r);
			list($obj->temphour,$obj->tempmin) = explode(":",$result["time_start"]);
                        $obj->readResult($result,$view);
                        $a++;
			$list[] = $obj;
                        unset($obj);
                }


		usort($list,'compare');
		return $list;
	}


	/** To read and set the events value in Appointment Obj
          * @param $act_array -- The vtiger_activity array :: Type Array
          * @param $view -- The calendar view :: Type String
         */
	function readResult($act_array, $view)
	{
		global $adb,$current_user,$app_strings;
		$format_sthour='';
                $format_stmin='';
		$this->description       = $act_array["description"];
		$this->eventstatus       = getRoleBasesdPickList('eventstatus',$act_array["eventstatus"]);
		$this->priority		 = getRoleBasesdPickList('taskpriority',$act_array["priority"]);
		$this->subject           = $act_array["subject"];
		$this->activity_type     = $act_array["activitytype"];
		$this->duration_hour     = $act_array["duration_hours"];
		$this->duration_minute   = $act_array["duration_minutes"];
		$this->creatorid         = $act_array["smcreatorid"];
		//$this->creator           = getUserName($act_array["smcreatorid"]);
		$this->assignedto = $act_array["user_name"];
                $this->owner   = $act_array["user_name"];
		if(!is_admin($current_user))
		{
			if($act_array["smownerid"]!=0 && $act_array["smownerid"] != $current_user->id && $act_array["visibility"] == "Public"){
				$que = "select * from vtiger_sharedcalendar where sharedid=? and userid=?";
				$row = $adb->pquery($que, array($current_user->id, $act_array["smownerid"]));
				$no = $adb->getRowCount($row);
				if($no > 0)
					$this->shared = true;
			}
		}	
		$this->image_name = $act_array["activitytype"].".gif";
		if(!empty($act_array["recurringid"]) && !empty($act_array["recurringtype"]))
			$this->recurring="Recurring.gif";
		
		$this->record            = $act_array["activityid"];
		$date = new DateTimeField($act_array["date_start"].' '. $act_array['time_start']);
		$eventStartDate = DateTimeField::convertToDBFormat($date->getDisplayDate());
		list($eventStartHour) = explode(':', $date->getDisplayTime());
		list($styear,$stmonth,$stday) = explode("-",$act_array["date_start"]);
		list($sthour, $stmin) = explode(':', $act_array['time_start']);
		if($act_array["notime"] != 1){
			$st_hour = $eventStartHour;
		}else{
			$st_hour = 'notime';
			$act_array["time_start"] = "00:00";
		}
		list($eyear,$emonth,$eday) = explode("-",$act_array["due_date"]);
		list($end_hour,$end_min) = explode(":",$act_array['time_end']);

		$start_date_arr = Array(
			'min'   => $stmin,
			'hour'  => $sthour,
			'day'   => $stday,
			'month' => $stmonth,
			'year'  => $styear
		);
		$end_date_arr = Array(
			'min'   => $end_min,
			'hour'  => $end_hour,
			'day'   => $eday,
			'month' => $emonth,
			'year'  => $eyear
		);
                $this->start_time        = new vt_DateTime($start_date_arr,true);
                $this->end_time          = new vt_DateTime($end_date_arr,true);
		if($view == 'day' || $view == 'week')
		{
			$this->formatted_datetime= DateTimeField::convertToUserFormat($eventStartDate)
					.":".$st_hour;
		}
		elseif($view == 'year')
		{
			list($year,$month,$date) = explode("-",$eventStartDate);
			$this->formatted_datetime = $month;
		}
		else
		{
			$this->formatted_datetime= DateTimeField::convertToUserFormat($eventStartDate);
		}
		return;
	}
	
	
}

/** To two array values
  * @param $a -- The vtiger_activity array :: Type Array
  * @param $b -- The vtiger_activity array :: Type Array
  * @returns value 0 or 1 or -1 depends on comparision result
 */
function compare($a,$b)
{
	if ($a->start_time->ts == $b->start_time->ts)
	{
		return 0;
   	}
	return ($a->start_time->ts < $b->start_time->ts) ? -1 : 1;
}
function getRoleBasesdPickList($fldname,$exist_val)
{
	global $adb,$app_strings,$current_user;
	$is_Admin = $current_user->is_admin;
		if($is_Admin == 'off' && $fldname != '')
			{
				$roleid=$current_user->roleid;
				$roleids = Array();
				$subrole = getRoleSubordinates($roleid);
				if(count($subrole)> 0)
				$roleids = $subrole;
				array_push($roleids, $roleid);

				//here we are checking wheather the table contains the sortorder column .If  sortorder is present in the main picklist table, then the role2picklist will be applicable for this table...

				$sql="select * from vtiger_$fldname where $fldname=?";
				$res = $adb->pquery($sql,array(decode_html($exist_val)));
				$picklistvalueid = $adb->query_result($res,0,'picklist_valueid');
				if ($picklistvalueid != null) {
					$pick_query="select * from vtiger_role2picklist where picklistvalueid=$picklistvalueid and roleid in (". generateQuestionMarks($roleids) .")";

					$res_val=$adb->pquery($pick_query,array($roleids));
					$num_val = $adb->num_rows($res_val);
				}
				if($num_val > 0)
				$pick_val = $exist_val;
				else
				$pick_val = $app_strings['LBL_NOT_ACCESSIBLE'];


			}else
			$pick_val = $exist_val;

			return $pick_val;
			
}
?>
