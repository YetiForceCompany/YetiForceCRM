<?php

namespace App\Exceptions;

/**
 * Csrf exception class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Csrf extends Security
{
	public function __construct($message = '', $code = 0, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
		\App\Session::init();
		$userName = \App\Session::get('full_user_name');
		$userName = empty($userName) ? '-' : \App\TextUtils::textTruncate($userName, 100, false);
		\App\Db::getInstance('log')->createCommand()
			->insert('o_#__csrf', [
				'username' => $userName,
				'date' => date('Y-m-d H:i:s'),
				'ip' => \App\TextUtils::textTruncate(\App\RequestUtil::getRemoteIP(), 100, false),
				'referer' => \App\TextUtils::textTruncate(\App\Request::_getServer('HTTP_REFERER', '-'), 300, false),
				'url' => \App\TextUtils::textTruncate(\App\RequestUtil::getBrowserInfo()->url, 300, false),
				'agent' => \App\TextUtils::textTruncate(\App\Request::_getServer('HTTP_USER_AGENT', '-'), 500, false),
			])->execute();
	}
}
