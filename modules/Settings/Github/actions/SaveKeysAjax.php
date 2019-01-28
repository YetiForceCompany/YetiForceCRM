<?php

/**
 * Save keys.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Github_SaveKeysAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$clientModel = Settings_Github_Client_Model::getInstance();
		$clientModel->setToken($request->getByType('token', 'Alnum'));
		$clientModel->setUsername($request->getByType('username', 'Text'));
		if ($clientModel->checkToken()) {
			$success = $clientModel->saveKeys() ? true : false;
		} else {
			$success = false;
		}
		$responce = new Vtiger_Response();
		$responce->setResult(['success' => $success]);
		$responce->emit();
	}
}
