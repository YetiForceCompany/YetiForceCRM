<?php
/**
 * Base mail composer driver file.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail\Composers;

/**
 * Base mail composer driver class.
 */
class Base
{
	/** @var string Class name */
	const NAME = 'LBL_DEFAULT_IN_BROWSER';

	/**
	 * Whether the mail message composer is active.
	 *
	 * @return bool
	 */
	public function isActive(): bool
	{
		return true;
	}

	/**
	 * Send mail message.
	 *
	 * @param \App\Request $request
	 *
	 * @return array
	 */
	public function sendMail(\App\Request $request): array
	{
		return [];
	}
}
