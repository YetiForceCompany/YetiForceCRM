<?php

/**
 * Products widget view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Products_Widget_View extends Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showProductsServices');
	}

	public function showProductsServices(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$fromModule = $request->getByType('fromModule');
		$mod = $request->getByType('mod', 1);
		$viewer = $this->getViewer($request);
		$moduleModel = Products_SummaryWidget_Model::getCleanInstance();
		$moduleModel->getProductsServices($request, $viewer);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORDID', $request->getInteger('record'));
		$viewer->assign('SOURCE_MODULE', $fromModule);
		$viewer->assign('RELATED_MODULE', $mod);
		$viewer->view('Detail/Widget/ProductsServices.tpl', $moduleName);
	}
}
