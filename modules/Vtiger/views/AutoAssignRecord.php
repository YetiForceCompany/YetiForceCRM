<?php

/**
 * Auto assign record View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_AutoAssignRecord_View extends Vtiger_BasicModal_View
{
	/**
	 * Checking permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return bool
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!$request->isEmpty('record', true)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
			if ($recordModel && $recordModel->isEditable()) {
				return true;
			}
		}
		throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
	}

	/**
	 * Function get modal size.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function getSize(\App\Request $request)
	{
		return 'modal-lg';
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$autoAssignModel = Settings_Vtiger_Module_Model::getInstance('Settings:AutomaticAssignment');
		$autoAssignRecord = $autoAssignModel->searchRecord($recordModel);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('AUTO_ASSIGN_RECORD', $autoAssignRecord);
		$this->preProcess($request);
		$viewer->view('AutoAssignRecord.tpl', $moduleName);
		$this->postProcess($request);
	}

	/**
	 * Function to get the list of Css models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_JsScript_Model[] - List of Vtiger_CssScript_Model instances
	 */
	public function getModalScripts(\App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'~libraries/datatables.net/js/jquery.dataTables.js',
			'~libraries/datatables.net-bs4/js/dataTables.bootstrap4.js',
			'~libraries/datatables.net-responsive/js/dataTables.responsive.js',
			'~libraries/datatables.net-responsive-bs4/js/responsive.bootstrap4.js'
		]), parent::getModalScripts($request));
	}
}
