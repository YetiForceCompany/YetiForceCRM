<?php

/**
 * Mail Mass send email action model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_MassSend_Action extends Vtiger_Mass_Action
{
	use \App\Controller\Traits\SettingsPermission;

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$recordIds = $this->getRecordsListFromRequest($request);
		$db = \App\Db::getInstance('admin');
		$dataReader = (new \App\Db\Query())->from('s_#__mail_queue')
			->where(['id' => $recordIds])
			->createCommand($db)->query();
		while ($rowQueue = $dataReader->read()) {
			\App\Mailer::sendByRowQueue($rowQueue);
		}
		$dataReader->close();
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}
