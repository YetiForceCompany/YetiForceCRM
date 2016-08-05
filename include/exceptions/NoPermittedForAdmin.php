<?php namespace Exception;

/**
 * No Permitted Exception class
 * @package YetiForce.Exception
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class NoPermittedForAdmin extends \Exception
{

	public function __construct($message = '', $code = 0, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
		\Vtiger_Session::init();

		$request = \AppRequest::init();
		$dbLog = \PearDatabase::getInstance('log');
		$userName = \Vtiger_Session::get('full_user_name');
		$dbLog->insert('o_yf_access_for_admin', [
			'username' => empty($userName) ? '-' : $userName,
			'date' => date('Y-m-d H:i:s'),
			'ip' => \vtlib\Functions::getRemoteIP(),
			'module' => $request->getModule(),
			'url' => \vtlib\Functions::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'request' => json_encode($_REQUEST),
			'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''
		]);
	}
}
