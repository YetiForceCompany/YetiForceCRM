<?php

/**
 * The file contains: YetiForce shop view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * YetiForce shop view class.
 */
class Settings_YetiForce_Shop_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer->assign('MODULE_NAME', $qualifiedModuleName);
		if (\App\YetiForce\Register::isRegistered()) {
			$viewer->assign('STATUS', $request->getByType('status'));
			$viewer->assign('CATEGORY', $request->isEmpty('category') ? 'All' : $request->getByType('category'));
			$viewer->assign('PRODUCTS_PREMIUM', (new \App\YetiForce\Shop())->getProducts());
		}
		$viewer->view('Shop.tpl', $qualifiedModuleName);
	}
}
