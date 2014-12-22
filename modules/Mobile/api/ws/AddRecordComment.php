<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/SaveRecord.php';

class Mobile_WS_AddRecordComment extends Mobile_WS_SaveRecord {
	
	function saveCommentToHelpDesk($commentcontent, $record, $user) {
		global $current_user;
		$current_user = $user;
		
		$targetModule = 'HelpDesk';
		$recordComponents = vtws_getIdComponents($record);
		
		$focus = CRMEntity::getInstance('HelpDesk');
		$focus->retrieve_entity_info($recordComponents[1], $targetModule);
		$focus->id = $recordComponents[1];
		$focus->mode = 'edit';
		$focus->column_fields['comments'] = $commentcontent;
		$focus->save($targetModule);
		return false;
	}
	
	function process(Mobile_API_Request $request) {

		$values = Zend_Json::decode($request->get('values'));
		$relatedTo = $values['related_to'];
		$commentContent = $values['commentcontent'];
		
		$user = $this->getActiveUser();
		
		$targetModule = '';
		if (!empty($relatedTo) && Mobile_WS_Utils::detectModulenameFromRecordId($relatedTo) == 'HelpDesk') {
			$targetModule = 'HelpDesk';
		} else {
			$targetModule = 'ModComments';
		}
		
		$response = false;
		if ($targetModule == 'HelpDesk') {
			$response = $this->saveCommentToHelpDesk($commentContent, $relatedTo, $user);
		} else {
			if (vtlib_isModuleActive($targetModule)) {
				$request->set('module', $targetModule);
				$values['assigned_user_id'] = sprintf('%sx%s', Mobile_WS_Utils::getEntityModuleWSId('Users'), $user->id);
				$request->set('values', Zend_Json::encode($values) );
				
				$response = parent::process($request);
			}
		}
		return $response;
	}
}