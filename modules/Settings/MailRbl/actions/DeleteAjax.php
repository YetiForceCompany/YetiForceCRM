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
		$dbCommand = \App\Db::getInstance('admin')->createCommand();
		if ('request' === $request->getMode()) {
			$status = $dbCommand->delete('s_#__mail_rbl_request', [
				'id' => $request->getInteger('record')
			])->execute();
		} else {
			$row = (new \App\Db\Query())->from('s_#__mail_rbl_list')->where(['id' => $request->getInteger('record')])->one(\App\Db::getInstance('admin'));
			$status = $dbCommand->delete('s_#__mail_rbl_list', [
				'id' => $request->getInteger('record')
			])->execute();
			$dbCommand->update('s_#__mail_rbl_request', [
				'status' => 3,
			], ['id' => $row['request']])->execute();
		}
		$response = new Vtiger_Response();
		$response->setResult($status);
		$response->emit();
	}
}
