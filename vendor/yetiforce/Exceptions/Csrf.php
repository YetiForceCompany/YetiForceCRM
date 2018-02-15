<?php

namespace App\Exceptions;

/**
 * Csrf exception class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Csrf extends Security
{
	public function __construct($message = '', $code = 0, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
		\App\Session::init();
		$userName = \App\Session::get('full_user_name');
		\App\Db::getInstance('log')->createCommand()
			->insert('o_#__csrf', [
				'username' => empty($userName) ? '-' : $userName,
				'date' => date('Y-m-d H:i:s'),
				'ip' => \App\RequestUtil::getRemoteIP(),
				'referer' => \App\Request::_getServer('HTTP_REFERER', '-'),
				'url' => \App\RequestUtil::getBrowserInfo()->url,
				'agent' => \App\Request::_getServer('HTTP_USER_AGENT', '-'),
			])->execute();
	}
}
