<?php
/**
 * AppException Exception class.
 *
 * @package   Exceptions
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Exceptions;

/**
 * Class AppException.
 */
class AppException extends \Exception
{
	/**
	 * Construct function.
	 *
	 * @param string     $message
	 * @param int        $code
	 * @param \Throwable $previous
	 */
	public function __construct($message = '', $code = 500, \Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Gets the display exception message.
	 *
	 * @return string
	 */
	public function getDisplayMessage()
	{
		return \App\ErrorHandler::parseException($this)['message'];
	}
}
