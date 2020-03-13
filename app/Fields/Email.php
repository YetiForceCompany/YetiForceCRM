<?php

namespace App\Fields;

/**
 * Tools for email class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Email
{
	/**
	 * Get user mail.
	 *
	 * @param int $userId
	 *
	 * @return string
	 */
	public static function getUserMail($userId)
	{
		return \App\User::getUserModel($userId)->getDetail('email1');
	}
}
