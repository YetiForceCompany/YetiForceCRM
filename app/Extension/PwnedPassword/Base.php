<?php
/**
 * Base provider file to check the password.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Extension\PwnedPassword;

/**
 * Base provider class to check the password.
 */
abstract class Base
{
	/**
	 * Provider url.
	 *
	 * @var string
	 */
	public $url;
	/**
	 * Website url with additional information.
	 *
	 * @var string
	 */
	public $infoUrl;

	/**
	 * Function to check the password.
	 *
	 * @param string $password
	 *
	 * @return array ['message' => (string) , 'status' => (bool)]
	 */
	abstract public function check(string $password): array;

	/**
	 * Function checks if provider is active.
	 *
	 * @param string $password
	 */
	abstract public function isActive(): bool;
}
