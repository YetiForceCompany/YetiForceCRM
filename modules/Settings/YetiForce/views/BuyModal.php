<?php

/**
 * YetiForce product Modal.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Offline registration modal view class.
 */
class Settings_YetiForce_BuyModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public $successBtn = 'LBL_SHOP_PROCEED_TO_CHECKOUT';

	/** {@inheritdoc} */
	public $successBtnIcon = 'far fa-credit-card';

	/**
	 * Header class.
	 *
	 * @var string
	 */
	public $headerClass = 'modal-header-xl';

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request): void
	{
		$qualifiedModuleName = $request->getModule(false);
		$this->modalIcon = 'yfi-marketplace';
		$this->pageTitle = \App\Language::translate('LBL_BUY', $qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request): void
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$productId = $request->isEmpty('productId') ? '' : $request->getByType('productId', \App\Purifier::ALNUM2);
		$product = \App\YetiForce\Shop::getProduct($request->getByType('product', \App\Purifier::ALNUM2), $productId);

		$viewer->assign('VARIABLE', $product->getVariable());
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('PRODUCT', $product);
		$viewer->assign('IMAGE', $product->getImage());
		$viewer->assign('PAYPAL_URL', \App\YetiForce\Shop::getPaypalUrl());
		$viewer->assign('RECORD', Settings_Companies_Record_Model::getInstance());
		$viewer->assign('FORM_FIELDS', (new \App\YetiForce\Order())->getFieldInstances());
		$viewer->view('BuyModal.tpl', $qualifiedModuleName);
	}
}
