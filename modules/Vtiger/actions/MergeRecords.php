<?php
/**
 * Merge records action.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Merge records class.
 */
class Vtiger_MergeRecords_Action extends Vtiger_Mass_Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'Merge')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$records = $request->getArray('records', 'Integer');
		$primaryRecord = $request->getInteger('record');
		$migrate = [];
		$result = false;
		foreach ($records as $record) {
			if ($record !== $primaryRecord) {
				$migrate[$record] = [];
			}
		}
		foreach ($moduleModel->getFields() as $field) {
			if ($request->has($field->getName()) && $request->getInteger($field->getName()) !== $primaryRecord && $field->isEditable()) {
				$migrate[$request->getInteger($field->getName())][$field->getName()] = $field->getName();
			}
		}
		try {
			\App\RecordTransfer::transfer($primaryRecord, $migrate);
			foreach (array_keys($migrate) as $recordId) {
				$recordModel = \Vtiger_Record_Model::getInstanceById($recordId);
				$recordModel->ext['modificationType'] = ModTracker_Record_Model::TRANSFER_DELETE;
				$recordModel->changeState('Trash');
			}
			$result = true;
		} catch (\Throwable $ex) {
			\App\Log::error($ex->getMessage());
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
