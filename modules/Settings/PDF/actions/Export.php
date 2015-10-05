<?php

/**
 * Returns special functions for PDF Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_PDF_Export_Action extends Vtiger_Action_Controller
{

	function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	function process(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->get('frommodule');
		$templateId = $request->get('template');

		$pdf = new Settings_PDF_mPDF_Model();
		$pdf->setTemplateId($templateId);
		$pdf->setRecordId($recordId);
		$pdf->setModuleName($moduleName);

		$template = Settings_PDF_Record_Model::getInstanceById($templateId);
		if ($template->get('margin_chkbox') == 0) {
			$pdf->setMargins(
				$template->get('margin_top'),
				$template->get('margin_right'),
				$template->get('margin_bottom'),
				$template->get('margin_left')
			);
		}

		$html = '';

		$pdf->setHeader('Header', $template->get('header_content'));
		$pdf->setFooter('Footer', $template->get('footer_content'));
		$html = $template->get('body_content');

		$pdf->loadHTML($html);
		$pdf->output();
	}
}
