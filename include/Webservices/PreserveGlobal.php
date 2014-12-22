<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class VTWS_PreserveGlobal{
	
	private static $globalData = array();
	
	static function preserveGlobal($name,$value){
		//$name store the name of the global.
		global $$name;
		//To not push null value . Ideally we should not push null value for any name
		//But current user null is dangerous so we are checking for only current user
        if(!empty($$name) || $name != 'current_user') {
			if(!is_array(VTWS_PreserveGlobal::$globalData[$name])){
				VTWS_PreserveGlobal::$globalData[$name] = array();		
			}
			
			VTWS_PreserveGlobal::$globalData[$name][] = $$name;
		}

		$$name = $value;
		return $$name;
	}
	
	static function restore($name){
		//$name store the name of the global.
		global $$name;
		
		if(is_array(VTWS_PreserveGlobal::$globalData[$name]) && count(VTWS_PreserveGlobal::$globalData[$name]) > 0){
			$$name = array_pop(VTWS_PreserveGlobal::$globalData[$name]);
		}
		$$name;
	}
	
	static function getGlobal($name){
		global $$name;
		return VTWS_PreserveGlobal::preserveGlobal($name,$$name);
	}
	
	static function flush(){
		foreach (VTWS_PreserveGlobal::$globalData as $name => $detail) {
			//$name store the name of the global.
			global $$name;
			if(is_array(VTWS_PreserveGlobal::$globalData[$name]) && count(VTWS_PreserveGlobal::$globalData[$name]) > 0) {
				$$name = array_pop(VTWS_PreserveGlobal::$globalData[$name]);
			}
		}
	}
	
}

?>
