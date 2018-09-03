<?php

namespace App\Exceptions;

/**
 * Unauthorized action exception class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Unauthorized extends Security
{
	/**
	 * {@inheritdoc}
	 */
	public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
	{
		header('yf-action: logout');
		\App\Session::set('UserLoginMessage', \App\Language::translate('ERR_AUTO_LOGOUT', 'Other:Exceptions'));
		\App\Session::set('UserLoginMessageType', 'error');
		parent::__construct($message, $code, $previous);
	}
}
