<?php
/**
 * Cron for workflow.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Vtiger_Workflow_Cron class.
 */
class Vtiger_Workflow_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		require_once 'include/Webservices/Utils.php';
		require_once 'include/Webservices/WebServiceError.php';
		require_once 'include/utils/VtlibUtils.php';
		require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';
		require_once 'modules/com_vtiger_workflow/include.php';
		require_once 'modules/com_vtiger_workflow/WorkFlowScheduler.php';

		$workflowScheduler = new WorkFlowScheduler();
		$workflowScheduler->queueScheduledWorkflowTasks();
		$readyTasks = (new VTTaskQueue())->getReadyTasks();
		$tm = new VTTaskManager();
		foreach ($readyTasks as $taskDetails) {
			[$taskId, $entityId, $taskContents] = $taskDetails;
			$task = $tm->retrieveTask($taskId);
			//If task is not there then continue
			if (empty($task) || !\App\Record::isExists($entityId)) {
				continue;
			}
			$task->setContents($taskContents);
			$task->doTask(Vtiger_Record_Model::getInstanceById($entityId));
			if ($this->checkTimeout()) {
				return;
			}
		}
	}
}
