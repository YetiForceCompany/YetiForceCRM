<?php
/**
 * XL file for the connector enabling connection to the Comarch XL interface via the ELTE-S API.
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

namespace App\Integrations\Comarch\Connector;

use App\Exceptions\AppException;

/**
 * XL class for the connector enabling connection to the Comarch XL interface via the ELTE-S API.
 */
class Xl extends Base
{
	/** {@inheritdoc} */
	const NAME = 'Comarch XL';
	/** {@inheritdoc} */
	const LOG_NAME = 'App\Integrations\ComarchXL';
	/** @var string Special token to authorization. */
	private $token;

	/** {@inheritdoc} */
	public function authorize(): void
	{
		$url = rtrim($this->config->get('url'), '/') . '/user/authenticate';
		\App\Log::beginProfile("POST|XL::authorize|{$url}", self::LOG_NAME);
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))
			->post($url, [
				'timeout' => 0,
				'http_errors' => false,
				'verify' => $this->config->get('verify_ssl') ? true : false,
				'json' => [
					'UserName' => $this->config->get('user_name'),
					'Password' => $this->config->get('password')],
			]);
		\App\Log::endProfile("POST|XL::authorize|{$url}", self::LOG_NAME);
		if (200 !== $response->getStatusCode()) {
			throw new AppException($response->getReasonPhrase(), $response->getStatusCode());
		}
		$body = \App\Json::decode((string) $response->getBody());
		if (empty($body['token'])) {
			throw new AppException('Invalid server response');
		}
		$this->token = $body['token'];
	}

	/** {@inheritdoc} */
	public function isAuthorized(): bool
	{
		return !empty($this->token);
	}

	/** {@inheritdoc} */
	public function request(string $method, string $action, array $params = []): string
	{
		$url = rtrim($this->config->get('url'), '/') . "/$action";
		\App\Log::beginProfile("{$method}::request|{$url}", self::LOG_NAME);
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))
			->request($method, $url, [
				'headers' => [
					'authorization' => 'Bearer ' . $this->token,
				],
				'timeout' => 0,
				'verify' => $this->config->get('verify_ssl') ? true : false,
				'json' => $params
			]);
		\App\Log::endProfile("{$method}::request|{$url}", self::LOG_NAME);
		return (string) $response->getBody();
	}
}
