<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class ModComments_Save_Action extends Vtiger_Save_Action
{
	public function process(\App\Request $request)
	{
		$request->set('assigned_user_id', App\User::getCurrentUserId());
		$recordModel = $this->saveRecord($request);
		$responseFieldsToSent = ['reasontoedit', 'commentcontent'];
		foreach ($responseFieldsToSent as $fieldName) {
			$result[$fieldName] = $recordModel->getDisplayValue($fieldName);
		}
		$result['success'] = true;
		$result['modifiedtime'] = \App\Fields\DateTime::formatToViewDate($recordModel->get('modifiedtime'));
		$result['modifiedtimetitle'] = \App\Fields\DateTime::formatToDay($recordModel->get('modifiedtime'));

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}
