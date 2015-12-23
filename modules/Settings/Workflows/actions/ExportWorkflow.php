<?php

/**
 * Export to XML Class for PDF Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_Workflows_ExportWorkflow_Action extends Settings_Vtiger_Index_Action
{

	public function process(Vtiger_Request $request)
	{
		$recordId = $request->get('id');
		$workflowModel = Settings_Workflows_Record_Model::getInstance($recordId);
		$workflowObject = $workflowModel->getWorkflowObject();

		header('content-type: application/xml; charset=utf-8');
		header('Pragma: public');
		header('Cache-Control: private');
		header('Content-Disposition: attachment; filename=' . $recordId . '_workflow.xml');
		header('Content-Description: PHP Generated Data');

		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->preserveWhiteSpace = false;
		$xml->formatOutput = true;

		$xmlTemplate = $xml->createElement('workflow');
		$xmlFields = $xml->createElement('fields');
		$xmlField = $xml->createElement('field');


		$cDataColumns = ['conditions'];
		foreach (Settings_Workflows_Module_Model::$allFields as $field) {
			if ($workflowModel->has($field)) {
				if (in_array($field, $cDataColumns)) {
					$name = $xmlField->appendChild($xml->createElement($field));
					$name->appendChild($xml->createCDATASection(json_encode($workflowModel->get($field))));
				} else {
					$value = $workflowModel->get($field);
					$xmlColumn = $xml->createElement($field, html_entity_decode($value, ENT_COMPAT, 'UTF-8'));
				}
			} else {
				$value = $workflowObject->$field;
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

			$name = $xmlTask->appendChild($xml->createElement('task'));
			$name->appendChild($xml->createCDATASection(html_entity_decode($task['task'])));

			$xmlTasks->appendChild($xmlTask);
			$xmlTemplate->appendChild($xmlTasks);
		}

		$xml->appendChild($xmlTemplate);
		print $xml->saveXML();
	}
}
