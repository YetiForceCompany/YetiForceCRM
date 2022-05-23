<?php
/**
 * SabreDav authentication plugin file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Dav\Backend;

use Sabre\DAV;
use Sabre\HTTP;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

/**
 * SabreDav authentication plugin class.
 */
class Auth extends DAV\Auth\Backend\PDO
{
	/**
	 * PDO table name we'll be using.
	 *
	 * @var string
	 */
	public $tableName = 'dav_users';

	/**
	 * Authentication Realm.
	 *
	 * The realm is often displayed by browser clients when showing the
	 * authentication dialog.
	 *
	 * @var string
	 */
	protected $realm = 'YetiDAV';

	/**
	 * When this method is called, the backend must check if authentication was
	 * successful.
	 *
	 * The returned value must be one of the following
	 *
	 * [true, "principals/username"]
	 * [false, "reason for failure"]
	 *
	 * If authentication was successful, it's expected that the authentication
	 * backend returns a so-called principal url.
	 *
	 * Examples of a principal url:
	 *
	 * principals/admin
	 * principals/user1
	 * principals/users/joe
	 * principals/uid/123457
	 *
	 * If you don't use WebDAV ACL (RFC3744) we recommend that you simply
	 * return a string such as:
	 *
	 * principals/users/[username]
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 *
	 * @return array
	 */
	public function check(RequestInterface $request, ResponseInterface $response)
	{
		if (0 === strpos($request->getHeader('Authorization'), 'Basic')) {
			return $this->checkBasic($request, $response);
		}
		return parent::check($request, $response);
	}

	/**
	 * When this method is called, the backend must check if authentication was
	 * successful.
	 *
	 * The returned value must be one of the following
	 *
	 * [true, "principals/username"]
	 * [false, "reason for failure"]
	 *
	 * If authentication was successful, it's expected that the authentication
	 * backend returns a so-called principal url.
	 *
	 * Examples of a principal url:
	 *
	 * principals/admin
	 * principals/user1
	 * principals/users/joe
	 * principals/uid/123457
	 *
	 * If you don't use WebDAV ACL (RFC3744) we recommend that you simply
	 * return a string such as:
	 *
	 * principals/users/[username]
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 *
	 * @return array
	 */
	public function checkBasic(RequestInterface $request, ResponseInterface $response)
	{
		$auth = new HTTP\Auth\Basic(
			$this->realm,
			$request,
			$response
		);
		$userpass = $auth->getCredentials();
		if (!$userpass) {
			return [false, "No 'Authorization: Basic' header found. Either the client didn't send one, or the server is misconfigured"];
		}
		$hash = $this->getDigestHash($this->realm, $userpass[0]);
		if (md5($userpass[0] . ':' . $this->realm . ':' . $userpass[1]) !== $hash) {
			return [false, 'Username or password was incorrect'];
		}
		return [true, $this->principalPrefix . $userpass[0]];
	}

	/**
	 * This method is called when a user could not be authenticated, and
	 * authentication was required for the current request.
	 *
	 * This gives you the opportunity to set authentication headers. The 401
	 * status code will already be set.
	 *
	 * In this case of Basic Auth, this would for example mean that the
	 * following header needs to be set:
	 *
	 * $response->addHeader('WWW-Authenticate', 'Basic realm=SabreDAV');
	 *
	 * Keep in mind that in the case of multiple authentication backends, other
	 * WWW-Authenticate headers may already have been set, and you'll want to
	 * append your own WWW-Authenticate header instead of overwriting the
	 * existing one.
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 */
	public function challenge(RequestInterface $request, ResponseInterface $response)
	{
		if (0 === strpos($request->getHeader('Authorization'), 'Basic')) {
			$auth = new HTTP\Auth\Basic(
				$this->realm,
				$request,
				$response
			);
			$auth->requireLogin();
		} else {
			parent::challenge($request, $response);
		}
	}
}
