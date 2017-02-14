<?php namespace Exception;

/**
 * No Permitted Exception class
 * @package YetiForce.Exception
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class NoPermittedToRecord extends NoPermitted
{

	public function __construct($message = '', $code = 0, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
		\Vtiger_Session::init();

		$request = \AppRequest::init();
		$record = $request->get('record');
		if(empty($record))
			$record = 0;
		$userName = \Vtiger_Session::get('full_user_name');
		\App\DB::getInstance('log')->createCommand()->insert('o_#__access_to_record', [
			'username' => empty($userName) ? '-' : $userName,
			'date' => date('Y-m-d H:i:s'),
			'ip' => \App\RequestUtil::getRemoteIP(),
			'record' => $record,
			'module' => $request->getModule(),
			'url' => \App\RequestUtil::getBrowserInfo()->url,
			'agent' => $_SERVER['HTTP_USER_AGENT'],
			'request' => json_encode($_REQUEST),
			'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''
		])->execute();
	}
}
