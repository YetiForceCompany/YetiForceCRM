<?php

/**
 * Auto assign record View Class
 * @package YetiForce.ModalView
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_AutoAssignRecord_View extends Vtiger_BasicModal_View
{

	/**
	 * Checking permission 
	 * @param \App\Request $request
	 * @return boolean
	 * @throws \Exception\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		$recordId = $request->get('record');
		if (!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $request->getModule());
			if ($recordModel && $recordModel->isEditable()) {
				return true;
			}
		}
		throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
	}

	/**
	 * Function get modal size
	 * @param \App\Request $request
	 * @return string
	 */
	public function getSize(\App\Request $request)
	{
		return 'modal-lg';
	}

	/**
	 * Process
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

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
	 * Function to get the list of Css models to be included
	 * @param \App\Request $request
	 * @return Vtiger_JsScript_Model[] - List of Vtiger_CssScript_Model instances
	 */
	public function getModalScripts(\App\Request $request)
	{
		$parentScriptInstances = parent::getModalScripts($request);
		$scripts = [
			'~libraries/jquery/datatables/media/js/jquery.dataTables.min.js',
			'~libraries/jquery/datatables/plugins/integration/bootstrap/3/dataTables.bootstrap.min.js'
		];
		$modalInstances = $this->checkAndConvertJsScripts($scripts);
		$scriptInstances = array_merge($modalInstances, $parentScriptInstances);
		return $scriptInstances;
	}
}
