<?php

/**
 * YetiForce product Modal.
 *
 * @package   Settings
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Offline registration modal view class.
 */
class Settings_YetiForce_ProductModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-full';
	/**
	 * Header class.
	 *
	 * @var string
	 */
	public $headerClass = 'modal-header-xl';

	/** @var string Qualified module name. */
	public $qualifiedModuleName;

	/**
	 * Set modal title.
	 *
	 * @param \App\Request $request
	 */
	public function preProcessAjax(App\Request $request)
	{
		$this->qualifiedModuleName = $request->getModule(false);
		$this->modalIcon = 'yfi-prodprouct-preview';
		$this->pageTitle = \App\Language::translate('LBL_PRODUCT_PREVIEW', $this->qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/**
	 * Process user request.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		try {
			$productId = $request->isEmpty('productId') ? '' : $request->getByType('productId', \App\Purifier::ALNUM2);
			$product = \App\YetiForce\Shop::getProduct($request->getByType('product', \App\Purifier::ALNUM2), $productId);
			$links = $product->getAdditionalButtons() ?? [];
			$this->successBtn = $product->getStatus() ? '' : 'LBL_BUY';

			$viewer = $this->getViewer($request);
			$viewer->assign('MODULE', $this->qualifiedModuleName);
			$viewer->assign('BTN_LINKS', $links);
			$viewer->assign('PRODUCT', $product);
			$viewer->assign('CURRENCY', $product->getCurrencyCode());
			$viewer->assign('PRICE', $product->getPrice());
			$viewer->assign('IMAGE', $product->getImage());
			$viewer->view('ProductModal.tpl', $this->qualifiedModuleName);
		} catch (\Throwable $e) {
			\App\Log::error($e->__toString());
		}
	}
}
