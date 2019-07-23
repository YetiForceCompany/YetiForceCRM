<?php

/**
 * YetiForce product Modal
 *
 * @package   Settings
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

/**
 * Offline registration modal view class.
 */
class Settings_YetiForce_BuyModal_View extends \App\Controller\ModalSettings
{
	/**
	 * The name of the activation button.
	 *
	 * @var string
	 */
	public $successBtn = 'LBL_BUY';

	/**
	 * Set modal title.
	 *
	 * @param \App\Request $request
	 */
	public function preProcessAjax(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$this->modalIcon = 'fas fa-paypal';
		$this->pageTitle = \App\Language::translate('LBL_BUY', $qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/**
	 * Process user request.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$productName = $request->getByType('product');
		$department = $request->getByType('department');
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('PRODUCT', \App\YetiForce\Shop::getProduct($productName, $department, \App\YetiForce\Shop::getConfig()));
		$viewer->assign('PAYPAL_URL', \App\YetiForce\Shop::getPaypalUrl());
		$viewer->view('BuyModal.tpl', $qualifiedModuleName);
	}
}
