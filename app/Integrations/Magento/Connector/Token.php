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
		\App\Log::beginProfile('POST|V1/integration/admin/token', 'Integrations/MagentoApi');
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))
			->post(rtrim($this->config->get('url'), '/') . '/rest/V1/integration/admin/token', [
				'timeout' => 0,
				'json' => ['username' => $this->config->get('user_name'), 'password' => $this->config->get('password')]]);
		\App\Log::endProfile('POST|V1/integration/admin/token', 'Integrations/MagentoApi');
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
		\App\Log::beginProfile($method . '|' . $action, 'Integrations/MagentoApi');
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request($method, rtrim($this->config->get('url'), '/') . "/rest/$action", [
			'headers' => [
				'authorization' => 'Bearer ' . $this->token
			],
			'timeout' => 0,
			'json' => $params]);
		\App\Log::endProfile($method . '|' . $action, 'Integrations/MagentoApi');
		if (200 !== $response->getStatusCode()) {
			throw new AppException();
		}
		return (string) $response->getBody();
	}
}
