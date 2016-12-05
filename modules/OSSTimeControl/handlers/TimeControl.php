<?php
/**
 * Time Control Handler Class
 * @package YetiForce.Handler
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
vimport('~~modules/com_vtiger_workflow/include.php');
vimport('~~modules/com_vtiger_workflow/VTEntityCache.php');
vimport('~~include/Webservices/Utils.php');
vimport('~~include/Webservices/Retrieve.php');

class OSSTimeControl_TimeControl_Handler
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

	/**
	 * EntityAfterDelete handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterDelete(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$db = PearDatabase::getInstance();
		$wfs = new VTWorkflowManager($db);
		$workflows = $wfs->getWorkflowsForModule($eventHandler->getModuleName(), VTWorkflowManager::$MANUAL);
		$wsId = vtws_getWebserviceEntityId($eventHandler->getModuleName(), $recordModel->getId());
		$entityCache = new VTEntityCache(Users_Record_Model::getCurrentUserModel());
		$entityData = $entityCache->forId($wsId);
		foreach ($workflows as &$workflow) {
			if ($workflow->evaluate($entityCache, $entityData->getId())) {
				$workflow->performTasks($entityData);
			}
		}
	}

	/**
	 * EntityAfterSave handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterSave(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		OSSTimeControl_Record_Model::setSumTime($recordModel);
		$wfs = new VTWorkflowManager(PearDatabase::getInstance());
		$workflows = $wfs->getWorkflowsForModule($eventHandler->getModuleName(), VTWorkflowManager::$MANUAL);
		$wsId = vtws_getWebserviceEntityId($eventHandler->getModuleName(), $recordModel->getId());
		$entityCache = new VTEntityCache(Users_Record_Model::getCurrentUserModel());
		$entityData = $entityCache->forId($wsId);
		foreach ($workflows as &$workflow) {
			if ($workflow->evaluate($entityCache, $entityData->getId())) {
				$workflow->performTasks($entityData);
			}
		}
	}

	/**
	 * EntityAfterRestore handler function
	 * @param App\EventHandler $eventHandler
	 */
	public function entityAfterRestore(App\EventHandler $eventHandler)
	{
		$recordModel = $eventHandler->getRecordModel();
		$wfs = new VTWorkflowManager(PearDatabase::getInstance());
		$workflows = $wfs->getWorkflowsForModule($eventHandler->getModuleName(), VTWorkflowManager::$MANUAL);
		$wsId = vtws_getWebserviceEntityId($eventHandler->getModuleName(), $recordModel->getId());
		$entityCache = new VTEntityCache(Users_Record_Model::getCurrentUserModel());
		$entityData = $entityCache->forId($wsId);
		foreach ($workflows as &$workflow) {
			if ($workflow->evaluate($entityCache, $entityData->getId())) {
				$workflow->performTasks($entityData);
			}
		}
	}
}
