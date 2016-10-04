<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */

class CalendarHandler extends VTEventHandler
{

	public function handleEvent($handlerType, $entityData)
	{
		if (!is_object($entityData)) {
			$entityData = $entityData['entityData'];
		}
		$moduleName = $entityData->getModuleName();
		if (!vtlib\Cron::isCronAction() && in_array($handlerType, ['vtiger.entity.unlink.after', 'vtiger.entity.afterrestore', 'vtiger.entity.aftersave.final']) && in_array($moduleName, ['Calendar', 'Events', 'Activity'])) {
			$recordId = $entityData->getId();
			$delta = [];
			if ($handlerType != 'vtiger.entity.afterrestore') {
				$vtEntityDelta = new VTEntityDelta();
				$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);
			}
			Calendar_Record_Model::setCrmActivity(self::getRefernceIds($entityData->getData(), $delta));
		} elseif (!vtlib\Cron::isCronAction() && $handlerType == 'vtiger.entity.beforesave' && in_array($moduleName, ['Calendar', 'Events', 'Activity'])) {
			$data = $entityData->getData();
			$state = Calendar_Module_Model::getCalendarState($data);
			if ($state) {
				$entityData->set('activitystatus', $state);
			}
		}
	}

	public static function getRefernceIds($data, $delta)
	{
		$referenceIds = [];
		foreach (Calendar_Record_Model::$referenceFields as $fieldName) {
			if (!empty($data[$fieldName])) {
				$referenceIds[$data[$fieldName]] = $fieldName;
			}
			if (!empty($delta[$fieldName])) {
				if (!empty($delta[$fieldName]['oldValue'])) {
					$referenceIds[$delta[$fieldName]['oldValue']] = $fieldName;
				}
				if (!empty($delta[$fieldName]['currentValue'])) {
					$referenceIds[$delta[$fieldName]['currentValue']] = $fieldName;
				}
			}
		}
		return $referenceIds;
	}
}
