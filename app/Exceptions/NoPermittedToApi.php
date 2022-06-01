<?php

namespace App\Exceptions;

/**
 * No permitted to api exception class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class NoPermittedToApi extends Security
{
	/** {@inheritdoc} */
	public function __construct($message = '', $code = 406, \Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
		\App\Session::init();
		\Api\Core\Request::init();
		$userName = \App\Session::get('full_user_name');
		\App\Db::getInstance('log')->createCommand()
			->insert('o_#__access_for_api', [
				'username' => empty($userName) ? '-' : $userName,
				'date' => date('Y-m-d H:i:s'),
				'ip' => \App\TextUtils::textTruncate(\App\RequestUtil::getRemoteIP(), 100, false),
				'url' => \App\TextUtils::textTruncate(\App\RequestUtil::getBrowserInfo()->url, 300, false),
				'agent' => \App\TextUtils::textTruncate(\App\Request::_getServer('HTTP_USER_AGENT', '-'), 500, false),
				'request' => json_encode((new \App\Anonymization())->setModuleName($_REQUEST['module'] ?? '')->setData($_REQUEST)->anonymize()->getData()),
			])->execute();
	}

	/**
	 * Display message.
	 *
	 * @param string $message
	 */
	public function stop(string $message)
	{
		echo json_encode($message);
	}
}
