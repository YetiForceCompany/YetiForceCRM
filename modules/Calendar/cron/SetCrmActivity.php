<?php
/**
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
$dataReader = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype'])
	->from('vtiger_crmentity')
	->innerJoin('vtiger_entity_stats', 'vtiger_entity_stats.crmid = vtiger_crmentity.crmid')
	->where(['and', ['vtiger_crmentity.deleted' => 0], ['not', ['vtiger_entity_stats.crmactivity' => null]]])
	->limit(AppConfig::module('Calendar', 'CRON_MAX_NUMBERS_ACTIVITY_STATS'))
	->createCommand()->query();
while ($row = $dataReader->read()) {
	Calendar_Record_Model::setCrmActivity(array_flip([$row['crmid']]), $row['setype']);
}
$dataReader->close();
