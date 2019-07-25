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
	public $successBtn = 'LBL_BUY';
	/**
	 * {@inheritdoc}
	 */
	public $successBtnIcon = 'fab fa-paypal';

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$this->modalIcon = 'fas fa-shopping-cart';
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
		foreach (\App\Company::getAll() as $key => $row) {
			if (1 === (int) $row['type']) {
				$companies = $row;
			}
		}
		$recordModel = [];
		if ($companies) {
			$recordModel = Settings_Companies_Record_Model::getInstance($companies['id'])->set('source', $qualifiedModuleName);
		}
		$viewer->assign('MODULE', $qualifiedModuleName);
		$viewer->assign('PRODUCT', $product);
		$viewer->assign('VARIABLE_PAYMENTS', \App\YetiForce\Shop::getVariablePayments());
		$viewer->assign('VARIABLE_PRODUCT', $product->getVariable());
		$viewer->assign('PAYPAL_URL', \App\YetiForce\Shop::getPaypalUrl());
		$viewer->assign('COMPANY_DATA', $companies);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('FORM_FIELDS', Settings_Companies_Module_Model::getFormFields());
		$viewer->view('BuyModal.tpl', $qualifiedModuleName);
	}
}
