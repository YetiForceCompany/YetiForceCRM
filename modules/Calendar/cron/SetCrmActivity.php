<?php
/**
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
$dataReader = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype'])
	->from('vtiger_crmentity')
	->innerJoin('vtiger_entity_stats', 'vtiger_entity_stats.crmid = vtiger_crmentity.crmid')
	->innerJoin('vtiger_tab', 'vtiger_tab.name=vtiger_crmentity.setype')
	->innerJoin('vtiger_field', 'vtiger_tab.tabid = vtiger_field.tabid')
	->where(['and', ['vtiger_crmentity.deleted' => 0], ['vtiger_field.tablename' => 'vtiger_entity_stats'], ['not', ['vtiger_entity_stats.crmactivity' => null]], ['not', ['vtiger_field.presence' => 1]]])
	->limit(App\Config::module('Calendar', 'CRON_MAX_NUMBERS_ACTIVITY_STATS'))
	->createCommand()->query();
while ($row = $dataReader->read()) {
	Calendar_Record_Model::setCrmActivity(array_flip([$row['crmid']]), $row['setype']);
}
$dataReader->close();
