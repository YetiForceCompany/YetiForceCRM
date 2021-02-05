<?php

namespace App\Exceptions;

/**
 * No permitted for admin exception class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class NoPermittedForAdmin extends Security
{
	/**
	 * Constructor.
	 *
	 * @param string     $message
	 * @param int        $code
	 * @param \Exception $previous
	 */
	public function __construct($message = '', $code = 406, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
		\App\Session::init();
		$request = \App\Request::init();
		$userName = \App\Session::get('full_user_name');
		$data = [
			'username' => empty($userName) ? '-' : $userName,
			'date' => date('Y-m-d H:i:s'),
			'ip' => \App\TextParser::textTruncate(\App\RequestUtil::getRemoteIP(), 100, false),
			'module' => $request->getModule(),
			'url' => \App\TextParser::textTruncate(\App\RequestUtil::getBrowserInfo()->url, 300, false),
			'agent' => \App\TextParser::textTruncate(\App\Request::_getServer('HTTP_USER_AGENT', '-'), 500, false),
			'request' => json_encode((new \App\Anonymization())->setModuleName($request->getModule())->setData($_REQUEST)->anonymize()->getData()),
			'referer' => \App\TextParser::textTruncate(\App\Request::_getServer('HTTP_REFERER', '-'), 300, false)
		];
		\App\Db::getInstance('log')->createCommand()->insert('o_#__access_for_admin', $data)->execute();
	}
}
