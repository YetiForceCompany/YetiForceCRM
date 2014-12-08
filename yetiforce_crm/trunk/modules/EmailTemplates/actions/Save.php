<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class EmailTemplates_Save_Action extends Vtiger_Save_Action {
	
	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$recordModel = new EmailTemplates_Record_Model();
		$recordModel->setModule($moduleName);
		
		if(!empty($record)) {
			$recordModel->setId($record);
		}

		$recordModel->set('templatename', $request->get('templatename'));
		$recordModel->set('description', $request->get('description'));
		$recordModel->set('subject', $request->get('subject'));
		$recordModel->set('body', $request->get('templatecontent'));
		
		$recordModel->save();

		$loadUrl = $recordModel->getDetailViewUrl();
		header("Location: $loadUrl");
	}
    
}