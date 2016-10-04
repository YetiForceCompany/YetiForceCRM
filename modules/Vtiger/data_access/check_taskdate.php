<?php
/*
  Return Description
  ------------------------
  Info type: error, info, success
  Info title: optional
  Info text: mandatory
  Type: 0 - notify
  Type: 1 - show quick create mondal
 */

Class DataAccess_check_taskdate
{

	public $config = false;

	public function process($ModuleName, $ID, $record_form, $config)
	{
		$projectmilestoneid = $record_form['projectmilestoneid'];
		if (!isset($projectmilestoneid) || $projectmilestoneid == 0 || $projectmilestoneid == '')
			return Array('save_record' => true);

		$moduleModel = Vtiger_Record_Model::getInstanceById($projectmilestoneid, 'ProjectMilestone');
		$projectMilestoneDate = $moduleModel->get('projectmilestonedate');
		if (!isset($projectMilestoneDate) || $projectMilestoneDate == 0 || $projectMilestoneDate == '')
			return Array('save_record' => true);

		$dateField = new DateTimeField($projectMilestoneDate);
		$projectMilestoneDateUserFormat = $dateField->convertToUserFormat($projectMilestoneDate);
		$dateField = new DateTimeField($record_form['targetenddate']);
		$targetEndDateUserFormat = $dateField->convertToDBFormat($record_form['targetenddate']);

		if (strtotime($targetEndDateUserFormat) > strtotime($projectMilestoneDate)) {
			return Array(
				'save_record' => false,
				'type' => 0,
				'info' => Array(
					'text' => vtranslate('Date can not be greater', 'DataAccess') . ' ( ' . $record_form['targetenddate'] . ' < ' . $projectMilestoneDateUserFormat . ')',
					'type' => 'error'
				)
			);
		} else {
			return Array('save_record' => true);
		}
	}

	public function getConfig($id, $module, $baseModule)
	{
		return [];
	}
}
