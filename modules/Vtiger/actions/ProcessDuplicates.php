<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_ProcessDuplicates_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$module = $request->getModule();
		$records = $request->get('records');
		if ($records) {
			foreach ($records as $record) {
				$recordPermission = Users_Privileges_Model::isPermitted($module, 'EditView', $record);
				if (!$recordPermission) {
					throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
				}
			}
		}
	}

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$records = $request->get('records');
		$primaryRecord = $request->get('primaryRecord');
		$primaryRecordModel = Vtiger_Record_Model::getInstanceById($primaryRecord, $moduleName);

		$fields = $moduleModel->getFields();
		foreach ($fields as $field) {
			$fieldValue = $request->get($field->getName());
			if ($field->isEditable()) {
				$primaryRecordModel->set($field->getName(), $fieldValue);
			}
		}
		$primaryRecordModel->save();

		$deleteRecords = array_diff($records, array($primaryRecord));
		foreach ($deleteRecords as $deleteRecord) {
			$record = Vtiger_Record_Model::getInstanceById($deleteRecord, $moduleName);
			if ($record->isDeletable()) {
				$primaryRecordModel->transferRelationInfoOfRecords([$deleteRecord]);
				$record->delete();
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
