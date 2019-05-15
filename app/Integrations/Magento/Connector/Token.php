<?php
/**
 * Connector based on session.
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
		$response = (new \GuzzleHttp\Client())->post(\App\Config::component('Magento', 'ADDRESS_API') . '/rest/V1/integration/admin/token', [
			'headers' => [
				'user-agent' => 'YetiForceCRM/' . \App\Version::get(),
			],
			'json' => ['username' => \App\Config::component('Magento', 'USERNAME'), 'password' => \App\Config::component('Magento', 'PASSWORD')]]);
		if ($response->getStatusCode() !== 200) {
			throw new AppException();
		}
		$this->token =  str_replace('"', '', (string) $response->getBody());
	}

	/**
	 * {@inheritdoc}
	 */
	public function request(string $method, string $action, array $params = []): string
	{
		$response = (new \GuzzleHttp\Client())->request($method, \App\Config::component('Magento', 'ADDRESS_API') . $action, [
			'headers' => [
				'user-agent' => 'YetiForceCRM/' . \App\Version::get(),
				'authorization' => 'Bearer ' . $this->token
			],
			'json' => $params]);
		if ($response->getStatusCode() !== 200) {
			throw new AppException();
		}
		return (string) $response->getBody();
	}
}
