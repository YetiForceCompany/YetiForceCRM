<?php

/**
 * The file contains: YetiForce shop view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

/**
 * YetiForce shop view class.
 */
class Settings_YetiForce_Shop_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		\App\YetiForce\Shop::generateCache();
		\App\Utils\ConfReport::saveEnv();

		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->assign('MODULE_NAME', $qualifiedModuleName);
		if (\App\YetiForce\Register::isRegistered()) {
			$viewer->assign('STATUS', $request->getByType('status'));
			$viewer->assign('TAB', $request->isEmpty('tab') ? 'Premium' : $request->getByType('tab'));
			$viewer->assign('CATEGORY', $request->isEmpty('category') ? 'All' : $request->getByType('category'));
			$viewer->assign('PRODUCTS_PREMIUM', \App\YetiForce\Shop::getProducts());
			$viewer->assign('PRODUCTS_PARTNER', \App\YetiForce\Shop::getProducts('', 'Partner'));
		}
		$viewer->view('Shop.tpl', $qualifiedModuleName);
	}
}
