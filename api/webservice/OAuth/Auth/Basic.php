<?php
/**
 * Basic OAuth authorization file.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Api\OAuth\Auth;

/**
 * Basic authorization class.
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
		if ($this->api->request->isEmpty('action', true)) {
			throw new \Api\Core\Exception('ERR_NO_TOKEN', 404);
		}
		if (!\App\Validator::alnum($this->api->request->getRaw('action'))) {
			throw new \App\Exceptions\Security('ERR_TOKEN_DOES_NOT_EXIST', 406);
		}

		return true;
	}
}
