<?php
/**
 * Record collectors task file.
 *
 * @package 	WorkflowTask
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Record collectors task class.
 */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

class VTRecordCollector extends VTTask
{
	/** @var bool Performs the task immediately after saving. */
	public $executeImmediately = true;

	/** {@inheritdoc} */
	public function getFieldNames()
	{
		return ['recordCollector'];
	}

	/** {@inheritdoc} */
	public function doTask($recordModel)
	{
		$moduleName = $recordModel->getModuleName();
		$value = [];
		$value['module'] = $moduleName;
		if (!empty($this->recordCollector)) {
			$recordCollector = \App\RecordCollector::getInstance('\App\RecordCollectors\\' . $this->recordCollector, $moduleName);
			foreach ($recordCollector->modulesFieldsMap[$moduleName] as $key => $searchField) {
				if ('' !== $recordModel->get($searchField)) {
					$value[$key] = $recordModel->get($searchField);
					break;
				}
			}
			if (!empty($value)) {
				$recordCollector->setRequest(new \App\Request($value, false));
				$response = $recordCollector->search();
				foreach ($response['fields'] as $fieldName => $values) {
					$recordModel->set($fieldName, $values['data'][0]['raw']);
				}
				$recordModel->setHandlerExceptions(['disableHandlerClasses' => ['Vtiger_Workflow_Handler']]);
				$recordModel->save();
			}
		}
	}
}
