<?php
/**
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
$dataReader = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype'])
		->from('vtiger_crmentity')
		->innerJoin('vtiger_entity_stats', 'vtiger_entity_stats.crmid = vtiger_crmentity.crmid')
		->where(['and', ['vtiger_crmentity.deleted' => 0], ['not', ['vtiger_entity_stats.crmactivity' => null]]])
		->limit(AppConfig::module('Calendar', 'CRON_MAX_NUMERS_ACTIVITY_STATS'))
		->createCommand()->query();
while ($row = $dataReader->read()) {
	Calendar_Record_Model::setCrmActivity(array_flip([$row['crmid']]), $row['setype']);
}

