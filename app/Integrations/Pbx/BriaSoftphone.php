<?php
/**
 * BRIA Softphone PBX integrations file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Pbx;

/**
 * BRIA Softphone PBX integrations class.
 */
class BriaSoftphone extends Base
{
	/** @var string[] Status map */
	const STATUS_MAP = [
		'missed' => 'Missed',
		'received' => 'Incoming',
		'dialed' => 'Outgoing received',
		'dialedMissed' => 'Outgoing missed',
	];

	/** {@inheritdoc} */
	public $name = 'BRIA Softphone';

	/** {@inheritdoc} */
	public function performCall(\App\Integrations\Pbx $pbx): array
	{
		// No GUI mode
		return [];
	}

	/** {@inheritdoc} */
	public function saveCalls(\App\Integrations\Pbx $pbx, \App\Request $request): array
	{
		foreach ($request->getMultiDimensionArray('calls', [
			'type' => 'Alnum',
			'number' => 'Text',
			'displayName' => 'Text',
			'duration' => 'Integer',
			'timeInitiated' => 'Integer',
			'id' => 'Alnum',
			'accountId' => 'Integer',
		]) as $call) {
			if (!(new \App\Db\Query())->from('vtiger_callhistory')->where(['from_number_extra' => $call['accountId'], 'phonecallid' => $call['id']])->exists()) {
				$this->addCall($call);
			}
		}
		return [];
	}

	/**
	 * Add call history.
	 *
	 * @param array $call
	 *
	 * @return void
	 */
	private function addCall(array $call): void
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('CallHistory');
		$recordModel->set('to_number', $call['number']);
		$recordModel->set('duration', $call['duration']);
		$recordModel->set('from_number_extra', $call['accountId']);
		$recordModel->set('phonecallid', $call['id']);
		if ('dialed' === $call['type'] && 0 == $call['duration']) {
			$call['type'] = 'dialedMissed';
		}
		$recordModel->set('callhistorytype', self::STATUS_MAP[$call['type']]);
		$recordModel->set('start_time', date('Y-m-d H:i:s', $call['timeInitiated']));
		if ($call['duration']) {
			$recordModel->set('end_time', date('Y-m-d H:i:s', ($call['timeInitiated'] + $call['duration'])));
		}
		$recordModel->save();
	}

	/** {@inheritdoc} */
	public function saveSettings(array $data): void
	{
		if (!\in_array('wss://cpclientapi.softphone.com:9002', \Config\Security::$allowedConnectDomains)) {
			$security = new \App\ConfigFile('security');
			$security->set('allowedConnectDomains', array_values(array_merge((\Config\Security::$allowedConnectDomains), [
				'wss://cpclientapi.softphone.com:9002'
			])));
			$security->create();
		}
	}
}
