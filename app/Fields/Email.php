<?php

namespace App\Fields;

/**
 * Tools for email class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
