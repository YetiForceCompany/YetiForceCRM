<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class HelpDesk_ConvertFAQ_Action extends Vtiger_Action_Controller {

	public function checkPermission(Vtiger_Request $request) {
		$recordPermission = Users_Privileges_Model::isPermitted('Faq', 'EditView');

		if(!$recordPermission) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$result = array();
		if (!empty ($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

			$faqRecordModel = Faq_Record_Model::getInstanceFromHelpDesk($recordModel);

			$answer = $faqRecordModel->get('faq_answer');
			if ($answer) {
				$faqRecordModel->save();
				header("Location: ".$faqRecordModel->getDetailViewUrl());
			} else {
				header("Location: ".$faqRecordModel->getEditViewUrl()."&parentId=$recordId&parentModule=$moduleName");
			}
		}
	}
}
