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
		$userName = empty($userName) ? '-' : substr($userName, 0, $schema->getColumn('username')->size | 50);
		$log->createCommand()
			->insert('o_#__csrf', [
				'username' => $userName,
				'date' => date('Y-m-d H:i:s'),
				'ip' => substr(\App\RequestUtil::getRemoteIP(), 0, $schema->getColumn('ip')->size | 100),
				'referer' => substr(\App\Request::_getServer('HTTP_REFERER', '-'), 0, $schema->getColumn('referer')->size | 300),
				'url' => substr(\App\RequestUtil::getBrowserInfo()->url, 0, $schema->getColumn('url')->size | 300),
				'agent' => substr(\App\Request::_getServer('HTTP_USER_AGENT', '-'), 0, $schema->getColumn('agent')->size | 255),
			])->execute();
	}
}
