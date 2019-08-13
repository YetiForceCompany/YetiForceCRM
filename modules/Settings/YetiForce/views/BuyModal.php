<?php

/**
 * YetiForce product Modal.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Offline registration modal view class.
 */
class Settings_YetiForce_BuyModal_View extends \App\Controller\ModalSettings
{
	/**
	 * {@inheritdoc}
	 */
	public $successBtn = 'LBL_SHOP_PROCEED_TO_CHECKOUT';
	/**
	 * {@inheritdoc}
	 */
	public $successBtnIcon = 'far fa-credit-card';
	/**
	 * {@inheritdoc}
	 */
	public $footerClass = 'px-md-5';
	/**
	 * Header class.
	 *
	 * @var string
	 */
	public $headerClass = 'modal-header-xl';

	/**
	 * Only administrator user can access settings modal.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 *
	 * @return bool
	 */
	public function checkPermission(App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin() && $request->isEmpty('installation')) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$this->modalIcon = 'yfi-marketplace';
		$this->pageTitle = \App\Language::translate('LBL_BUY', $qualifiedModuleName);
		parent::preProcessAjax($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$department = $request->isEmpty('department') ? '' : $request->getByType('department');
		$product = \App\YetiForce\Shop::getProduct($request->getByType('product'), $department);
		$companies = [];
		$currency = 'EUR';
		$installMode = !$request->isEmpty('installation');
		if (!$installMode) {
			foreach (\App\Company::getAll() as $key => $row) {
				if (1 === (int) $row['type']) {
					$companies = $row;
				}
			}
			$viewer->assign('VARIABLE_PAYMENTS', \App\YetiForce\Shop::getVariablePayments());
			$viewer->assign('VARIABLE_PRODUCT', $product->getVariable());
			$currency = $product->currencyCode;
		}
		$recordModel = $formFields = [];
		$formFields = array_filter(Settings_Companies_Module_Model::getFormFields(), function ($key) {
			return isset($key['paymentData']);
		});
		if ($companies) {
			$recordModel = Settings_Companies_Record_Model::getInstance($companies['id'])->set('source', $qualifiedModuleName);
		} elseif (!$installMode) {
			$this->successBtn = '';
		}
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('PRODUCT', $product);
		$viewer->assign('PAYPAL_URL', \App\YetiForce\Shop::getPaypalUrl());
		$viewer->assign('COMPANY_DATA', $companies);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('FORM_FIELDS', $formFields);
		$viewer->assign('CURRENCY', $currency);
		$viewer->assign('INSTALL_MODE', $installMode);
		$viewer->view('BuyModal.tpl', $qualifiedModuleName);
	}
}
