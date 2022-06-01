<?php

namespace App\Exceptions;

/**
 * No permitted exception class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class NoPermitted extends Security
{
	public function __construct($message = '', $code = 406, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
		\App\Session::init();

		$request = \App\Request::init();
		$userName = \App\Session::get('full_user_name');
		\App\DB::getInstance('log')->createCommand()->insert('o_#__access_for_user', [
			'username' => empty($userName) ? '-' : $userName,
			'date' => date('Y-m-d H:i:s'),
			'ip' => \App\TextUtils::textTruncate(\App\RequestUtil::getRemoteIP(), 100, false),
			'module' => $request->getModule(),
			'url' => \App\TextUtils::textTruncate(\App\RequestUtil::getBrowserInfo()->url, 300, false),
			'agent' => \App\TextUtils::textTruncate(\App\Request::_getServer('HTTP_USER_AGENT', '-'), 500, false),
			'request' => json_encode((new \App\Anonymization())->setModuleName($request->getModule())->setData($_REQUEST)->anonymize()->getData()),
			'referer' => \App\TextUtils::textTruncate(\App\Request::_getServer('HTTP_REFERER', '-'), 300, false),
		])->execute();
	}
}
