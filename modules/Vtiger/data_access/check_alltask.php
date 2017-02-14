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

	public $config = true;

	public function process($ModuleName, $ID, $record_form, $config)
	{
		if (!isset($ID) || $ID == 0 || $ID == '')
			return ['save_record' => true];
		$count = (new \App\Db\Query())->from('vtiger_activity')
				->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_activity.activityid')
				->where(['and',
					['vtiger_crmentity.deleted' => 0],
					['vtiger_activity.activitytype' => 'Task'],
					['vtiger_activity.status' => $config['status']],
					['or', ['vtiger_activity.link' => $ID], ['vtiger_activity.process' => $ID]]
				])->count();
		if ($count > 0)
			return [
				'save_record' => false,
				'type' => 0,
				'info' => [
					'text' => vtranslate($config['message'], 'DataAccess'),
					'type' => 'error'
				]
			];
		else
			return ['save_record' => true];
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
