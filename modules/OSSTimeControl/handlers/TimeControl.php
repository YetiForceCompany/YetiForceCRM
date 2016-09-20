<?php

/**
 * Time Control Handler Class
 * @package YetiForce.Handlers
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class TimeControlHandler extends VTEventHandler
{

	public function handleEvent($eventName, $data)
	{
		if (!is_object($data)) {
			$data = $data['entityData'];
		}
		$moduleName = $data->getModuleName();
		if ($moduleName == 'OSSTimeControl' && in_array($eventName, ['vtiger.entity.aftersave.final', 'vtiger.entity.afterrestore', 'vtiger.entity.afterdelete', 'vtiger.entity.unlink.after'])) {
			if ($eventName == 'vtiger.entity.aftersave.final') {
				OSSTimeControl_Record_Model::setSumTime($data);
			}
			vimport('~~modules/com_vtiger_workflow/include.inc');
			vimport('~~modules/com_vtiger_workflow/VTEntityCache.inc');
			vimport('~~include/Webservices/Utils.php');
			vimport('~~include/Webservices/Retrieve.php');
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
