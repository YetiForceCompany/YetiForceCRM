<?php

/**
 * Logout handler
 * @package YetiForce.User
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class LogoutHandler extends VTEventHandler
{

	function handleEvent($eventName, $entityData)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');

		if ($eventName == 'user.logout.before') {
			$log->debug("Start LogoutHandler: user.logout.before");

			$mainUrl = OSSMail_Record_Model::GetSite_URL() . 'modules/OSSMail/roundcube/';
			vimport('~modules/OSSMail/RoundcubeLogin.class.php');
			$rcl = new RoundcubeLogin($mainUrl);
			try {
				$rcl->logout();
			} catch (RoundcubeLoginException $ex) {
				$log->error('OSSMail_index_View|RoundcubeLoginException: ' . $ex->getMessage());
			}
			$log->debug("End LogoutHandler: user.logout.before");
		}
	}
}
