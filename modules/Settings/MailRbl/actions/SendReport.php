<?php

/**
 * Settings MailRbl send report action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Settings MailRbl send report action class.
 */
class Settings_MailRbl_SendReport_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$status = \App\Mail\Rbl::sendReport([
			'id' => $request->getInteger('id'),
			'type' => $request->getByType('type'),
			'desc' => $request->getByType('desc', 'Text'),
			'category' => $request->getByType('category', 'Text'),
		]);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $status,
			'notify' => ['title' => App\Language::translate($status ? 'LBL_SENT' : 'ERR_OCCURRED_CHECK_LOGS')],
		]);
		$response->emit();
	}
}
