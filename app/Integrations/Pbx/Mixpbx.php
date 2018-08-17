<?php

namespace App\Integrations\Pbx;

/**
 * Mixpbx PBX integrations class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Mixpbx extends Base
{
	/**
	 * @var string Class name
	 */
	public $name = 'MixPBX';

	/**
	 * Values to configure.
	 *
	 * @var string[]
	 */
	public $configFields = ['url' => ['label' => 'LBL_URL'], 'username' => ['label' => 'LBL_USERNAME'], 'password' => ['label' => 'LBL_PASSWORD']];

	/**
	 * Perform phone call.
	 *
	 * @param \App\Integrations\Pbx $pbx
	 */
	public function performCall(\App\Integrations\Pbx $pbx)
	{
		$url = $pbx->getConfig('url');
		$url .= '?username=' . urlencode($pbx->getConfig('username'));
		$url .= '&password=' . urlencode($pbx->getConfig('password'));
		$url .= '&number=' . urlencode($pbx->get('targetPhone'));
		$url .= '&extension=' . urlencode($pbx->get('sourcePhone'));
		$responsse = \Requests::get($url);
		if (trim($responsse->body) !== 'OK') {
			\App\Log::warning($responsse->body, 'PBX[Mixpbx]');
		}
	}
}
