<?php

/**
 * Export to XML Class for PDF Settings.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Workflows_ExportWorkflow_Action extends Settings_Vtiger_Index_Action
{
	public function process(App\Request $request)
	{
		$recordId = $request->getInteger('id');
		$workflowModel = Settings_Workflows_Record_Model::getInstance($recordId);
		$workflowObject = $workflowModel->getWorkflowObject();
		$workflowMethods = [];

		header('content-type: application/xml; charset=utf-8');
		header('pragma: public');
		header('cache-control: private');
		header('content-disposition: attachment; filename="' . $recordId . '_workflow.xml"');
		header('content-description: PHP Generated Data');

		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->preserveWhiteSpace = false;
		$xml->formatOutput = true;

		$xmlTemplate = $xml->createElement('workflow');
		$xmlFields = $xml->createElement('fields');
		$xmlField = $xml->createElement('field');

		$cDataColumns = ['conditions'];
		foreach (Settings_Workflows_Module_Model::$allFields as $field) {
			if ($workflowModel->has($field)) {
				if (\in_array($field, $cDataColumns)) {
					$name = $xmlField->appendChild($xml->createElement($field));
					$name->appendChild($xml->createCDATASection(json_encode($workflowModel->get($field))));
				} else {
					$value = $workflowModel->get($field);
					$xmlColumn = $xml->createElement($field, html_entity_decode($value, ENT_COMPAT, 'UTF-8'));
				}
			} elseif (isset($workflowObject->{$field})) {
				$value = $workflowObject->{$field};
				$xmlColumn = $xml->createElement($field, html_entity_decode($value, ENT_COMPAT, 'UTF-8'));
			}
			$xmlField->appendChild($xmlColumn);
		}

		$xmlFields->appendChild($xmlField);
		$xmlTemplate->appendChild($xmlFields);

		$xmlTasks = $xml->createElement('workflow_tasks');
		foreach ($workflowModel->getTasksForExport() as $task) {
			$xmlTask = $xml->createElement('workflow_task');
			$xmlColumn = $xml->createElement('summary', html_entity_decode($task['summary'], ENT_COMPAT, 'UTF-8'));
			$xmlTask->appendChild($xmlColumn);

			if (false !== strpos($task['task'], 'VTEntityMethodTask')) {
				require_once 'modules/com_vtiger_workflow/tasks/VTEntityMethodTask.php';
				$taskObject = unserialize(html_entity_decode($task['task']));
				$method = Settings_Workflows_Module_Model::exportTaskMethod($taskObject->methodName);

				if (!\array_key_exists($method['workflowtasks_entitymethod_id'], $method)) {
					$workflowMethods[$method['workflowtasks_entitymethod_id']] = $method;
				}
			}

			$name = $xmlTask->appendChild($xml->createElement('task'));
			$name->appendChild($xml->createCDATASection(base64_encode($task['task'])));

			$xmlTasks->appendChild($xmlTask);
			$xmlTemplate->appendChild($xmlTasks);
		}

		$xmlMethods = $xml->createElement('workflow_methods');
		foreach ($workflowMethods as $method) {
			$xmlMethod = $xml->createElement('workflow_method');
			$xmlMethod->appendChild($xml->createElement('module_name', html_entity_decode($method['module_name'], ENT_COMPAT, 'UTF-8')));
			$xmlMethod->appendChild($xml->createElement('method_name', html_entity_decode($method['method_name'], ENT_COMPAT, 'UTF-8')));
			$xmlMethod->appendChild($xml->createElement('function_path', html_entity_decode($method['function_path'], ENT_COMPAT, 'UTF-8')));
			$xmlMethod->appendChild($xml->createElement('function_name', html_entity_decode($method['function_name'], ENT_COMPAT, 'UTF-8')));
			$script = $xmlMethod->appendChild($xml->createElement('script_content'));
			$script->appendChild($xml->createCDATASection(html_entity_decode($method['script_content'])));

			$xmlMethods->appendChild($xmlMethod);
			$xmlTemplate->appendChild($xmlMethods);
		}

		$xml->appendChild($xmlTemplate);
		echo $xml->saveXML();
	}

	/** {@inheritdoc} */
	public function validateRequest(App\Request $request)
	{
		$request->validateReadAccess();
	}
}
