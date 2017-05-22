<?php

/**
 * Products widget view class
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Products_Widget_View extends Vtiger_Index_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('showProductsServices');
	}

	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function showProductsServices(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$fromModule = $request->get('fromModule');
		$mod = $request->get('mod');
		$viewer = $this->getViewer($request);
		$moduleModel = Products_SummaryWidget_Model::getCleanInstance();
		$moduleModel->getProductsServices($request, $viewer);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORDID', $request->get('record'));
		$viewer->assign('SOURCE_MODULE', $fromModule);
		$viewer->assign('RELATED_MODULE', $mod);
		$viewer->view('widgets/ProductsServices.tpl', $moduleName);
	}
}
