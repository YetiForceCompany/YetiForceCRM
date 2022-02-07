<?php

/**
 * Auto assign record View Class.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_AutoAssignRecord_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-xl';

	/** {@inheritdoc} */
	public $modalIcon = 'yfi yfi-automatic-assignment';

	/** {@inheritdoc} */
	public $showFooter = false;

	/** {@inheritdoc} */
	public $pageTitle = 'BTN_ASSIGN_TO';

	/** @var \Vtiger_Record_Model Record model. */
	private $recordModel;

	/** @var \App\AutoAssign Record model. */
	private $autoAssignModel;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$this->recordModel = $request->isEmpty('record', true) ? null : Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		if (!$this->recordModel || !$this->recordModel->isPermitted('AutoAssignRecord') || !$this->recordModel->isEditable() || !($this->autoAssignModel = \App\AutoAssign::getAutoAssignForRecord($this->recordModel, \App\AutoAssign::MODE_MANUAL))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD', $this->recordModel);
		$viewer->assign('CURRENT_OWNER', $this->recordModel->get('assigned_user_id'));
		$viewer->assign('AUTO_ASSIGN_RECORD', $this->autoAssignModel);
		$viewer->view('AutoAssignRecord.tpl', $moduleName);
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts([
			'~libraries/datatables.net/js/jquery.dataTables.js',
			'~libraries/datatables.net-bs4/js/dataTables.bootstrap4.js',
			'~libraries/datatables.net-responsive/js/dataTables.responsive.js',
			'~libraries/datatables.net-responsive-bs4/js/responsive.bootstrap4.js'
		]), parent::getModalScripts($request));
	}

	/** {@inheritdoc} */
	public function getModalCss(App\Request $request)
	{
		return array_merge($this->checkAndConvertCssStyles([
			'~libraries/jstree-bootstrap-theme/dist/themes/proton/style.css',
			'~libraries/datatables.net-bs4/css/dataTables.bootstrap4.css',
			'~libraries/datatables.net-responsive-bs4/css/responsive.bootstrap4.css'
		]), parent::getModalCss($request));
	}
}
