<?php
/**
 * Auto assign records Task Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class VTAutoAssign extends VTTask
{
	/** @var bool */
	public $executeImmediately = true;

	/**
	 * Get field names.
	 *
	 * @return array
	 */
	public function getFieldNames()
	{
		return ['template'];
	}

	/**
	 * Execute task.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function doTask($recordModel)
	{
		$autoAssignInstance = \App\AutoAssign::getInstanceById((int) $this->template);
		if ($autoAssignInstance && $autoAssignInstance->isActive(\App\AutoAssign::MODE_WORKFLOW) && $autoAssignInstance->checkConditionForRecord($recordModel) && $assignedUserId = $autoAssignInstance->getOwner()) {
			$fieldModel = $recordModel->getField('assigned_user_id');
			$cloneRecordModel = \Vtiger_Record_Model::getCleanInstance($recordModel->getModuleName());
			$cloneRecordModel->setData($recordModel->getData());
			$cloneRecordModel->ext = $recordModel->ext;
			$cloneRecordModel->isNew = false;
			$cloneRecordModel->setHandlerExceptions(['disableHandlerClasses' => ['Vtiger_Workflow_Handler']]);
			$cloneRecordModel->set($fieldModel->getName(), $assignedUserId);
			$cloneRecordModel->setDataForSave([$fieldModel->getTableName() => [$fieldModel->getColumnName() => $assignedUserId]]);
			$cloneRecordModel->save();
			$autoAssignInstance->postProcess($assignedUserId);
			foreach (array_keys($cloneRecordModel->getPreviousValue()) as $fieldName) {
				$recordModel->set($fieldName, $cloneRecordModel->get($fieldName));
			}
		}
	}
}
