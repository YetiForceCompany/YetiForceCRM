<?php

/**
 * MailSmtp SaveAjax action model class
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_MailSmtp_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateSmtp');
	}

	public function updateSmtp(Vtiger_Request $request)
	{
		$result = Settings_MailSmtp_Record_Model::updateSmtp($request);

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
