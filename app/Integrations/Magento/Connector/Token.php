<?php
/**
 * Connector based on session.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App\Integrations\Magento\Connector;

use App\Exceptions\AppException;

/**
 * Token class.
 */
class Token implements ConnectorInterface
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
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->post(\App\Config::component('Magento', 'addressApi') . '/rest/V1/integration/admin/token', [
			'timeout' => 0,
			'json' => ['username' => \App\Config::component('Magento', 'username'), 'password' => \App\Config::component('Magento', 'password')]]);
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
		$response = (new \GuzzleHttp\Client(\App\RequestHttp::getOptions()))->request($method, \App\Config::component('Magento', 'addressApi') . $action, [
			'headers' => [
				'user-agent' => 'YetiForceCRM/' . \App\Version::get(),
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
