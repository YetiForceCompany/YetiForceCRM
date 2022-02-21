<?php

/**
 * Vtiger WorkflowTrigger view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_WorkflowTrigger_View extends Vtiger_BasicModal_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$recordId = $request->getInteger('record');
		if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		if (!\App\Privilege::isPermitted($moduleName, 'WorkflowTrigger') && !$recordModel->isEditable()
		|| !($recordModel->isPermitted('EditView') && App\Privilege::isPermitted($moduleName, 'WorkflowTriggerWhenRecordIsBlocked') && $recordModel->isBlocked())
		) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$this->preProcess($request);
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		$tree = Vtiger_WorkflowTrigger_Model::getTreeWorkflows($moduleName, $record);
		$viewer = $this->getViewer($request);
		$viewer->assign('TREE', \App\Json::encode($tree));
		$viewer->view('WorkflowTrigger.tpl', $moduleName);
		$this->postProcess($request);
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'~libraries/jstree/dist/jstree.js',
			'~layouts/resources/libraries/jstree.category.js',
		]), parent::getModalScripts($request));
	}

	/** {@inheritdoc} */
	public function getModalCss(App\Request $request)
	{
		return array_merge($this->checkAndConvertCssStyles([
			'~libraries/jstree-bootstrap-theme/dist/themes/proton/style.css',
		]), parent::getModalCss($request));
	}
}
