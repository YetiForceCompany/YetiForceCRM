<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

require_once("include/utils/CommonUtils.php");
require_once("include/events/SqlResultIterator.inc");
require_once("include/events/VTWSEntityType.inc");

require_once("VTWorkflowApplication.inc");
require_once("VTTaskManager.inc");
require_once("VTWorkflowManager.inc");
require_once("VTWorkflowUtils.php");
	function vtTaskEdit($adb, $request, $current_language, $app_strings){
		global $theme;
		$util = new VTWorkflowUtils();
		$image_path = "themes/$theme/images/";

		$module = new VTWorkflowApplication('edittask');

		$mod = return_module_language($current_language, $module->name);

		if(!$util->checkAdminAccess()){
			$errorUrl = $module->errorPageUrl($mod['LBL_ERROR_NOT_ADMIN']);
			$util->redirectTo($errorUrl, $mod['LBL_ERROR_NOT_ADMIN']);
			return;
		}

		$smarty = new vtigerCRM_Smarty();
		$tm = new VTTaskManager($adb);
		$smarty->assign('edit',isset($request["task_id"]));
		if(isset($request["task_id"])){
			$task = $tm->retrieveTask($request["task_id"]);
			$taskClass = get_class($task);
			$workflowId=$task->workflowId;
		}else{
			$workflowId = $request["workflow_id"];
			$taskClass = vtlib_purifyForSql($request["task_type"]);
			$task = $tm->createTask($taskClass, $workflowId);
		}

		if($task==null){
			$errorUrl = $module->errorPageUrl($mod['LBL_ERROR_NO_TASK']);
			$util->redirectTo($errorUrl, $mod['LBL_ERROR_NO_TASK']);
			return;
		}

		$wm = new VTWorkflowManager($adb);
		$workflow = $wm->retrieve($workflowId);
		if($workflow==null){
			$errorUrl = $module->errorPageUrl($mod['LBL_ERROR_NO_WORKFLOW']);
			$util->redirectTo($errorUrl, $mod['LBL_ERROR_NO_WORKFLOW']);
			return;
		}


		$smarty->assign("workflow", $workflow);
		$smarty->assign("returnUrl", vtlib_purify($request["return_url"]));
		$smarty->assign("task", $task);
		$smarty->assign("taskType", $taskClass);
		$smarty->assign("saveType", vtlib_purify($request['save_type']));

		$taskTypeInstance = VTTaskType::getInstanceFromTaskType($taskClass);
		$taskTemplateClass = $tm->retrieveTemplatePath($module->name, $taskTypeInstance);
		$smarty->assign("taskTemplate", $taskTemplateClass);

		$et = VTWSEntityType::usingGlobalCurrentUser($workflow->moduleName);
		$smarty->assign("entityType", $et);
		$smarty->assign('entityName', $workflow->moduleName);
		$smarty->assign("fieldNames", $et->getFieldNames());
		$repeat_date = $task->calendar_repeat_limit_date;
		if(!empty ($repeat_date)){
		    $repeat_date = DateTimeField::convertToUserFormat($repeat_date);
		}
		$smarty->assign('REPEAT_DATE',$repeat_date);
		$dateFields = array();
		$fieldTypes = $et->getFieldTypes();
		$fieldLabels = $et->getFieldLabels();
		foreach($fieldTypes as $name => $type){
			if($type->type=='Date' || $type->type=='DateTime'){
				$dateFields[$name] = $fieldLabels[$name];
			}
		}

		$smarty->assign('dateFields', $dateFields);


		if($task->trigger!=null){
			$trigger = $task->trigger;
			$days = $trigger['days'];
			if ($days < 0){
				$days*=-1;
				$direction = 'before';
			}else{
				$direction = 'after';
			}
			$smarty->assign('trigger', array('days'=>$days, 'direction'=>$direction,
			  'field'=>$trigger['field']));
		}
		$metaVariables = $task->getMetaVariables();

		$date = new DateTimeField(null);
		$time = substr($date->getDisplayTime(), 0, 5);
		$smarty->assign("META_VARIABLES",$metaVariables);
		$smarty->assign("SYSTEM_TIMEZONE",$db_timezone);
		$smarty->assign("USER_TIME",$task->formatTimeForTimePicker($time));
		$smarty->assign("USER_DATE", $date->getDisplayDate());
		$smarty->assign("MOD", array_merge(
			return_module_language($current_language,'Settings'),
			return_module_language($current_language, 'Calendar'),
			return_module_language($current_language, $module->name)));
		$smarty->assign("APP", $app_strings);
		$smarty->assign("dateFormat", parse_calendardate($app_strings['NTC_DATE_FORMAT']));
		$smarty->assign("IMAGE_PATH",$image_path);
		$smarty->assign("THEME", $theme);
		$smarty->assign("MODULE_NAME", $module->label);
		$smarty->assign("PAGE_NAME", $mod['LBL_EDIT_TASK']);
		$smarty->assign("PAGE_TITLE", $mod['LBL_EDIT_TASK_TITLE']);

		$users = $group = array();
		$users = get_user_array();
		$group = get_group_array();
		foreach($group as $id => $name) {
			$users[$id] = $name;
		}
		$smarty->assign('ASSIGNED_TO', $users);
		$smarty->assign("module", $module);
		$smarty->display("{$module->name}/EditTask.tpl");
	}
	vtTaskEdit($adb, $_REQUEST, $current_language, $app_strings);
?>