<?php

namespace App\Exceptions;

/**
 * Csrf exception class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Csrf extends Security
{
	public function __construct($message = '', $code = 0, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
		\App\Session::init();
		$log = \App\Db::getInstance('log');
		$schema = $log->getTableSchema('o_#__csrf');
		$userName = \App\Session::get('full_user_name');
		$userName = empty($userName) ? '-' : substr($userName, 0, $schema->getColumn('username')->size);
		$log->createCommand()
			->insert('o_#__csrf', [
				'username' => $userName,
				'date' => date('Y-m-d H:i:s'),
				'ip' => \App\RequestUtil::getRemoteIP(),
				'referer' => substr(\App\Request::_getServer('HTTP_REFERER', '-'), 0, $schema->getColumn('referer')->size),
				'url' => substr(\App\RequestUtil::getBrowserInfo()->url, 0, $schema->getColumn('url')->size),
				'agent' => substr(\App\Request::_getServer('HTTP_USER_AGENT', '-'), 0, $schema->getColumn('agent')->size),
			])->execute();
	}
}
