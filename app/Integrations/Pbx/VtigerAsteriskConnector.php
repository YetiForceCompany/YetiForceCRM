<?php

namespace App\Integrations\Pbx;

/**
 * Vtiger Asterisk Connector integrations class.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class VtigerAsteriskConnector extends Base
{
	/**
	 * @var string Class name
	 */
	public $name = 'VtigerAsteriskConnector';

	/**
	 * Values to configure.
	 *
	 * @var string[]
	 */
	public $configFields = ['url' => ['label' => 'LBL_URL'], 'secretkey' => ['label' => 'LBL_SECRET_KEY'], 'outboundContext' => ['label' => 'LBL_OUTBOUND_CONTEXT']];

	/**
	 * Perform phone call.
	 *
	 * @param \App\Integrations\Pbx $pbx
	 */
	public function performCall(\App\Integrations\Pbx $pbx)
	{
		$serviceURL = $pbx->getConfig('url');
		$serviceURL .= '/makecall?event=OutgoingCall&';
		$serviceURL .= 'secret=' . urlencode($pbx->getConfig('secretkey')) . '&';
		$serviceURL .= 'from=' . urlencode($pbx->get('sourcePhone')) . '&';
		$serviceURL .= 'to=' . urlencode($pbx->get('targetPhone')) . '&';
		$serviceURL .= 'context=' . urlencode($pbx->get('outboundContext'));

		try {
			\App\Log::beginProfile("POST|VtigerAsteriskConnector::performCall|{$serviceURL}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('POST', $serviceURL);
			\App\Log::endProfile("POST|VtigerAsteriskConnector::performCall|{$serviceURL}", __NAMESPACE__);
			if (200 !== $response->getStatusCode()) {
				\App\Log::warning('Error: ' . $serviceURL . ' | ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase(), __CLASS__);
				return false;
			}
		} catch (\Throwable $exc) {
			\App\Log::warning('Error: ' . $serviceURL . ' | ' . $exc->getMessage(), __CLASS__);
			return false;
		}
	}
}
