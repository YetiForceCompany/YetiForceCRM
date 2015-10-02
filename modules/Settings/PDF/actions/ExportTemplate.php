<?php

/**
 * Export to XML Class for PDF Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_PDF_ExportTemplate_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	public function process(Vtiger_Request $request)
	{
		$recordId = $request->get('id');

		$pdfRecordModel = Settings_PDF_Record_Model::getInstanceById($recordId);

		header("content-type: application/xml; charset=utf-8");
		header("Pragma: public");
		header("Cache-Control: private");
		header("Content-Disposition: attachment; filename=lol.xml");
		header("Content-Description: PHP Generated Data");

		$xml = new DOMDocument('1.0', 'utf-8');

		$xmlTemplate = $xml->createElement('pdf_template');
		$xmlFields = $xml->createElement('fields');

		$xmlField = $xml->createElement('field');

		$cDataColumns = ['header_content', 'body_content', 'footer_content', 'conditions'];
		foreach (Settings_PDF_Module_Model::$allFields as $field) {
			if (in_array($field, $cDataColumns)) {
				$value = '<![CDATA[' . htmlspecialchars_decode($pdfRecordModel->getRaw($field)) . ']]>';
			} elseif ($field === 'watermark_image') {
				if (file_exists($pdfRecordModel->get($field))) {
					$watermarkPath = $pdfRecordModel->get($field);
					//$watermarkName = basename($watermarkPath);
					$im = file_get_contents($watermarkPath);
					$imData = base64_encode($im);

					$xmlColumn = $xml->createElement('imageblob', $imData);
					$xmlField->appendChild($xmlColumn);
					$value = $watermarkPath; //$watermarkName;
				} else {
					$value = '';
				}
			} else {
				$value = $pdfRecordModel->get($field);
			}
			$xmlColumn = $xml->createElement($field, html_entity_decode($value, ENT_COMPAT, 'UTF-8'));
			$xmlField->appendChild($xmlColumn);
		}

		$xmlFields->appendChild($xmlField);

		$xmlTemplate->appendChild($xmlFields);

		$xmlTemplate->appendChild($xmlFields);

		$xml->appendChild($xmlTemplate);

		print $xml->saveXML();
	}
}
