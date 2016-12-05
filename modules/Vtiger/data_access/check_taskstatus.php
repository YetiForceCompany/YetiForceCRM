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

		$activitiesExists = (new \App\Db\Query())
				->from('vtiger_projecttask')
				->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_projecttask.projecttaskid')
				->where([
					'vtiger_crmentity.deleted' => 0,
					'vtiger_projecttask.projecttaskstatus' => $config['status'],
					'vtiger_projecttask.parentid' => $ID
				])->exists();
		if ($activitiesExists) {
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
		return ['status' => (new App\Db\Query())->select(['projecttaskstatus'])->from('vtiger_projecttaskstatus')->orderBy('sortorderid')->column()];
	}
}
