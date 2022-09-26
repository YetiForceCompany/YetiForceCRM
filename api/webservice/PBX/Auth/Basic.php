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
		$apiKey = $this->api->request->getHeaders()['x-api-key'] ?? null;
		if (!$apiKey || $apiKey !== \App\Encryption::getInstance()->decrypt($this->api->app['api_key'])) {
			throw new \Api\Core\Exception('Invalid api key', 401);
		}
		return true;
	}
}
