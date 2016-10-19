<?php namespace Exception;

/**
 * No Permitted Exception class
 * @package YetiForce.Exception
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Csrf extends \Exception
{

	public function __construct($message = '', $code = 0, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
		\Vtiger_Session::init();

		$dbLog = \PearDatabase::getInstance('log');
		$userName = \Vtiger_Session::get('full_user_name');
		$dbLog->insert('o_yf_csrf', [
			'username' => empty($userName) ? '-' : $userName,
			'date' => date('Y-m-d H:i:s'),
			'ip' => \App\RequestUtil::getRemoteIP(),
			'referer' => $_SERVER['HTTP_REFERER'],
			'url' => \App\RequestUtil::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
		]);
	}
}
