<?php
namespace Exception;

/**
 * No Permitted Exception class
 * @package YetiForce.Exception
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class NoPermittedToApi extends \Exception
{

	public function __construct($message = '', $code = 0, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
		\Vtiger_Session::init();

		$request = \AppRequest::init();
		$dbLog = \PearDatabase::getInstance('log');
		$userName = \Vtiger_Session::get('full_user_name');
		$dbLog->insert('o_yf_access_for_api', [
			'username' => empty($userName) ? '-' : $userName,
			'date' => date('Y-m-d H:i:s'),
			'ip' => \App\RequestUtil::getRemoteIP(),
			'url' => \App\RequestUtil::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'request' => json_encode($_REQUEST),
		]);
	}
}
