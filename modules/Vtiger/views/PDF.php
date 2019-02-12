<?php

/**
 * Export PDF Modal View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Maciej Stencel <m.stencel@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_PDF_View extends Vtiger_BasicModal_View
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($moduleName, 'ExportPdf')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!$request->isEmpty('record') && !\App\Privilege::isPermitted($moduleName, 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$this->preProcess($request);
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$view = $request->getByType('fromview', 1);
		$allRecords = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		$handlerClass = Vtiger_Loader::getComponentClassName('Model', 'PDF', $moduleName);
		$pdfModel = new $handlerClass();

		$viewer = $this->getViewer($request);
		if ($view === 'Detail') {
			$viewer->assign('TEMPLATES', $pdfModel->getActiveTemplatesForRecord($recordId, $view, $moduleName));
		} elseif ($view === 'List') {
			$viewer->assign('TEMPLATES', $pdfModel->getActiveTemplatesForModule($moduleName, $view));
		}
		$viewer->assign('ALL_RECORDS', $allRecords);
		$viewer->assign('EXPORT_VARS', [
			'record' => $recordId,
			'fromview' => $view,
		]);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->view('ExportPDF.tpl', $moduleName);
		$this->postProcess($request);
	}
}
