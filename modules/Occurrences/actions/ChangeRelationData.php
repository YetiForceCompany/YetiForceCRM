<?php

/**
 * Action change relation data.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author Arkadiusz Dudek <a.dudekk@yetiforce.com>
 */
class Occurrences_ChangeRelationData_Action extends Vtiger_BasicAjax_Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$recordModel = \Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
		if (!$recordModel->isEditable() || !Vtiger_Record_Model::getInstanceById($request->getInteger('fromRecord'))->isEditable()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$parentRecordId = $request->getInteger('fromRecord');
		$relationId = $request->getInteger('relationId');
		$parentRecord = \Vtiger_Record_Model::getInstanceById($recordId);
		$relationView = \Vtiger_RelationListView_Model::getInstance($parentRecord, $moduleName, $relationId);
		$updateData = [];
		foreach ($relationView->getHeaders() as $fieldModel) {
			if (!$fieldModel->getTableName() && $request->has($fieldModel->getName())) {
				$value = $request->getByType($fieldModel->getName(), 'Text');
				$fieldModel->getUITypeModel()->validate($value, true);
				$updateData[$fieldModel->getColumnName()] = $fieldModel->getUITypeModel()->getDBValue($value);
			}
		}

		#echo $relationView->getRelationModel()->getRelationModuleName();
		#die();
		$result = $relationView->getRelationModel()->getTypeRelationModel()->updateRelationData($parentRecordId, $recordId, $updateData);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
