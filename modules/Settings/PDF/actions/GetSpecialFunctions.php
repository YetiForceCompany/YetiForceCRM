<?php

/**
 * Returns special functions for PDF Settings
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */
class Settings_PDF_GetSpecialFunctions_Action extends Vtiger_Action_Controller
{

	function checkPermission(Vtiger_Request $request)
	{
		return true;
	}

	function process(Vtiger_Request $request)
	{
		$forModule = $request->get('for_module');

		$output = Settings_PDF_Module_Model::getSpecialFunctions($forModule);

		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}
