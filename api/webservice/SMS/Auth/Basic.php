<?php
/**
 * Basic authorization file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\SMS\Auth;

/**
 * Basic authorization class.
 */
class Basic extends \Api\Core\Auth\Basic
{
	/** {@inheritdoc}  */
	public function authenticate(string $realm): bool
	{
		if (!$this->api->app) {
			$this->api->response->addHeader('WWW-Authenticate', 'Basic realm="' . $realm . '"');
			throw new \Api\Core\Exception('Web service - Applications: Unauthorized', 401);
		}

		return true;
	}

	/** {@inheritdoc} */
	public function setServer(): self
	{
		$this->api->app = [];
		$apiKey = $this->api->request->getByType('x-api-key', \App\Purifier::ALNUM);
		$type = $this->api->request->getByType('_container', \App\Purifier::STANDARD);
		$query = (new \App\Db\Query())->from('w_#__servers')->where(['type' => $type, 'status' => 1]);
		if ($apiKey && $row = $query->andWhere(['api_key' => \App\Encryption::getInstance()->encrypt($apiKey)])->one()) {
			$row['id'] = (int) $row['id'];
			$this->api->app = $row;
		}

		return $this;
	}
}
