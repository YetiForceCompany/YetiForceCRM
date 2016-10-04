<?php

/**
 * Export PDF Modal View Class
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_PDF_View extends Vtiger_BasicModal_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		if (!Users_Privileges_Model::isPermitted($moduleName, 'ExportPdf')) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$this->preProcess($request);
		$moduleName = $request->getModule();
		$allRecords = [];
		$recordId = $request->get('record');
		$view = $request->get('fromview');
		$allRecords = Vtiger_Mass_Action::getRecordsListFromRequest($request);

		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleName);
		$pdfModel = new $handlerClass();

		$viewer = $this->getViewer($request);
		if ($view === 'Detail') {
			$viewer->assign('TEMPLATES', $pdfModel->getActiveTemplatesForRecord($recordId, $view, $moduleName));
		} elseif ($view === 'List') {
			$viewer->assign('TEMPLATES', $pdfModel->getActiveTemplatesForModule($moduleName, $view));
		}
		$postVars = [
			'record' => $recordId,
			'fromview' => $view
		];
		$viewer->assign('ALL_RECORDS', $allRecords);
		$viewer->assign('EXPORT_VARS', $postVars);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->view('ExportPDF.tpl', $moduleName);
		$this->postProcess($request);
	}
}
