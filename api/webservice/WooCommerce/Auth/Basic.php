<?php
/**
 * Basic WooCommerce authorization file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WooCommerce\Auth;

/**
 * Basic WooCommerce authorization class.
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
			if (empty($this->api->app['api_key'])) {
				throw new \Api\Core\Exception('Invalid api key', 401);
			}
		}
		return $this;
	}

	/** {@inheritdoc}  */
	public function authenticate(string $realm): bool
	{
		$headers = $this->api->request->getHeaders();
		if (empty($headers['x-wc-webhook-signature'])) {
			throw new \Api\Core\Exception('No signature', 401);
		}
		$signature = $headers['x-wc-webhook-signature'];
		if (empty($this->api->app['api_key'])) {
			throw new \Api\Core\Exception('Invalid api key', 401);
		}
		$content = file_get_contents('php://input');
		$apiKey = \App\Encryption::getInstance()->decrypt($this->api->app['api_key']);
		$sig = base64_encode(hash_hmac('sha256', $content, $apiKey, true));
		if ($sig !== $signature) {
			throw new \Api\Core\Exception('Invalid signature', 401);
		}
		return true;
	}
}
