<?php

/**
 * Import View Class for MappedFields Settings
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_MappedFields_Import_View extends Vtiger_BasicModal_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		true;
	}

	public function preProcess(Vtiger_Request $request)
	{
		echo '<div class="modal fade" id="mfImport"><div class="modal-dialog"><div class="modal-content">';
	}

	function process(Vtiger_Request $request)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '(' . print_r($request, true) . ') method ...');

		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$this->preProcess($request);
		$viewer->view('Import.tpl', $qualifiedModule);
		$this->postProcess($request);

		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}
}
