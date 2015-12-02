<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class CalendarHandler extends VTEventHandler
{

	public function handleEvent($handlerType, $entityData)
	{
		$moduleName = $entityData->getModuleName();
		if ($handlerType == 'vtiger.entity.beforesave' && in_array($moduleName, ['Calendar', 'Events', 'Activity'])) {
			$data = $entityData->getData();
			$state = Calendar_Module_Model::getCalendarState($data);
			if ($state) {
				$entityData->set('activitystatus', $state);
			}
		}
	}
}
