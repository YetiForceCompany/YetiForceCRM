<?php

/**
 * Settings RealizationProcesses index view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_RealizationProcesses_Index_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		\App\Log::trace('Entering Settings_RealizationProcesses_Index_View::process() method ...');
		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$statusNotModify = Settings_RealizationProcesses_Module_Model::getStatusNotModify();
		$viewer->assign('STATUS_NOT_MODIFY', $statusNotModify);
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->view('Index.tpl', $qualifiedModule);
		\App\Log::trace('Exiting Settings_RealizationProcesses_Index_View::process() method ...');
	}
}
