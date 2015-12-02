<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class OSSMailTemplates_GetTemplates_Action extends Vtiger_Action_Controller
{

	function checkPermission(Vtiger_Request $request)
	{
		return;
	}

	public function process(Vtiger_Request $request)
	{

		$moduleName = $request->getModule();
		$output = array();

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$output = $moduleModel->getTemplates();

		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}
