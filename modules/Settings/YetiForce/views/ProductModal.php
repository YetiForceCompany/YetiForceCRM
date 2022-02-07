<?php

/**
 * YetiForce product Modal.
 *
 * @package   Settings
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
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
		$installation = $request->getBoolean('installation');
		$department = $request->isEmpty('department') ? '' : $request->getByType('department');
		$product = \App\YetiForce\Shop::getProduct($request->getByType('product'), $department);
		$alert = $product->showAlert();
		$links = $product->getAdditionalButtons() ?? [];
		if (isset($product->expirationDate)) {
			if ($alert['status']) {
				$links[] = Vtiger_Link_Model::getInstanceFromValues([
					'linklabel' => \App\Language::translate($alert['type'], 'Settings:_Base'),
					'linkicon' => 'fas fa-exclamation-triangle',
					'linkhref' => true,
					'linkurl' => $alert['href'] ?? '',
					'linkclass' => 'btn-warning',
					'showLabel' => 1,
				]);
			}
			$this->successBtn = '';
		} else {
			$this->successBtn = 'LBL_BUY';
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $this->qualifiedModuleName);
		$viewer->assign('BTN_LINKS', $links);
		$viewer->assign('PRODUCT', $product);
		$viewer->assign('CURRENCY', $product->isCustom() ? $product->currencyCode : 'EUR');
		$viewer->assign('PRICE', $installation ? false : $product->getPrice());
		$viewer->assign('IMAGE', ($installation ? '../' : '') . $product->getImage());
		$viewer->view('ProductModal.tpl', $this->qualifiedModuleName);
	}
}
