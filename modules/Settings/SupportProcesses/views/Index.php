<?php

/**
 * Settings SupportProcesses index view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_SupportProcesses_Index_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		\App\Log::trace('Entering Settings_SupportProcesses_Index_View::process() method ...');
		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);

		$ticketStatus = Settings_SupportProcesses_Module_Model::getTicketStatus();
		$ticketStatusNotModify = Settings_SupportProcesses_Module_Model::getTicketStatusNotModify();
		$viewer->assign('TICKETSTATUSNOTMODIFY', $ticketStatusNotModify);
		$viewer->assign('TICKETSTATUS', $ticketStatus);
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));

		$viewer->view('Index.tpl', $qualifiedModule);
		\App\Log::trace('Exiting Settings_SupportProcesses_Index_View::process() method ...');
	}
}
