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
class Test{
	public $restler;
	
	public function post($app_name = ''){
		$adb = PearDatabase::getInstance(); $log = vglobal('log');
		$log->info('Start Test metod');
		$return = 'false';
		if ($app_name == 'PushCall')
			$return = "true";
		if ($app_name == 'PushMessage')
			$return = "true";
		if ($app_name == 'HistoryCall')
			$return = "true";
		$log->info('tart Test metod | return: '.$return);
		return $return;
	}
}
