<?php

/**
 * Settings MailRbl send report action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$data = [
			'id' => $request->getInteger('id'),
		];
		if ($request->has('category')) {
			$data['desc'] = $request->getByType('desc', 'Text');
			$data['category'] = $request->getByType('category', 'Text');
		}
		$status = \App\Mail\Rbl::sendReport($data);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $status['status'],
			'notify' => [
				'type' => ($status['status'] ? 'success' : 'error'),
				'title' => App\Language::translate($status['status'] ? 'LBL_SENT' : $status['message'])
			],
		]);
		$response->emit();
	}
}
