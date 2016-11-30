<?php
/**
 * Time Control Handler Class
 * @package YetiForce.Handlers
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
vimport('~~modules/com_vtiger_workflow/include.php');
vimport('~~modules/com_vtiger_workflow/VTEntityCache.php');
vimport('~~include/Webservices/Utils.php');
vimport('~~include/Webservices/Retrieve.php');

class TimeControl_TimeControl_Handler
{

	/**
	 * EntityAfterUnLink handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterUnLink(App\EventHandler $eventHandler)
	{
		$params = $eventHandler->getParams();
		$db = PearDatabase::getInstance();
		$wfs = new VTWorkflowManager($db);
		$workflows = $wfs->getWorkflowsForModule($params['destinationModule'], VTWorkflowManager::$MANUAL);
		$wsId = vtws_getWebserviceEntityId($params['destinationModule'], $params['destinationRecordId']);
		$entityCache = new VTEntityCache(Users_Record_Model::getCurrentUserModel());
		$entityData = $entityCache->forId($wsId);
		foreach ($workflows as &$workflow) {
			if ($workflow->evaluate($entityCache, $entityData->getId())) {
				$workflow->performTasks($entityData);
			}
		}
	}
}

class TimeControlHandler extends VTEventHandler
{

	public function handleEvent($eventName, $data)
	{
		if (!is_object($data)) {
			$data = $data['entityData'];
		}
		$moduleName = $data->getModuleName();
		if ($moduleName == 'OSSTimeControl' && in_array($eventName, ['vtiger.entity.aftersave.final', 'vtiger.entity.afterrestore', 'vtiger.entity.afterdelete'])) {
			if ($eventName == 'vtiger.entity.aftersave.final') {
				OSSTimeControl_Record_Model::setSumTime($data);
			}
			$db = PearDatabase::getInstance();
			$wfs = new VTWorkflowManager($db);
			$workflows = $wfs->getWorkflowsForModule($moduleName, VTWorkflowManager::$MANUAL);

			$currentUser = Users_Record_Model::getCurrentUserModel();
			$wsId = vtws_getWebserviceEntityId($moduleName, $data->getId());
			$entityCache = new VTEntityCache($currentUser);
			$entityData = $entityCache->forId($wsId);
			if ($eventName == 'vtiger.entity.afterdelete' && !$entityData->getData()) {
				$entityData->data = $data->getData();
				$entityData->data['id'] = $wsId;
				$entityData->id = $wsId;
				$entityData->mode = 'delete';
				$entityData->moduleName = $moduleName;
			}
			foreach ($workflows as $id => $workflow) {
				if ($workflow->evaluate($entityCache, $entityData->getId())) {
					$workflow->performTasks($entityData);
				}
			}
		}
	}
}
