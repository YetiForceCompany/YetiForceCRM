<?php
/**
 * Vtiger Asterisk Connector integrations file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Pbx;

/**
 * Vtiger Asterisk Connector integrations class.
 */
class VtigerAsteriskConnector extends Base
{
	/** {@inheritdoc} */
	const NAME = 'Vtiger Asterisk Connector';

	/** {@inheritdoc} */
	const CONFIG_FIELDS = [
		'url' => ['label' => 'LBL_URL'],
		'secretkey' => ['label' => 'LBL_SECRET_KEY'],
		'outboundContext' => ['label' => 'LBL_OUTBOUND_CONTEXT']
	];

	/** {@inheritdoc} */
	public function performCall(string $targetPhone, int $record): array
	{
		if ($this->pbx->isEmpty('sourcePhone')) {
			throw new \App\Exceptions\AppException('No user phone number');
		}
		$status = true;
		$serviceURL = $this->pbx->getConfig('url');
		$serviceURL .= '/makecall?event=OutgoingCall&';
		$serviceURL .= 'secret=' . urlencode($this->pbx->getConfig('secretkey')) . '&';
		$serviceURL .= 'from=' . urlencode($this->pbx->get('sourcePhone')) . '&';
		$serviceURL .= 'to=' . urlencode($targetPhone) . '&';
		$serviceURL .= 'context=' . urlencode($this->pbx->get('outboundContext'));
		try {
			\App\Log::beginProfile("POST|VtigerAsteriskConnector::performCall|{$serviceURL}", __NAMESPACE__);
			$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request('POST', $serviceURL);
			\App\Log::endProfile("POST|VtigerAsteriskConnector::performCall|{$serviceURL}", __NAMESPACE__);
			if (200 !== $response->getStatusCode()) {
				\App\Log::warning('Error: ' . $serviceURL . ' | ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase(), __CLASS__);
				$status = false;
			}
		} catch (\Throwable $exc) {
			\App\Log::warning('Error: ' . $serviceURL . ' | ' . $exc->getMessage(), __CLASS__);
			$status = false;
		}
		return ['status' => $status];
	}

	/** {@inheritdoc} */
	public function isActive(): bool
	{
		return !empty(\App\User::getCurrentUserModel()->getDetail('phone_crm_extension_extra'));
	}
}
