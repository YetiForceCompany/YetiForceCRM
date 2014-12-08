<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Reminder_UIType extends Vtiger_Date_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Reminder.tpl';
	}
    
    /**
	 * Function to get the Detailview template name for the current UI Type Object
	 * @return <String> - Template Name
	 */
	public function getDetailViewTemplateName() {
		return 'uitypes/ReminderDetailView.tpl';
	}
    
    /**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value) {
        $reminder_value = '';
        $reminder_time = $this->getEditViewDisplayValue($value);
		if(!empty($reminder_time[0])){
			$reminder_value = $reminder_time[0].' '.vtranslate('LBL_DAYS');
		}
		if(!empty($reminder_time[1])){
			$reminder_value = $reminder_value.' '.$reminder_time[1].' '.vtranslate('LBL_HOURS');
		}
		if(!empty($reminder_time[2])){
			$reminder_value = $reminder_value.' '.$reminder_time[2].' '.vtranslate('LBL_MINUTES');
		}
		
		return $reminder_value;
	}
    
    /**
	 * Function to get the display value in edit view
	 * @param $value
	 * @return converted value
	 */
	public function getEditViewDisplayValue($value) {
		if($value != 0 ){
            $rem_days = floor($value/(24*60));
            $rem_hrs = floor(($value-$rem_days*24*60)/60);
            $rem_min = ($value-($rem_days*24*60))%60;
            $reminder_time = array($rem_days , $rem_hrs, $rem_min);
            return $reminder_time;
        }else{
            return '';
        }
	}
    
}