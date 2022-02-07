<?php
/**
 * Merge records action.
 *
 * @package Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Merge records class.
 */
class Vtiger_MergeRecords_Action extends Vtiger_Mass_Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'Merge')) {
			throw new \App\Exceptions\NoPermitted('ERR_NOT_ACCESSIBLE', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
		$primaryRecord = $request->getInteger('record');
		$migrate = [];
		$result = false;
		foreach ($request->getArray('records', 'Integer') as $record) {
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
			\App\Log::error($ex->__toString());
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
