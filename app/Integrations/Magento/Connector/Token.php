<?php
/**
 * Connector based on session.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Connector;

use App\Exceptions\AppException;

/**
 * Token class.
 */
class Token extends Base
{
	/**
	 * Special token to authorization.
	 *
	 * @var string
	 */
	private $token;

	/** {@inheritdoc} */
	public function authorize()
	{
		$url = rtrim($this->config->get('url'), '/') . '/rest/V1/integration/admin/token';
		\App\Log::beginProfile("POST|Token::authorize|{$url}", 'App\Integrations\Magento');
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))
			->post($url, [
				'timeout' => 0,
				'http_errors' => false,
				'json' => ['username' => $this->config->get('user_name'), 'password' => $this->config->get('password')], ]);
		\App\Log::endProfile("POST|Token::authorize|{$url}", 'App\Integrations\Magento');
		if (200 !== $response->getStatusCode()) {
			throw new AppException($response->getReasonPhrase(), $response->getStatusCode());
		}
		$this->token = \App\Json::decode((string) $response->getBody());
	}

	/** {@inheritdoc} */
	public function request(string $method, string $action, array $params = []): string
	{
		$url = rtrim($this->config->get('url'), '/') . "/rest/$action";
		\App\Log::beginProfile("{$method}|Token::request|{$url}", 'App\Integrations\Magento');
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request($method, $url, [
			'headers' => [
				'authorization' => 'Bearer ' . $this->token,
			],
			'timeout' => 0,
			'http_errors' => false,
			'json' => $params, ]);
		\App\Log::endProfile("{$method}|Token::request|{$url}", 'App\Integrations\Magento');
		if (200 !== $response->getStatusCode()) {
			throw new AppException($response->getReasonPhrase(), $response->getStatusCode());
		}
		return (string) $response->getBody();
	}
}
