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
	const NAME = 'BRIA Softphone';

	/** {@inheritdoc} */
	public function performCall(string $targetPhone, int $record): array
	{
		// No GUI mode
		return [];
	}

	/** {@inheritdoc} */
	public function saveCalls(\App\Request $request): array
	{
		$accountName = $request->getByType('accountName', 'Text');
		$loadMore = true;
		foreach ($request->getMultiDimensionArray('calls', [
			'type' => 'Alnum',
			'number' => 'Text',
			'displayName' => 'Text',
			'duration' => 'Integer',
			'timeInitiated' => 'Integer',
			'id' => 'Alnum',
			'accountId' => 'Integer',
		]) as $call) {
			if ((new \App\Db\Query())->from('vtiger_callhistory')->where(['subscriberId' => $call['accountId'], 'phonecallid' => $call['id']])->exists()) {
				$loadMore = false;
				break;
			}
			$this->addCall($call, $accountName);
		}
		return [
			'loadMore' => $loadMore
		];
	}

	/** {@inheritdoc} */
	public function saveSettings(array $data): void
	{
		if (!\in_array('wss://cpclientapi.softphone.com:9002', \Config\Security::$allowedConnectDomains)) {
			$security = new \App\ConfigFile('security');
			$security->set('allowedConnectDomains', array_values(array_merge(\Config\Security::$allowedConnectDomains, [
				'wss://cpclientapi.softphone.com:9002'
			])));
			$security->create();
		}
	}

	/**
	 * Add call history.
	 *
	 * @param array  $call
	 * @param string $accountName
	 *
	 * @return void
	 */
	private function addCall(array $call, string $accountName): void
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('CallHistory');
		$phoneNumber = $call['number'];
		try {
			\App\Fields\Phone::verifyNumber($phoneNumber);
		} catch (\Throwable $th) {
			$phoneNumber = '+' . $phoneNumber;
		}
		foreach (\App\Fields\Phone::parsePhone('to_number', ['to_number' => $phoneNumber]) as $key => $value) {
			if ('to_number' !== $key) {
				$value = ltrim($value, '+');
			}
			$recordModel->set($key, $value);
			if ($id = $this->pbx->findNumber($value)) {
				$recordModel->set('destination', $id);
			}
		}
		foreach (\App\Fields\Phone::parsePhone('from_number', ['from_number' => $accountName]) as $key => $value) {
			$recordModel->set($key, $value);
		}
		$recordModel->set('location', 'BriaSoftphone');
		$recordModel->set('duration', $call['duration']);
		$recordModel->set('subscriberId', $call['accountId']);
		$recordModel->set('phonecallid', $call['id']);
		if ('dialed' === $call['type'] && 0 == $call['duration']) {
			$call['type'] = 'dialedMissed';
		}
		$recordModel->set('callhistorytype', self::STATUS_MAP[$call['type']]);
		$recordModel->set('start_time', date('Y-m-d H:i:s', $call['timeInitiated']));
		if ($call['duration']) {
			$recordModel->set('end_time', date('Y-m-d H:i:s', $call['timeInitiated'] + $call['duration']));
		}
		$recordModel->save();
	}
}
