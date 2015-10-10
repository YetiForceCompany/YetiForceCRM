<?php
define('_BENNU_VERSION', '0.1');
include('modules/Calendar/iCal/iCalendar_rfc2445.php');
include('modules/Calendar/iCal/iCalendar_components.php');
include('modules/Calendar/iCal/iCalendar_properties.php');
include('modules/Calendar/iCal/iCalendar_parameters.php');
include('modules/Calendar/iCal/ical-parser-class.php');
require_once('include/Zend/Json.php');
require_once('modules/Calendar/iCalLastImport.php');

require_once('include/utils/utils.php');
require_once('include/CRMEntity.php');

global $import_dir,$current_user,$mod_strings,$app_strings,$currentModule;

if($_REQUEST['step']!='undo'){
	$last_import = new iCalLastImport();
	$last_import->clearRecords($current_user->id);
	$file_details = $_FILES['ics_file'];
	$binFile = 'vtiger_import'.date('YmdHis');
	$file = $import_dir.''.$binFile;
	$filetmp_name = $file_details['tmp_name'];
	$upload_status = move_uploaded_file($filetmp_name,$file);

	$skip_fields = array(
		'Events'=>array('duration_hours'),
		'Calendar'=>array('activitystatus')
	);
	$required_fields = array();

	$modules = array('Events','Calendar');
	foreach($modules as $module){
		$calendar = CRMEntity::getInstance('Calendar');
		$calendar->initRequiredFields($module);
		$val = array_keys($calendar->required_fields);
		$required_fields[$module] = array_diff($val,$skip_fields[$module]);
	}

	$ical = new iCal();
	$ical_activities = $ical->iCalReader($binFile);

	$count['Events'] = $count['Calendar'] = $skip_count['Events'] = $skip_count['Calendar'] = 0;
	for($i=0;$i<count($ical_activities);$i++){
		if($ical_activities[$i]['TYPE'] == 'VEVENT'){
			$activity = new iCalendar_event;
			$module = 'Events';
		} else {
			$activity = new iCalendar_todo;
			$module = 'Calendar';
		}

		$count[$module]++;
		$calendar = CRMEntity::getInstance('Calendar');
		$calendar->column_fields = $activity->generateArray($ical_activities[$i]);
		$calendar->column_fields['assigned_user_id'] = $current_user->id;
		$skip_record = false;
		foreach($required_fields[$module] as $key){
			if(empty($calendar->column_fields[$key])){
				$skip_count[$module]++;
				$skip_record = true;
				break;
			}
		}
		if($skip_record === true) {
			continue;
		}
		$calendar->save('Calendar');
		$last_import = new iCalLastImport();
		$last_import->setFields(array('userid' => $current_user->id,
									'entitytype' => 'Calendar',
									'crmid' => $calendar->id));
		$last_import->save();
		if(!empty($ical_activities[$i]['VALARM'])){
			$calendar->activity_reminder($calendar->id,$calendar->column_fields['reminder_time'],0,'','');
		}
	}
	unlink($file);
	$smarty->assign("IMAGE_PATH", $last_imported);
	$smarty = new vtigerCRM_Smarty;

	$smarty->assign("MOD", $mod_strings);
	$smarty->assign("APP", $app_strings);
	$smarty->assign("IMP", $import_mod_strings);
	$smarty->assign("THEME", $theme);
	$smarty->assign("IMAGE_PATH", $image_path);
	$smarty->assign("MODULE", vtlib_purify($_REQUEST['module']));
	$smarty->assign("SINGLE_MOD", vtlib_purify($_REQUEST['module']));
		$smarty->display("Buttons_List1.tpl");

	$imported_events = $count['Events'] - $skip_count['Events'];
	$imported_tasks = $count['Calendar'] - $skip_count['Calendar'];
	 $message= "<b>".$mod_strings['LBL_SUCCESS']."</b>"
	 			."<br><br>" .$mod_strings['LBL_SUCCESS_EVENTS_1']."  $imported_events"
	 			."<br><br>" .$mod_strings['LBL_SKIPPED_EVENTS_1'].$skip_count['Events']
	 			."<br><br>" .$mod_strings['LBL_SUCCESS_CALENDAR_1']."  $imported_tasks"
	 			."<br><br>" .$mod_strings['LBL_SKIPPED_CALENDAR_1'].$skip_count['Calendar']
	 			."<br><br>";

	$smarty->assign("MESSAGE", $message);
	$smarty->assign("RETURN_MODULE", $currentModule);
	$smarty->assign("RETURN_ACTION", 'ListView');
	$smarty->assign("MODULE", $currentModule);
	$smarty->assign("MODULENAME", $currentModule);
	$smarty->display("iCalImport.tpl");

} else {
	$smarty->assign("IMAGE_PATH", $last_imported);
	$smarty = new vtigerCRM_Smarty;

	$smarty->assign("MOD", $mod_strings);
	$smarty->assign("APP", $app_strings);
	$smarty->assign("IMP", $import_mod_strings);
	$smarty->assign("THEME", $theme);
	$smarty->assign("IMAGE_PATH", $image_path);
	$smarty->assign("MODULE", vtlib_purify($_REQUEST['module']));
	$smarty->assign("SINGLE_MOD", vtlib_purify($_REQUEST['module']));
		$smarty->display("Buttons_List1.tpl");

	$last_import = new iCalLastImport();
	$ret_value = $last_import->undo('Calendar', $current_user->id);

	if(!empty($ret_value)){
	 $message= "<b>".$mod_strings['LBL_SUCCESS']."</b>"
	 			."<br><br>" .$mod_strings['LBL_LAST_IMPORT_UNDONE']." ";
	} else {
	 $message= "<b>".$mod_strings['LBL_FAILURE']."</b>"
	 			."<br><br>" .$mod_strings['LBL_NO_IMPORT_TO_UNDO']." ";
	}

	$smarty->assign("MESSAGE", $message);
	$smarty->assign("UNDO", 'yes');
	$smarty->assign("RETURN_MODULE", $currentModule);
	$smarty->assign("RETURN_ACTION", 'ListView');
	$smarty->assign("MODULE", $currentModule);
	$smarty->assign("MODULENAME", $currentModule);
	$smarty->display("iCalImport.tpl");
}
?>
