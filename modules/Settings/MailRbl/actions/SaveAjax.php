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
		$dbCommand = \App\Db::getInstance('admin')->createCommand();
		$dbCommand->update($requestMode ? 's_#__mail_rbl_request' : 's_#__mail_rbl_list', [
			'status' => $request->getInteger('status')
		], ['id' => $request->getInteger('record')]
		)->execute();
		if ($requestMode && 1 === $request->getInteger('status')) {
			$recordModel = Settings_MailRbl_Record_Model::getRequestById($request->getInteger('record'));
			$sender = $recordModel->getSender();
			if (!empty($sender['ip'])) {
				$dbCommand->insert('s_#__mail_rbl_list', [
					'ip' => $sender['ip'],
					'status' => 0,
					'type' => $recordModel->get('type'),
					'from' => $sender['from'],
					'by' => $sender['by'],
					'source' => '',
				])->execute();
			}
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'message' => App\Language::translate('LBL_CHANGES_SAVED'),
		]);
		$response->emit();
	}
}
