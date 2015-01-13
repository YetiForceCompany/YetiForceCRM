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
class Vtiger_Mobile_Model extends Vtiger_Base_Model {
	public function checkPermissionForOutgoingCall() {
		global $adb;
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$result = $adb->pquery( "SELECT id FROM yetiforce_mobile_keys WHERE user = ? AND service = ?;", array( $currentUser->getId() , 'pushcall' ), true );
		if($adb->num_rows($result) > 0){
			return true;
		}
		return false;
	}
	public function performCall( $record = false, $phoneNumber = false ) {
		global $adb;
		$return = false;
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if($phoneNumber){
			$result = $adb->pquery('INSERT INTO yetiforce_mobile_pushcall (`user`, `number`) VALUES (?, ?);', array( $currentUser->getId() , $phoneNumber ));
			$return = true;
		}
		return $return;
	}
}