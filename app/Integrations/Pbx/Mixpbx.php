<?php
/**
 * Mixpbx PBX integrations file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Pbx;

/**
 * Mixpbx PBX integrations class.
 */
class Mixpbx extends Base
{
	/** {@inheritdoc} */
	public $name = 'MixPBX';

	/** {@inheritdoc} */
	public $configFields = ['url' => ['label' => 'LBL_URL'], 'username' => ['label' => 'LBL_USERNAME'], 'password' => ['label' => 'LBL_PASSWORD']];

	/** {@inheritdoc} */
	public function performCall(\App\Integrations\Pbx $pbx): array
	{
		$status = true;
		$url = $pbx->getConfig('url');
		$url .= '?username=' . urlencode($pbx->getConfig('username'));
		$url .= '&password=' . urlencode($pbx->getConfig('password'));
		$url .= '&number=' . urlencode($pbx->get('targetPhone'));
		$url .= '&extension=' . urlencode($pbx->get('sourcePhone'));
		try {
			\App\Log::beginProfile("GET|Mixpbx::performCall|{$url}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('GET', $url);
			\App\Log::endProfile("GET|Mixpbx::performCall|{$url}", __NAMESPACE__);
			if (200 !== $response->getStatusCode()) {
				\App\Log::warning('Error: ' . $url . ' | ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase(), __CLASS__);
				$status = false;
			}
			$contents = $response->getBody()->getContents();
			if ('OK' !== trim($contents)) {
				\App\Log::warning($contents, 'PBX[Mixpbx]');
			}
		} catch (\Throwable $exc) {
			\App\Log::warning('Error: ' . $url . ' | ' . $exc->getMessage(), __CLASS__);
			$status = false;
		}
		return ['status' => $status];
	}
}
