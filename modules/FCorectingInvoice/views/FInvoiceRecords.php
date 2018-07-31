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
	 * Get FInvoice inventory view.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return \html
	 */
	public function get(\App\Request $request)
	{
		$recordModel = FInvoice_Record_Model::getInstanceById($request->getInteger('record'));
		$viewer = \Vtiger_Viewer::getInstance();
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE_NAME', 'FInvoice');
		return $viewer->view('DetailViewInventoryView.tpl');
	}
}
