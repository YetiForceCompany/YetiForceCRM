<?php

/**
 * Error codes container class.
 *
 * @package   Log
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace App\Log;

/**
 * Class ErrorCodes container.
 */
class ErrorCodes
{
	/**
	 * @var string name of the DB table to store event logs content.
	 */
	public $logTableMail = 'l_#__mail';

	/**
	 * @var array mail error codes to labels
	 */
	public static $errorCodesMail = [
		1 => 'No SMTP configuration id provided',
		2 => 'SMTP configuration with provided id not exists',
		3 => 'No target email address provided'
	];
}
