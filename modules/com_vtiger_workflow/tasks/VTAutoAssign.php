<?php
/**
 * Auto assign records Task Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';

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
		$listViewModel = Settings_Vtiger_ListView_Model::getInstance('Settings:AutomaticAssignment');
		$listViewModel->set('sourceModule', \App\Module::getModuleId($moduleName));
		return $listViewModel->getListViewEntries(new Vtiger_Paging_Model());
	}
}
