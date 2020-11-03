<?php

/**
 * The file contains: YetiForce shop view class.
 *
 * @package View
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * YetiForce shop view class.
 */
class Settings_YetiForce_Shop_View extends Settings_Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->assign('MODULE_NAME', $qualifiedModuleName);
		$viewer->assign('STATUS', $request->getByType('status'));
		$viewer->assign('TAB', $request->isEmpty('tab') ? 'Premium' : $request->getByType('tab'));
		$viewer->assign('CATEGORY', $request->isEmpty('category') ? 'All' : $request->getByType('category'));
		$viewer->assign('PRODUCTS_PREMIUM', \App\YetiForce\Shop::getProducts());
		$viewer->assign('PRODUCTS_PARTNER', \App\YetiForce\Shop::getProducts('', 'Partner'));
		$viewer->view('Shop.tpl', $qualifiedModuleName);
	}
}
