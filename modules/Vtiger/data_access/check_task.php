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

Class DataAccess_check_task
{

	public $config = true;

	public function process($ModuleName, $ID, $record_form, $config)
	{
		if (!isset($ID) || $ID == 0 || $ID == '')
			return ['save_record' => true];
		$activitiesExists = (new \App\Db\Query())->from('vtiger_activity')->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_activity.activityid')
				->where(['and',
					['vtiger_crmentity.deleted' => 0],
					['vtiger_activity.activitytype' => 'Task'],
					['vtiger_activity.status' => $config['status']],
					['vtiger_activity.subject' => $config['name']],
					['or', ['vtiger_activity.link' => $ID], ['vtiger_activity.process' => $ID]]
				])->exists();
		if (!$activitiesExists)
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
		return ['status' => (new App\Db\Query())->select(['activitystatus'])->from('vtiger_activitystatus')->orderBy('sortorderid')->column()];
	}
}
