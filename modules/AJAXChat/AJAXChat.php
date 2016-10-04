<?php

/**
 * 
 * @package YetiForce.handler
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class AJAXChat
{

	public function vtlib_handler($modulename, $event_type)
	{
		if ($event_type == 'module.postinstall') {
			
		} else if ($event_type == 'module.disabled') {
			$moduleInstance = vtlib\Module::getInstance($modulename);
			$moduleInstance->deleteLink('HEADERSCRIPT', 'Chat', 'layouts/_layoutName_/modules/AJAXChat/Chat.js');
			return;
		} else if ($event_type == 'module.enabled') {
			if (Settings_ModuleManager_Library_Model::checkLibrary('AJAXChat')) {
				throw new \Exception\NotAllowedMethod(vtranslate('ERR_NO_REQUIRED_LIBRARY', 'Settings:Vtiger', 'AJAXChat'));
			}
			$moduleInstance = vtlib\Module::getInstance($modulename);
			$moduleInstance->addLink('HEADERSCRIPT', 'Chat', 'layouts/_layoutName_/modules/AJAXChat/Chat.js');
			return;
		} else if ($event_type == 'module.preuninstall') {
			return;
		} else if ($event_type == 'module.preupdate') {
			return;
		} else if ($event_type == 'module.postupdate') {
			
		}
	}
}
