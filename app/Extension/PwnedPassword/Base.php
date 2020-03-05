<?php
/**
 * Base provider file to check the password.
 *
 * @package App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * Function to check the password.
	 *
	 * @param string $password
	 */
	abstract public function check(string $password): bool;

	/**
	 * Function checks if provider is active.
	 *
	 * @param string $password
	 */
	abstract public function isActive(): bool;
}
