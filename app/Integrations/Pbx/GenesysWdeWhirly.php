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
	public $name = 'Genesys WDE by Whirly';

	/** {@inheritdoc} */
	public $configFields = [
		'httpListener' => ['label' => 'LBL_HTTP_LISTENER_PORT', 'uitype' => 7, 'typeofdata' => 'I~M', 'defaultvalue' => 6999],
		'serviceValue' => ['label' => 'LBL_OUTBOUND_CONTEXT', 'uitype' => 1, 'typeofdata' => 'V~O'],
	];

	/** {@inheritdoc} */
	public function performCall(): array
	{
		$url = "http://localhost:{$this->pbx->getConfig('httpListener')}/CLICKTODIAL?dialednumber={$this->pbx->get('targetPhone')}&crmsourceid={$this->pbx->get('record')}";
		if ($serviceValue = $this->pbx->getConfig('serviceValue')) {
			$url .= '&servicevalue=' . $serviceValue;
		}
		return [
			'status' => true,
			'url' => $url
		];
	}

	/** {@inheritdoc} */
	public function saveSettings(array $data): void
	{
		$host = 'localhost:' . $data['httpListener'];
		if (!\in_array($host, \Config\Security::$allowedConnectDomains)) {
			$security = new \App\ConfigFile('security');
			$security->set('allowedConnectDomains', array_values(array_merge((\Config\Security::$allowedConnectDomains), [
				$host
			])));
			$security->create();
		}
	}
}
