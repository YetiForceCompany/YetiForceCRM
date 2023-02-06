<?php
/**
 * HTTP authentication file which must have each connector.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription.
 * File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\WooCommerce\Connector;

/**
 * HTTP authentication class which must have each connector.
 */
class HttpAuth extends Base
{
	/** {@inheritdoc} */
	public function request(string $method, string $action, array $params = []): string
	{
		$url = rtrim($this->config->get('url'), '/') . "/wp-json/wc/v3/$action";
		\App\Log::beginProfile("{$method}|HttpAuth::request|{$url}", 'App\Integrations\WooCommerce');
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request($method, $url, [
			'auth' => [$this->config->get('user_name'), $this->config->get('password')],
			'timeout' => 0,
			'verify' => $this->config->get('verify_ssl') ? true : false,
			'json' => $params]);
		\App\Log::endProfile("{$method}|HttpAuth::request|{$url}", 'App\Integrations\WooCommerce');
		return (string) $response->getBody();
	}
}
