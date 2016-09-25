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

Class DataAccess_check_taskstatus
{

	var $config = true;

	public function process($ModuleName, $ID, $record_form, $config)
	{
		$db = PearDatabase::getInstance();
		if (!isset($ID) || $ID == 0 || $ID == '')
			return Array('save_record' => true);
		if (is_array($config['status']))
			$config['status'] = implode("','", $config['status']);

		$result = $db->pquery(sprintf("SELECT count(*) as num
								FROM vtiger_projecttask 
								INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_projecttask.projecttaskid 
								WHERE vtiger_crmentity.deleted = ? 
								AND vtiger_projecttask.projecttaskstatus in ('%s')
								AND vtiger_projecttask.parentid = ?", $config['status']), array(0, $ID), true);

		if ($db->query_result($result, 0, 'num') > 0) {
			return Array(
				'save_record' => false,
				'type' => 0,
				'info' => Array(
					'text' => vtranslate('Subordinate tasks have not been completed yet', 'DataAccess'),
					'type' => 'error'
				)
			);
		} else {
			return Array('save_record' => true);
		}
	}

	public function getConfig($id, $module, $baseModule)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT projecttaskstatus FROM vtiger_projecttaskstatus ORDER BY sortorderid", [], true);
		$fields = [];
		while ($row = $db->fetch_array($result)) {
			array_push($fields, $row['projecttaskstatus']);
		}
		return Array('status' => $fields);
	}
}
