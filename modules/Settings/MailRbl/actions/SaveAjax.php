<?php

/**
 * Settings MailRbl save action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings MailRbl save action class.
 */
class Settings_MailRbl_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	use \App\Controller\ExposeMethod;
	use \App\Controller\Traits\SettingsPermission;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('update');
		$this->exposeMethod('config');
	}

	/**
	 * Rbl record instance.
	 *
	 * @var \App\Mail\Rbl
	 */
	protected $rblRecord;

	/**
	 * Status update.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function update(App\Request $request): void
	{
		$db = \App\Db::getInstance('admin');
		$requestMode = \in_array($request->getByType('type'), ['forVerification', 'toSend', 'request']);
		$response = new Vtiger_Response();
		if ('forVerification' === $request->getByType('type') && 1 === $request->getInteger('status') && ($error = $this->checkExistingRbl($request))) {
			$response->setResult([
				'success' => true,
				'notify' => [
					'type' => 'error',
					'title' => $error,
				],
			]);
		} else {
			$db->createCommand()
				->update($requestMode ? 's_#__mail_rbl_request' : 's_#__mail_rbl_list', [
					'status' => $request->getInteger('status')
				], ['id' => $request->getInteger('record')])->execute();
			if ($requestMode) {
				$this->updateList($request);
			} else {
				$ips = (new \App\Db\Query())->select(['ip'])->from('s_#__mail_rbl_list')->where(['id' => $request->getInteger('record')])->column($db);
				foreach ($ips as $ip) {
					\App\Cache::delete('MailRblIpColor', $ip);
					\App\Cache::delete('MailRblList', $ip);
				}
			}
			$response->setResult([
				'success' => true,
				'notify' => [
					'type' => 'success',
					'title' => App\Language::translate('LBL_CHANGES_SAVED'),
				],
			]);
		}
		$response->emit();
	}

	/**
	 * Update.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	private function updateList(App\Request $request): void
	{
		if (1 === $request->getInteger('status')) {
			$rblRecord = \App\Mail\Rbl::getRequestById($request->getInteger('record'));
			$rblRecord->parse();
			$rblRecord->updateList($request->getInteger('record'));
		} else {
			\App\Db::getInstance('admin')
				->createCommand()
				->delete('s_#__mail_rbl_list', ['request' => $request->getInteger('record')])
				->execute();
		}
	}

	/**
	 * Check existing IP in RBL list.
	 *
	 * @param App\Request $request
	 *
	 * @return string
	 */
	private function checkExistingRbl(App\Request $request): string
	{
		$rblRecord = \App\Mail\Rbl::getRequestById($request->getInteger('record'));
		$rblRecord->parse();
		$sender = $rblRecord->getSender();
		if (!empty($sender['ip'])) {
			if ($ipsList = \App\Mail\Rbl::findIp($sender['ip'])) {
				$type = (int) $rblRecord->get('type');
				foreach ($ipsList as $ipList) {
					if ($type !== (int) $ipList['type']) {
						$type = $ipList['type'] ? 'LBL_WHITE_LIST' : 'LBL_BLACK_LIST';
						$tab = $ipList['type'] ? 'whiteList' : 'blackList';
						$href = "<a href=\"http://yeti/index.php?parent=Settings&module=MailRbl&view=Index&tab={$tab}&ip={$sender['ip']}\" target=\"_blank\">{$sender['ip']}</a>";
						return App\Language::translateArgs('LBL_IP_EXISTING_IN_RBL', $request->getModule(false), $href, App\Language::translate($type, $request->getModule(false)));
					}
				}
			}
		}
		return '';
	}

	/**
	 * Config update.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	public function config(App\Request $request): void
	{
		$name = 'accept_mode' === $request->getByType('name') ? 'rcListAcceptAutomatically' : 'rcListSendReportAutomatically';
		$configFile = new \App\ConfigFile('component', 'Mail');
		$configFile->set($name, $request->getBoolean('value'));
		$configFile->create();

		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'notify' => [
				'type' => 'success',
				'title' => App\Language::translate('LBL_CHANGES_SAVED'),
			],
		]);
		$response->emit();
	}
}
