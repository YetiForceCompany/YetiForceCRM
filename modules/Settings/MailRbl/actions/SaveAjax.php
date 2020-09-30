<?php

/**
 * Settings MailRbl save action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings MailRbl save action class.
 */
class Settings_MailRbl_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$requestMode = 'request' === $request->getMode();
		\App\Db::getInstance('admin')->createCommand()
			->update($requestMode ? 's_#__mail_rbl_request' : 's_#__mail_rbl_list', [
				'status' => $request->getInteger('status')
			], ['id' => $request->getInteger('record')])->execute();
		if ($requestMode) {
			$this->update($request);
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => App\Language::translate('LBL_CHANGES_SAVED'),
		]);
		$response->emit();
	}

	/**
	 * Update.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	private function update(App\Request $request): void
	{
		$dbCommand = \App\Db::getInstance('admin')->createCommand();
		if (1 === $request->getInteger('status')) {
			$recordModel = \App\Mail\Rbl::getRequestById($request->getInteger('record'));
			$recordModel->parse();
			$sender = $recordModel->getSender();
			if (!empty($sender['ip'])) {
				$id = false;
				if ($ipsList = \App\Mail\Rbl::findIp($sender['ip'])) {
					foreach ($ipsList as $ipList) {
						if (2 !== (int) $ipList['type']) {
							$id = $ipList['id'];
							break;
						}
					}
				}
				if ($id) {
					$dbCommand->update('s_#__mail_rbl_list', [
						'status' => 0,
						'type' => $recordModel->get('type'),
						'request' => $request->getInteger('record'),
					], ['id' => $id])->execute();
					$dbCommand->update('s_#__mail_rbl_request', [
						'status' => 3,
					], ['id' => $ipList['request']])->execute();
				} else {
					$dbCommand->insert('s_#__mail_rbl_list', [
						'ip' => $sender['ip'],
						'status' => 0,
						'type' => $recordModel->get('type'),
						'request' => $request->getInteger('record'),
						'source' => '',
					])->execute();
				}
			}
		} else {
			$dbCommand->delete('s_#__mail_rbl_list', ['request' => $request->getInteger('record')])->execute();
		}
	}
}
