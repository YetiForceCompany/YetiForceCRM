<?php
class AJAXChat {
	function vtlib_handler($modulename, $event_type) {
		if($event_type == 'module.postinstall') {

		} else if($event_type == 'module.disabled') {
			$moduleInstance = Vtiger_Module::getInstance($modulename);
			$moduleInstance->deleteLink('HEADERSCRIPT', 'Chat', 'layouts/vlayout/modules/AJAXChat/Chat.js');
			// TODO Handle actions when this module is disabled.
			return;
		} else if($event_type == 'module.enabled') {
			$moduleInstance = Vtiger_Module::getInstance($modulename);
			$moduleInstance->addLink('HEADERSCRIPT', 'Chat', 'layouts/vlayout/modules/AJAXChat/Chat.js');
			// TODO Handle actions when this module is enabled.
			return;
		} else if($event_type == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
			return;		
		} else if($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
			return;			
		} else if($event_type == 'module.postupdate') {
		
		}
	}
}