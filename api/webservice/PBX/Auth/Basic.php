<?php
/**
 * Basic PBX authorization file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\PBX\Auth;

/**
 * Basic PBX authorization class.
 */
class Basic extends \Api\Core\Auth\Basic
{
	/** {@inheritdoc} */
	public function setServer(): self
	{
		$this->api->app = [];
		$type = $this->api->request->getByType('_container', \App\Purifier::STANDARD);
		$query = (new \App\Db\Query())->from('w_#__servers')->where(['type' => $type,  'status' => 1]);
		if ($row = $query->one()) {
			$row['id'] = (int) $row['id'];
			$this->api->app = $row;
		}
		return $this;
	}

	/** {@inheritdoc}  */
	public function authenticate(string $realm): bool
	{
		$apiKey = \App\Encryption::getInstance()->decrypt($this->api->app['api_key']);
		$headers = $this->api->request->getHeaders();
		if (!empty($headers['x-api-key'])) {
			if ($headers['x-api-key'] !== $apiKey) {
				throw new \Api\Core\Exception('Invalid api key', 401);
			}
		} elseif (!empty($headers['x-api-signature'])) {
			$sig = base64_encode(hash_hmac('sha256', file_get_contents('php://input'), $apiKey, true));
			if ($sig !== $headers['x-api-signature']) {
				throw new \Api\Core\Exception('Invalid signature', 401);
			}
		} else {
			throw new \Api\Core\Exception('No API key or signature', 401);
		}
		return true;
	}
}
