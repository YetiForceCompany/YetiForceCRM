<?php

/**
 * Export to XML Class for PDF Settings.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_PDF_ExportTemplate_Action extends Settings_Vtiger_Index_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$recordId = $request->getInteger('id');
		$pdfModel = Vtiger_PDF_Model::getInstanceById($recordId);

		header('content-type: application/xml; charset=utf-8');
		header('pragma: public');
		header('cache-control: private');
		header('content-disposition: attachment; filename="' . $recordId . '_pdftemplate.xml"');
		header('content-description: PHP Generated Data');

		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->preserveWhiteSpace = false;
		$xml->formatOutput = true;

		$xmlTemplate = $xml->createElement('pdf_template');
		$xmlFields = $xml->createElement('fields');
		$xmlField = $xml->createElement('field');

		$cDataColumns = ['header_content', 'body_content', 'footer_content', 'conditions'];
		foreach (Settings_PDF_Module_Model::$allFields as $field) {
			if (\in_array($field, $cDataColumns)) {
				$name = $xmlField->appendChild($xml->createElement($field));
				$name->appendChild($xml->createCDATASection(html_entity_decode($pdfModel->getRaw($field))));
			} elseif ('watermark_image' === $field) {
				if (file_exists($pdfModel->get($field))) {
					$watermarkPath = $pdfModel->get($field);
					$im = file_get_contents($watermarkPath);
					$imData = base64_encode($im);

					$xmlColumn = $xml->createElement('imageblob', $imData);
					$xmlField->appendChild($xmlColumn);
					$value = $watermarkPath;
				} else {
					$value = '';
				}
				$xmlColumn = $xml->createElement($field, $value);
			} else {
				$value = $pdfModel->get($field);
				$xmlColumn = $xml->createElement($field, html_entity_decode($value, ENT_COMPAT, 'UTF-8'));
			}
			$xmlField->appendChild($xmlColumn);
		}

		$xmlFields->appendChild($xmlField);
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
