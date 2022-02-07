<?php
/**
 * AppException Exception class.
 *
 * @package   Exceptions
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Exceptions;

/**
 * Class AppException.
 */
class AppException extends \Exception
{
	/**
	 * Gets the display exception message.
	 *
	 * @return string
	 */
	public function getDisplayMessage()
	{
		$message = $this->getMessage();
		if (false === strpos($message, '||')) {
			$message = \App\Language::translateSingleMod($message, 'Other.Exceptions');
		} else {
			$params = explode('||', $message);
			$message = \call_user_func_array('vsprintf', [\App\Language::translateSingleMod(array_shift($params), 'Other.Exceptions'), $params]);
		}
		return $message;
	}
}
