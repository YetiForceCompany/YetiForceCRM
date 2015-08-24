<?php

/**
 * Supplies Detail View Class
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Supplies_Detail_View extends Vtiger_Detail_View
{

	/**
	 * Function returns Inventory details
	 * @param Vtiger_Request $request
	 */
	function showModuleDetailView(Vtiger_Request $request)
	{
		echo parent::showModuleDetailView($request);
		$this->showLineItemDetails($request);
	}

	function showLineItemDetails(Vtiger_Request $request)
	{
		$record = $request->get('record');
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('SUP_RECORD', $record);
		echo $viewer->view('DetailViewSupplies.tpl', Supplies_Module_Model::getModuleNameForTpl('DetailViewSupplies.tpl', $moduleName), true);
	}
}
