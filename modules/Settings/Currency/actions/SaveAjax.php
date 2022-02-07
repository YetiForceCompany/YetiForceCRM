<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_Currency_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('setDefault');
		$this->exposeMethod('save');
	}

	/**
	 * Set default currency.
	 *
	 * @param \App\Request $request
	 */
	public function setDefault(App\Request $request)
	{
		$recordModel = Settings_Currency_Record_Model::getInstance($request->getInteger('record'));
		$recordModel->set('defaultid', -11);
		$recordModel->save();
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Save currency.
	 *
	 * @param \App\Request $request
	 *
	 * @throws Exception
	 */
	public function save(App\Request $request)
	{
		if ($request->isEmpty('record')) {
			//get instance from currency name, Aleady deleted and adding again same currency case
			$recordModel = Settings_Currency_Record_Model::getInstance($request->getByType('currency_name', 'Text'));
			if (empty($recordModel)) {
				$recordModel = new Settings_Currency_Record_Model();
			}
		} else {
			$recordModel = Settings_Currency_Record_Model::getInstance($request->getInteger('record'));
		}
		$recordModel->set('currency_name', $request->getByType('currency_name', 'Text'));
		$recordModel->set('currency_status', $request->getByType('currency_status'));
		$recordModel->set('currency_symbol', $request->getByType('currency_symbol', 'Text'));
		$recordModel->set('currency_code', $request->getByType('currency_code'));
		$recordModel->set('conversion_rate', $request->getByType('conversion_rate', 'NumberInUserFormat'));
		//To make sure we are saving record as non deleted. This is useful if we are adding deleted currency
		$recordModel->set('deleted', 0);
		$response = new Vtiger_Response();
		try {
			if ('Inactive' === $request->getByType('currency_status') && !$request->isEmpty('record')) {
				$transforCurrencyToId = $request->getInteger('transform_to_id');
				if (empty($transforCurrencyToId)) {
					throw new \App\Exceptions\AppException('Transfer currency id cannot be empty');
				}
			}
			$id = $recordModel->save();
			$recordModel = Settings_Currency_Record_Model::getInstance($id);
			$response->setResult(array_merge($recordModel->getData(), ['record' => $recordModel->getId()]));
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}
}
