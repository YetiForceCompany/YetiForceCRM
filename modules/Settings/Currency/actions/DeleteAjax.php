<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Currency_DeleteAjax_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$response = new Vtiger_Response();
		$transforCurrencyToId = $request->getInteger('transform_to_id');
		if (empty($transforCurrencyToId)) {
			throw new \App\Exceptions\AppException('Transfer currency id cannot be empty');
		}
		Settings_Currency_Module_Model::delete($request->getInteger('record'));
		$response->setResult(['success' => 'true']);
		$response->emit();
	}
}
