<?php
/**
 * Update Invoice Related Account Fields Handler Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class VTUpdateInvoiceFields extends VTTask
{
	public $executeImmediately = true;

	public function getFieldNames()
	{
		return ['target_fieldname', 'source_fieldname'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$targetFieldName = explode('::', $this->target_fieldname);
		$targetRecordModel = \Vtiger_Record_Model::getCleanInstance($targetFieldName[1]);
		$targetFieldModel = $targetRecordModel->getField($targetFieldName[2]);
		$relationColumnName = $recordModel->getModule()->getField($targetFieldName[0])->get('column');
		$relationFieldValue = $recordModel->get($targetFieldName[0]);
		if (!empty($relationFieldValue)) {
			$columnSumValue = (new \App\Db\Query())->from($recordModel->getEntity()->table_name)->where([$relationColumnName => $relationFieldValue])->sum($this->source_fieldname);
			\App\Db::getInstance()->createCommand()->update($targetRecordModel->getEntity()->table_name, [$targetFieldModel->get('column') => $columnSumValue], [$relationColumnName => $relationFieldValue])->execute();
		} else {
			return false;
		}
	}
}
