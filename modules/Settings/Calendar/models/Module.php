<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class Settings_Calendar_Module_Model extends Settings_Vtiger_Module_Model {
	/**
	* To get the lists of View Types
	* @param $id --  user id
	* @returns <Array> $calendarViewTypes
	*/
	public static function getCalendarViewTypes(){
		$adb = PearDatabase::getInstance();

        $result = $adb->query("SELECT * FROM vtiger_calendar_default_activitytypes");
        $rows = $adb->num_rows($result);

		$calendarViewTypes = Array();
        for($i=0; $i<$rows; $i++){
			$activityTypes = $adb->query_result_rowdata($result, $i);
			$calendarViewTypes[] = array(
				'id'=>$activityTypes['id'], 
				'module'=>$activityTypes['module'], 
				'fieldname'=>$activityTypes['fieldname'], 
				'fieldlabel'=>$activityTypes['fieldname'], 
				'color' => $activityTypes['defaultcolor'],
				'active' => $activityTypes['active']
			);
        }
		return $calendarViewTypes;
	}
	/**
	* Color update
	* @param $color -- new color
	* @param $viewtypesid -- view type id 
	*/
	public static function updateColor($params){
		$adb = PearDatabase::getInstance();
		$adb->pquery('UPDATE vtiger_calendar_default_activitytypes SET defaultcolor = ? WHERE id = ?;', array( $params['color'], $params['viewtypesid'] ));
		$adb->pquery('UPDATE vtiger_calendar_user_activitytypes SET color = ? WHERE defaultid = ?;', array( $params['color'], $params['viewtypesid'] ));
	}
	
	public static function addActivityTypes($module, $fieldname, $defaultcolor){
		$adb = PearDatabase::getInstance();
		$queryResult = $adb->pquery('SELECT id, defaultcolor FROM vtiger_calendar_default_activitytypes', array());
		$insertActivityTypesSql = 'INSERT INTO vtiger_calendar_default_activitytypes (id, module, fieldname, defaultcolor) VALUES (?,?,?,?)';
		$insertActivityTypesParams = array($adb->getUniqueID('vtiger_calendar_default_activitytypes'), $module, $fieldname, $defaultcolor);
	}
	
	public static function updateActiveType($params){
		$adb = PearDatabase::getInstance();
		$active = $params['active'] == 'true' ? '1': '0';
		$adb->pquery('UPDATE vtiger_calendar_default_activitytypes SET active = ? WHERE id = ?;', array( $active, $params['viewtypesid'] ));
	}
}