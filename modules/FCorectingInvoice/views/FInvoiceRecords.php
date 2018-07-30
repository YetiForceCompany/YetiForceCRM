<?php

/**
 * FCorectingInvoice FInvoiceRecords View.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Rafal Pospiech <r.pospiech@yetiforce.com>
 */
class FCorectingInvoice_FInvoiceRecords_View extends Vtiger_IndexAjax_View
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('get');
	}

	/**
	 * {@inheritdoc}
	 */
	public function get(\App\Request $request)
	{
		$moduleName = 'FInvoice';
		$recordModel = FInvoice_Record_Model::getInstanceById($request->getInteger('record'));
		$moduleModel = $recordModel->getModule();
		$viewer = \Vtiger_Viewer::getInstance();
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('VIEW_MODEL', $recordModel);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODULE_TYPE', $moduleModel->getModuleType());
		return $viewer->view('DetailViewInventoryView.tpl');
	}
}
