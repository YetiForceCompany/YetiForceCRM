<?php

/**
 * Import View Class for MappedFields Settings
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MappedFields_Import_View extends Settings_Vtiger_BasicModal_View
{

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		echo '<div class="modal fade" id="mfImport"><div class="modal-dialog"><div class="modal-content">';
	}

	public function process(Vtiger_Request $request)
	{
		
		\App\Log::trace('Entering ' . __METHOD__ . '() method ...');

		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$this->preProcess($request);
		$viewer->view('Import.tpl', $qualifiedModule);
		$this->postProcess($request);

		\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');
	}
}
