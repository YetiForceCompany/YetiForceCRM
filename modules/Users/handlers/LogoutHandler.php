<?php

/**
 * Logout handler
 * @package YetiForce.User
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class LogoutHandler extends VTEventHandler
{

	public function handleEvent($eventName, $entityData)
	{
		if ($eventName == 'user.logout.before') {
			
		}
	}
}
