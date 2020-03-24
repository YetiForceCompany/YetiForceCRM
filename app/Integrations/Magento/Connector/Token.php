<?php
/**
 * Connector based on session.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	/**
	 * {@inheritdoc}
	 */
	public function authorize()
	{
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))
			->post($this->config->get('addressApi') . 'rest/V1/integration/admin/token', [
				'timeout' => 0,
				'json' => ['username' => $this->config->get('username'), 'password' => $this->config->get('password')]]);
		if (200 !== $response->getStatusCode()) {
			throw new AppException();
		}
		$this->token = \App\Json::decode((string) $response->getBody());
	}

	/**
	 * {@inheritdoc}
	 */
	public function request(string $method, string $action, array $params = []): string
	{
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request($method, $this->config->get('addressApi') . "rest/$action", [
			'headers' => [
				'authorization' => 'Bearer ' . $this->token
			],
			'timeout' => 0,
			'json' => $params]);
		if (200 !== $response->getStatusCode()) {
			throw new AppException();
		}
		return (string) $response->getBody();
	}
}
