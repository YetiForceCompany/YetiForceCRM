<?php

/**
 * Export to XML Class for MappedFields Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MappedFields_ExportTemplate_Action extends Settings_Vtiger_Index_Action
{

	public function process(Vtiger_Request $request)
	{
		$recordId = $request->get('id');
		$moduleInstance = Settings_MappedFields_Module_Model::getInstanceById($recordId);

		header('content-type: application/xml; charset=utf-8');
		header('Pragma: public');
		header('Cache-Control: private');
		header('Content-Disposition: attachment; filename=' . $recordId . '_mftemplate.xml');
		header('Content-Description: PHP Generated Data');

		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->preserveWhiteSpace = false;
		$xml->formatOutput = true;

		$xmlTemplate = $xml->createElement('mf_template');
		$xmlFields = $xml->createElement('fields');
		$xmlField = $xml->createElement('field');


		$cDataColumns = ['conditions', 'params'];
		$changeNames = ['tabid', 'reltabid'];
		foreach (Settings_MappedFields_Module_Model::$allFields as $field) {
			if (in_array($field, $cDataColumns)) {
				$name = $xmlTemplate->appendChild($xml->createElement($field));
				$name->appendChild($xml->createCDATASection(html_entity_decode($moduleInstance->getRecord()->getRaw($field))));
			} else {
				if (in_array($field, $changeNames)) {
					$value = vtlib\Functions::getModuleName($moduleInstance->get($field));
				} else {
					$value = $moduleInstance->get($field);
				}
				$xmlColumn = $xml->createElement($field, html_entity_decode($value, ENT_COMPAT, 'UTF-8'));
			}
			$xmlTemplate->appendChild($xmlColumn);
		}
		foreach ($moduleInstance->getMapping() as $field) {
			$xmlField = $xml->createElement('field');
			foreach ($field as $key => $details) {
				if (gettype($details) == 'object') {
					$value = $details->getFieldName();
				} else {
					$value = $details;
				}
				$xmlColumn = $xml->createElement($key, html_entity_decode($value, ENT_COMPAT, 'UTF-8'));
				$xmlField->appendChild($xmlColumn);
			}
			$xmlFields->appendChild($xmlField);
		}

		$xmlTemplate->appendChild($xmlFields);
		$xmlTemplate->appendChild($xmlFields);
		$xml->appendChild($xmlTemplate);
		print $xml->saveXML();
	}
}
