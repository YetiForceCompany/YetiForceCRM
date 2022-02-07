<?php

/**
 * Export to XML Class for MappedFields Settings.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_MappedFields_ExportTemplate_Action extends Settings_Vtiger_Index_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordId = $request->getInteger('id');
		$moduleInstance = Settings_MappedFields_Module_Model::getInstanceById($recordId);

		header('content-type: application/xml; charset=utf-8');
		header('pragma: public');
		header('cache-control: private');
		header('content-disposition: attachment; filename="' . $recordId . '_mftemplate.xml"');
		header('content-description: PHP Generated Data');

		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->preserveWhiteSpace = false;
		$xml->formatOutput = true;

		$xmlTemplate = $xml->createElement('mf_template');
		$xmlFields = $xml->createElement('fields');

		$cDataColumns = ['conditions', 'params'];
		$changeNames = ['tabid', 'reltabid'];
		foreach (Settings_MappedFields_Module_Model::$allFields as $field) {
			if (\in_array($field, $cDataColumns)) {
				$name = $xmlTemplate->appendChild($xml->createElement($field));
				$name->appendChild($xml->createCDATASection(html_entity_decode($moduleInstance->getRecord()->getRaw($field))));
			} else {
				if (\in_array($field, $changeNames)) {
					$value = \App\Module::getModuleName($moduleInstance->get($field));
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
				if ('object' == \gettype($details)) {
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
		echo $xml->saveXML();
	}

	/** {@inheritdoc} */
	public function validateRequest(App\Request $request)
	{
		$request->validateReadAccess();
	}
}
