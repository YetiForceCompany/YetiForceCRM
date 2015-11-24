<?php

/**
 * @package YetiForce.Modal
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_GenerateModal_View extends Vtiger_BasicModal_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		true;
	}

	public function preProcess(Vtiger_Request $request)
	{
		echo '<div class="modal fade"><div class="modal-dialog"><div class="modal-content">';
	}

	function process(Vtiger_Request $request)
	{
		$log = vglobal('log');
		$log->debug('Entering ' . __CLASS__ . '::' . __METHOD__ . '(' . print_r($request, true) . ') method ...');

		$qualifiedModule = $request->getModule(false);
		$sourceModule = $request->get('source');
		$recordId = $request->get('record');
		$viewer = $this->getViewer($request);

		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'MappedFields', $sourceModule);
		$mfModel = new $handlerClass();
		$templates = $mfModel->getActiveTemplatesForRecord($recordId, 'Detail', $sourceModule);

		$viewer->assign('TEMPLATES', $templates);
		$viewer->assign('RECORD', $recordId);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$this->preProcess($request);
		$viewer->view('GenerateModal.tpl', $qualifiedModule);
		$this->postProcess($request);
		$log->debug('Exiting ' . __CLASS__ . '::' . __METHOD__ . ' method ...');
	}
}
