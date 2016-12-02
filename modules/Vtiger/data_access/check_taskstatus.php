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

	public $config = true;

	public function process($ModuleName, $ID, $record_form, $config)
	{
		if (!isset($ID) || $ID == 0 || $ID == '')
			return ['save_record' => true];

		$countActivities = (new \App\Db\Query())
				->from('vtiger_projecttask')
				->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_projecttask.projecttaskid')
				->where([
					'vtiger_crmentity.deleted' => 0,
					'vtiger_projecttask.projecttaskstatus' => $config['status'],
					'vtiger_projecttask.parentid' => $ID
				])->count();
		if ($countActivities > 0) {
			return [
				'save_record' => false,
				'type' => 0,
				'info' => [
					'text' => \App\Language::translate('Subordinate tasks have not been completed yet', 'DataAccess'),
					'type' => 'error'
				]
			];
		} else {
			return ['save_record' => true];
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
