<?php

/**
 * Sen mail manually action model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_SendManuallyAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$record = $request->getInteger('id');
		$db = \App\Db::getInstance('admin');
		$row = (new \App\Db\Query())->from('s_#__mail_queue')
			->where(['id' => $record])->one($db);
		\App\Mailer::sendByRowQueue($row);
		$response = new Vtiger_Response();
		$response->setResult(['success' => true, 'message' => \App\Language::translate('LBL_SEND_EMAIL_MANUALLY', $request->getModule(false))]);
		$response->emit();
	}
}
