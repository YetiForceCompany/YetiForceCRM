<?php
/**
 * Genesys WDE by Whirly PBX integrations file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Pbx;

/**
 * Genesys WDE by Whirly PBX integrations class.
 */
class GenesysWdeWhirly extends Base
{
	/** {@inheritdoc} */
	const NAME = 'Genesys WDE by Whirly';

	/** {@inheritdoc} */
	const CONFIG_FIELDS = [
		'httpListener' => ['label' => 'LBL_HTTP_LISTENER_PORT', 'uitype' => 7, 'typeofdata' => 'I~M', 'defaultvalue' => 6999],
		'httpToken' => ['label' => 'LBL_HTTP_LISTENER_TOKEN', 'uitype' => 1, 'typeofdata' => 'V~O'],
		'serviceValuePhone' => ['label' => 'FL_PHONE', 'uitype' => 1, 'typeofdata' => 'V~O'],
		'serviceValueEmail' => ['label' => 'FL_EMAIL', 'uitype' => 13, 'typeofdata' => 'V~O'],
	];

	/** {@inheritdoc} */
	public function performCall(string $targetPhone, int $record): array
	{
		$url = "http://localhost:{$this->pbx->getConfig('httpListener')}/CLICKTODIAL?dialednumber={$targetPhone}&crmsourceid={$record}";
		if ($serviceValuePhone = $this->pbx->getConfig('serviceValuePhone')) {
			$url .= '&servicevalue=' . $serviceValuePhone;
		}
		return [
			'status' => true,
			'url' => $url,
			'token' => $this->pbx->getConfig('httpToken'),
		];
	}

	/** {@inheritdoc} */
	public function saveSettings(array $data): void
	{
		$host = 'http://localhost:' . $data['httpListener'];
		if (!\in_array($host, \Config\Security::$allowedConnectDomains)) {
			$security = new \App\ConfigFile('security');
			$security->set('allowedConnectDomains', array_values(array_merge(\Config\Security::$allowedConnectDomains, [
				$host
			])));
			$security->create();
		}
	}
}
