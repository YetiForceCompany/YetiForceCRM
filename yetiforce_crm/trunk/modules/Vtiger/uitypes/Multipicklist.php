<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Multipicklist_UIType extends Vtiger_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/MultiPicklist.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value) {
        if(is_array($value)){
            $value = implode(' |##| ', $value);
        }
		return str_ireplace(' |##| ', ', ', $value);
	}
    
    public function getDBInsertValue($value) {
		if(is_array($value)){
            $value = implode(' |##| ', $value);
        }
        return $value;
	}
    
    
    public function getListSearchTemplateName() {
        return 'uitypes/MultiSelectFieldSearchView.tpl';
    }
}