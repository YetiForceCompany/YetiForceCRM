<?php

/**
 * Products widget view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Products_Widget_View extends Vtiger_Index_View
{
	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showProductsServices');
	}

	public function showProductsServices(App\Request $request)
	{
		$moduleName = $request->getModule();
		$fromModule = $request->getByType('fromModule');
		$mod = current($request->getArray('mod', 'Alnum'));
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
