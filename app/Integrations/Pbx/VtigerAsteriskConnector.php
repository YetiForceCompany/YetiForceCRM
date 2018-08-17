<?php

namespace App\Integrations\Pbx;

/**
 * Vtiger Asterisk Connector integrations class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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

		$responsse = \Requests::post($serviceURL);

		\App\Log::warning($responsse->body, 'PBX[VtigerAsteriskConnector]');
	}
}
