<?php
/**
 * Auto assign records Task Class
 * @package YetiForce.WorkflowTask
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once('modules/com_vtiger_workflow/VTEntityCache.php');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');

class VTAutoAssign extends VTTask
{

	public $executeImmediately = true;

	public function getFieldNames()
	{
		return ['template'];
	}

	public function doTask($recordModel)
	{
		Settings_AutomaticAssignment_Module_Model::autoAssignExecute($recordModel);
	}

	public function getAutoAssignEntries($moduleName)
	{
		$moduleName = \App\Module::getTabName($moduleName);
		$listViewModel = Settings_Vtiger_ListView_Model::getInstance('Settings:AutomaticAssignment');
		$listViewModel->set('sourceModule', \App\Module::getModuleId($moduleName));
		$entries = $listViewModel->getListViewEntries(new Vtiger_Paging_Model());
		return $entries;
	}
}
