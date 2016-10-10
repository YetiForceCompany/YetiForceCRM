<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 * ****************************************************************************** */

/**
 * To get the lists of vtiger_users id who shared their calendar with specified user
 * @param $sharedid -- The shared user id :: Type integer
 * @returns $shared_ids -- a comma seperated vtiger_users id  :: Type string
 */
function getSharedCalendarId($sharedid)
{
	$adb = PearDatabase::getInstance();
	$query = "SELECT * from vtiger_sharedcalendar where sharedid=?";
	$result = $adb->pquery($query, array($sharedid));
	if ($adb->num_rows($result) != 0) {
		for ($j = 0; $j < $adb->num_rows($result); $j++)
			$userid[] = $adb->query_result($result, $j, 'userid');
		$shared_ids = implode(",", $userid);
	}
	return $shared_ids;
}

/**
 * To get hour,minute and format
 * @param $starttime -- The date&time :: Type string
 * @param $endtime -- The date&time :: Type string
 * @param $format -- The format :: Type string
 * @returns $timearr :: Type Array
 */
function getaddEventPopupTime($starttime, $endtime, $format)
{
	$timearr = Array();
	list($sthr, $stmin) = explode(":", $starttime);
	list($edhr, $edmin) = explode(":", $endtime);
	if ($format == 'am/pm' || $format == '12') {
		$hr = $sthr + 0;
		$timearr['startfmt'] = ($hr >= 12) ? "pm" : "am";
		if ($hr == 0)
			$hr = 12;
		$timearr['starthour'] = twoDigit(($hr > 12) ? ($hr - 12) : $hr);
		$timearr['startmin'] = $stmin;

		$edhr = $edhr + 0;
		$timearr['endfmt'] = ($edhr >= 12) ? "pm" : "am";
		if ($edhr == 0)
			$edhr = 12;
		$timearr['endhour'] = twoDigit(($edhr > 12) ? ($edhr - 12) : $edhr);
		$timearr['endmin'] = $edmin;
		return $timearr;
	}
	if ($format == '24') {
		$timearr['starthour'] = twoDigit($sthr);
		$timearr['startmin'] = $stmin;
		$timearr['startfmt'] = '';
		$timearr['endhour'] = twoDigit($edhr);
		$timearr['endmin'] = $edmin;
		$timearr['endfmt'] = '';
		return $timearr;
	}
}

/**
 * Function to get the vtiger_activity details for mail body
 * @param   string   $description       - activity description
 * @param   string   $from              - to differenciate from notification to invitation.
 * return   string   $list              - HTML in string format
 */
function getActivityDetails($description, $user_id, $from = '')
{
	$log = vglobal('log');
	$current_user = vglobal('current_user');
	$adb = PearDatabase::getInstance();
	require_once 'include/utils/utils.php';
	$current_language = vglobal('current_language');
	$mod_strings = return_module_language($current_language, 'Calendar');
	$log->debug("Entering getActivityDetails(" . $description . ") method ...");
	$updated = $mod_strings['LBL_UPDATED'];
	$created = $mod_strings['LBL_CREATED'];
	$reply = (($description['mode'] == 'edit') ? "$updated" : "$created");
	if ($description['activity_mode'] == "Events") {
		$end_date_lable = $mod_strings['End date and time'];
	} else {
		$end_date_lable = $mod_strings['Due Date'];
	}

	$name = \includes\fields\Owner::getUserLabel($user_id);

	// Show the start date and end date in the users date format and in his time zone
	$inviteeUser = CRMEntity::getInstance('Users');
	$inviteeUser->retrieveCurrentUserInfoFromFile($user_id);
	$startDate = new DateTimeField($description['st_date_time']);
	$endDate = new DateTimeField($description['end_date_time']);

	if ($from == "invite")
		$msg = \includes\Language::translate($mod_strings['LBL_ACTIVITY_INVITATION']);
	else
		$msg = \includes\Language::translate($mod_strings['LBL_ACTIVITY_NOTIFICATION']);

	$current_username = \includes\fields\Owner::getUserLabel($current_user->id);
	$status = \includes\Language::translate($description['status'], 'Calendar');
	$list = $name . ',';
	$list .= '<br><br>' . $msg . ' ' . $reply . '.<br> ' . $mod_strings['LBL_DETAILS_STRING'] . ':<br>';
	$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["LBL_SUBJECT"] . ' : ' . $description['subject'];
	$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["Start date and time"] . ' : ' . $startDate->getDisplayDateTimeValue($inviteeUser) . ' ' . \includes\Language::translate($inviteeUser->time_zone, 'Users');
	$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $end_date_lable . ' : ' . $endDate->getDisplayDateTimeValue($inviteeUser) . ' ' . \includes\Language::translate($inviteeUser->time_zone, 'Users');
	$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["LBL_STATUS"] . ': ' . $status;
	$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["Priority"] . ': ' . \includes\Language::translate($description['taskpriority']);
	$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["Related To"] . ': ' . \includes\Language::translate($description['relatedto']);
	if (!empty($description['contact_name'])) {
		$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["LBL_CONTACT_LIST"] . ' ' . $description['contact_name'];
	} else
		$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["Location"] . ' : ' . $description['location'];

	$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["LBL_APP_DESCRIPTION"] . ': ' . $description['description'];
	$list .= '<br><br>' . $mod_strings["LBL_REGARDS_STRING"] . ' ,';
	$list .= '<br>' . $current_username . '.';

	$log->debug("Exiting getActivityDetails method ...");
	return $list;
}

function twoDigit($no)
{
	if ($no < 10 && strlen(trim($no)) < 2)
		return "0" . $no;
	else
		return "" . $no;
}

// User Select Customization
/**
 * Function returns the id of the User selected by current user in the picklist of the ListView or Calendar view of Current User
 * return String -  Id of the user that the current user has selected 
 */
function calendarview_getSelectedUserId()
{
	global $current_user, $default_charset;
	$only_for_user = htmlspecialchars(strip_tags(AppRequest::getForSql('onlyforuser')), ENT_QUOTES, $default_charset);
	if ($only_for_user == '')
		$only_for_user = $current_user->id;
	return $only_for_user;
}

function calendarview_getSelectedUserFilterQuerySuffix()
{
	global $current_user, $adb;
	$only_for_user = calendarview_getSelectedUserId();
	$qcondition = '';
	if (!empty($only_for_user)) {
		if ($only_for_user != 'ALL') {
			// For logged in user include the group records also.
			if ($only_for_user == $current_user->id) {
				$user_group_ids = fetchUserGroupids($current_user->id);
				// User does not belong to any group? Let us reset to non-existent group
				if (!empty($user_group_ids))
					$user_group_ids .= ',';
				else
					$user_group_ids = '';
				$user_group_ids .= $current_user->id;
				$qcondition = " && vtiger_crmentity.smownerid IN (" . $user_group_ids . ")";
			} else {
				$qcondition = " && vtiger_crmentity.smownerid = " . $adb->sql_escape_string($only_for_user);
			}
		}
	}
	return $qcondition;
}

?>
