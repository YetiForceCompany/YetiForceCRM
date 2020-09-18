<?php

/**
 * Settings MailRbl delete action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license		YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings MailRbl delete action class.
 */
class Settings_MailRbl_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$createCommand = \App\Db::getInstance('admin')->createCommand();
		if ('request' === $request->getMode()) {
			$status = $createCommand->delete('s_#__mail_rbl_request', [
				'id' => $request->getInteger('record')
			])->execute();
		} else {
			$status = $createCommand->delete('s_#__mail_rbl_list', [
				'id' => $request->getInteger('record')
			])->execute();
		}
		$response = new Vtiger_Response();
		$response->setResult($status);
		$response->emit();
	}
}
