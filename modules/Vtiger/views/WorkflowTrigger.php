<?php

/**
 * Vtiger WorkflowTrigger view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_WorkflowTrigger_View extends Vtiger_BasicModal_View
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModuleActionPermission($request->getModule(), 'WorkflowTrigger')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
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

	/**
	 * {@inheritdoc}
	 */
	public function getModalScripts(App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'~libraries/jstree/dist/jstree.js',
			'~layouts/resources/libraries/jstree.category.js'
		]), parent::getModalScripts($request));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModalCss(App\Request $request)
	{
		return array_merge($this->checkAndConvertCssStyles([
			'~libraries/jstree-bootstrap-theme/dist/themes/proton/style.css',
		]), parent::getModalCss($request));
	}
}
