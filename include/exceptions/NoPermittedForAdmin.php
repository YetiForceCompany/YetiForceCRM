<?php
namespace Exception;

/**
 * No Permitted Exception class
 * @package YetiForce.Exception
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class NoPermittedForAdmin extends \Exception
{

	/**
	 * Constructor
	 * @param string $message
	 * @param int $code
	 * @param \Exception $previous
	 */
	public function __construct($message = '', $code = 0, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
		\Vtiger_Session::init();

		$request = \AppRequest::init();
		$dbLog = \PearDatabase::getInstance('log');
		$userName = \Vtiger_Session::get('full_user_name');

		$data = [
			'username' => empty($userName) ? '-' : $userName,
			'date' => date('Y-m-d H:i:s'),
			'ip' => \App\RequestUtil::getRemoteIP(),
			'module' => $request->getModule(),
			'url' => \App\RequestUtil::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'request' => json_encode($_REQUEST),
			'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''
		];
		\App\Db::getInstance()->createCommand()->insert('o_#__access_for_admin', $data)->execute();
	}
}
