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

class Vtiger_ProcessDuplicates_Action extends \App\Controller\Action
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!\App\Privilege::isPermitted($moduleName, 'DuplicatesHandling', $request->getInteger('primaryRecord'))) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$records = $request->getArray('records');
		foreach ($records as $record) {
			if (!is_numeric($record) || !\App\Privilege::isPermitted($moduleName, 'EditView', $record)) {
				throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
	}

	/**
	 * Process
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$records = $request->getArray('records');
		$primaryRecord = $request->getInteger('primaryRecord');
		$primaryRecordModel = Vtiger_Record_Model::getInstanceById($primaryRecord, $moduleName);

		$fields = $moduleModel->getFields();
		foreach ($fields as $field) {
			$fieldValue = $request->get($field->getName());
			if ($field->isEditable()) {
				$primaryRecordModel->set($field->getName(), $fieldValue);
			}
		}
		$primaryRecordModel->save();
		$deleteRecords = array_diff($records, [$primaryRecord]);
		foreach ($deleteRecords as $deleteRecord) {
			$record = Vtiger_Record_Model::getInstanceById($deleteRecord, $moduleName);
			if ($record->privilegeToMoveToTrash()) {
				$primaryRecordModel->transferRelationInfoOfRecords([$deleteRecord]);
				$record->changeState('Trash');
			} elseif ($record->privilegeToDelete()) {
				$primaryRecordModel->transferRelationInfoOfRecords([$deleteRecord]);
				$record->delete();
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
