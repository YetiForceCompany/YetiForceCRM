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

Class DataAccess_check_alltask
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
								FROM vtiger_activity 
								INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid 
								WHERE vtiger_crmentity.deleted = ? 
								AND vtiger_activity.activitytype = ? 
								AND vtiger_activity.status in ('%s')
								AND (vtiger_activity.link = ? || vtiger_activity.process = ?)", $config['status']), array(0, 'Task', $ID, $ID), true);

		if ($db->query_result($result, 0, 'num') > 0)
			return Array(
				'save_record' => false,
				'type' => 0,
				'info' => Array(
					'text' => vtranslate($config['message'], 'DataAccess'),
					'type' => 'error'
				)
			);
		else
			return Array('save_record' => true);
	}

	public function getConfig($id, $module, $baseModule)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT * FROM vtiger_activitystatus ORDER BY sortorderid", [], true);
		$fields = [];
		while ($row = $db->fetch_array($result)) {
			array_push($fields, $row['activitystatus']);
		}
		return Array('status' => $fields);
	}
}
