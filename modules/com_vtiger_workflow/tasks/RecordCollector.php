<?php
/**
 * Record collectors task file.
 *
 * @package 	WorkflowTask
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Record collectors task class.
 */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class RecordCollector extends VTTask
{
	/** @var bool Performs the task immediately after saving. */
	public $executeImmediately = true;

	/** {@inheritdoc} */
	public function getFieldNames()
	{
		return ['recordCollector', 'fieldsMap'];
	}

	/** {@inheritdoc} */
	public function doTask($recordModel)
	{
		if (empty($this->recordCollector)) {
			return;
		}
		$value = [];
		$moduleName = $recordModel->getModuleName();
		$recordCollector = \App\RecordCollector::getInstance($this->recordCollector, $moduleName);
		foreach ($recordCollector->getFieldsModule($moduleName) as $key => $searchField) {
			if ('' !== $recordModel->get($searchField)) {
				$value[$key] = $recordModel->get($searchField);
				break;
			}
		}
		if (!empty($value)) {
			$value['module'] = $moduleName;
			$recordCollector->setRequest(new \App\Request($value, false));
			$response = $recordCollector->search();
			$key = array_key_first($response['dataCounter']);
			if (!empty($this->fieldsMap)) {
				foreach ($this->fieldsMap as $fieldMapName) {
					$updateFields[$fieldMapName] = $response['fields'][$fieldMapName];
				}
			} else {
				$updateFields = $response['fields'];
			}
			foreach ($updateFields as $fieldName => $values) {
				try {
					if ($values['data'][$key]['raw']) {
						$recordModel->getField($fieldName)->getUITypeModel()->validate($values['data'][$key]['raw']);
						$recordModel->set($fieldName, $values['data'][$key]['raw']);
					}
				} catch (\Throwable $th) {
					\App\Log::error("[taxNumber => $value]Error during data validation: \n{$th->__toString()}\n", __CLASS__);
				}
			}
			$recordModel->setHandlerExceptions(['disableHandlerClasses' => ['Vtiger_Workflow_Handler']]);
			$recordModel->save();
		}
	}
}
