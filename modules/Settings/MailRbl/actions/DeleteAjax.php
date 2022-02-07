<?php

/**
 * Settings MailRbl delete action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license		YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings MailRbl delete action class.
 */
class Settings_MailRbl_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$db = \App\Db::getInstance('admin');
		$dbCommand = $db->createCommand();
		if (\in_array($request->getMode(), ['forVerification', 'toSend', 'request'])) {
			$status = $dbCommand->delete('s_#__mail_rbl_request', [
				'id' => $request->getInteger('record')
			])->execute();
			$row = (new \App\Db\Query())->from('s_#__mail_rbl_list')->where(['request' => $request->getInteger('record')])->one($db);
			if ($row) {
				$dbCommand->delete('s_#__mail_rbl_list', [
					'request' => $request->getInteger('record')
				])->execute();
				\App\Cache::delete('MailRblIpColor', $row['ip']);
				\App\Cache::delete('MailRblList', $row['ip']);
			}
		} else {
			$row = (new \App\Db\Query())->from('s_#__mail_rbl_list')->where(['id' => $request->getInteger('record')])->one($db);
			$status = $dbCommand->delete('s_#__mail_rbl_list', [
				'id' => $request->getInteger('record')
			])->execute();
			$dbCommand->update('s_#__mail_rbl_request', [
				'status' => 3,
			], ['id' => $row['request']])->execute();
			\App\Cache::delete('MailRblIpColor', $row['ip']);
			\App\Cache::delete('MailRblList', $row['ip']);
		}
		$response = new Vtiger_Response();
		$response->setResult($status);
		$response->emit();
	}
}
