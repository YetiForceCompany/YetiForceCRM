<?php
/**
 * Digest authorization file.
 *
 * @package API
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Core\Auth;

/**
 * Digest authorization class.
 */
class Digest extends AbstractAuth
{
	/** {@inheritdoc}  */
	public function authenticate(string $realm): bool
	{
		$userpass = $this->getCredentials();
		if (!$userpass) {
			$this->requireLogin();
			throw new \Api\Core\Exception('No basic authentication headers were found', 401);
		}

		// Authenticates the user
		if (!$this->validateUserPass($userpass[0], $userpass[1])) {
			$this->requireLogin();
			throw new \Api\Core\Exception('Username or password does not match', 401);
		}
		$this->currentUser = $userpass[0];

		return true;
	}

	public function getCredentials()
	{
		$auth = $this->api->request->getHeader('authorization');
		if (!$auth) {
			return null;
		}
		if ('basic ' !== strtolower(substr($auth, 0, 6))) {
			return null;
		}
		return explode(':', base64_decode(substr($auth, 6)), 2);
	}

	public function requireLogin()
	{
		$this->api->response->addHeader('WWW-Authenticate', 'Basic realm="' . $this->realm . '"');
		$this->api->response->setStatus(401);
	}
}
