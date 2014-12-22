<?php

/*+*******************************************************************************
 *   The contents of this file are subject to the vtiger CRM Public License Version 1.0
 *   ("License"); You may not use this file except in compliance with the License
 *   The Original Code is:  vtiger CRM Open Source
 *   The Initial Developer of the Original Code is vtiger.
 *   Portions created by vtiger are Copyright (C) vtiger.
 *   All Rights Reserved.
 * 
 *********************************************************************************/

/**
 * @author Musavir Ahmed Khan<musavir at vtiger.com>
 */

/**
 *
 * @param WebserviceId $id
 * @param String $oldPassword
 * @param String $newPassword
 * @param String $confirmPassword
 * @param Users $user 
 * 
 */
function vtws_changePassword($id, $oldPassword, $newPassword, $confirmPassword, $user) {
	vtws_preserveGlobal('current_user',$user);
	$idComponents = vtws_getIdComponents($id);
	if($idComponents[1] == $user->id || is_admin($user)) {
		$newUser = new Users();
		$newUser->retrieve_entity_info($idComponents[1], 'Users');
		if(!is_admin($user)) {
			if(empty($oldPassword)) {
				throw new WebServiceException(WebServiceErrorCode::$INVALIDOLDPASSWORD, 
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$INVALIDOLDPASSWORD));
			}
			if(!$user->verifyPassword($oldPassword)) {
				throw new WebServiceException(WebServiceErrorCode::$INVALIDOLDPASSWORD, 
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$INVALIDOLDPASSWORD));
			}
		}
		if(strcmp($newPassword, $confirmPassword) === 0) {
			$db = PearDatabase::getInstance();
			$db->dieOnError = true;
			$db->startTransaction();
			$success = $newUser->change_password($oldPassword, $newPassword, false);
			$error = $db->hasFailedTransaction();
			$db->completeTransaction();
			if($error) {
				throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, 
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$DATABASEQUERYERROR));
			}
			if(!$success) {
				throw new WebServiceException(WebServiceErrorCode::$CHANGEPASSWORDFAILURE, 
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$CHANGEPASSWORDFAILURE));
			}
		} else {
			throw new WebServiceException(WebServiceErrorCode::$CHANGEPASSWORDFAILURE, 
					vtws_getWebserviceTranslatedString('LBL_'.
							WebServiceErrorCode::$CHANGEPASSWORDFAILURE));
		}
		VTWS_PreserveGlobal::flush();
		return array('message' => 'Changed password successfully');
	}
}


?>
