<?php
/**
 * Genesys WDE by Whirly mail composer driver file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail\Composers;

/**
 * Genesys WDE by Whirly mail composer driver class.
 */
class GenesysWdeWhirly extends Base
{
	/** {@inheritdoc} */
	const NAME = 'LBL_GENESYS_WDE';

	/** {@inheritdoc} */
	public function isActive(): bool
	{
		return true;
	}

	/** {@inheritdoc} */
	public function sendMail(\App\Request $request): array
	{
		$params = [];
		foreach (\App\Integrations\Pbx::getAll() as $row) {
			if ('GenesysWdeWhirly' === $row['type']) {
				$params = \App\Json::decode($row['param']);
			}
		}
		$url = "http://localhost:{$params['httpListener']}/CLICKTOMAIL?customeremail={$request->get('email')}";
		if ($request->isEmpty('record')) {
			$url .= '&crmsourceid=' . $request->getInteger('record');
		}
		if ($params && ($serviceValue = ($params['serviceValueEmail'] ?? ''))) {
			$url .= '&servicevalue=' . $serviceValue;
		}
		return [
			'status' => true,
			'url' => $url
		];
	}
}
