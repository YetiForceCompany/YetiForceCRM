<?php

/**
 * Save issue to github.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Github_SaveIssuesAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$title = $request->getByType('title', 'Text');
		$body = $request->getForHtml('body');
		$clientModel = Settings_Github_Client_Model::getInstance();
		$success = $clientModel->createIssue($body, $title);
		$success = $success ? true : false;
		$responce = new Vtiger_Response();
		$responce->setResult(['success' => $success]);
		$responce->emit();
	}
}
