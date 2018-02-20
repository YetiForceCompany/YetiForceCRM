<?php
/**
 * Label updater cron.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$limit = AppConfig::performance('CRON_MAX_NUMBERS_RECORD_LABELS_UPDATER');
$executed = [];
$dataReader = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype',
			'u_#__crmentity_label.label', 'u_#__crmentity_search_label.searchlabel', ])
			->from('vtiger_crmentity')
			->innerJoin('vtiger_tab', 'vtiger_tab.name = vtiger_crmentity.setype')
			->leftJoin('u_#__crmentity_label', ' u_#__crmentity_label.crmid = vtiger_crmentity.crmid')
			->leftJoin('u_#__crmentity_search_label', 'u_#__crmentity_search_label.crmid = vtiger_crmentity.crmid')
			->where(['and', ['vtiger_crmentity.deleted' => 0], ['or', ['u_#__crmentity_label.label' => null], ['u_#__crmentity_search_label.searchlabel' => null]], ['vtiger_tab.presence' => 0]])
			->limit($limit)
			->createCommand()->query();
while ($row = $dataReader->read()) {
	$updater = false;
	if ($row['label'] === null && $row['searchlabel'] !== null) {
		$updater = 'label';
	} elseif ($row['searchlabel'] === null && $row['label'] !== null) {
		$updater = 'searchlabel';
	}
	\App\Record::updateLabel($row['setype'], $row['crmid'], true, $updater);
	--$limit;
	if (0 === $limit) {
		return;
	}
}
$dataReader->close();
$dataReader = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype'])
	->from('vtiger_crmentity')
	->innerJoin('vtiger_tab', 'vtiger_tab.name = vtiger_crmentity.setype')
	->leftJoin('u_#__crmentity_label', ' u_#__crmentity_label.crmid = vtiger_crmentity.crmid')
	->leftJoin('u_#__crmentity_search_label', 'u_#__crmentity_search_label.crmid = vtiger_crmentity.crmid')
	->where(['and', ['vtiger_crmentity.deleted' => 0], ['or', ['u_#__crmentity_label.label' => ''], ['u_#__crmentity_search_label.searchlabel' => '']], ['vtiger_tab.presence' => 0]])
	->limit($limit)
	->createCommand()->query();
while ($row = $dataReader->read()) {
	\App\Record::updateLabel($row['setype'], $row['crmid']);
	--$limit;
	if (0 === $limit) {
		return;
	}
}
$dataReader->close();
$dataReader = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'u_#__crmentity_label.label', 'u_#__crmentity_search_label.searchlabel'])
	->from('vtiger_crmentity')
	->leftJoin('u_#__crmentity_label', ' u_#__crmentity_label.crmid = vtiger_crmentity.crmid')
	->leftJoin('u_#__crmentity_search_label', 'u_#__crmentity_search_label.crmid = vtiger_crmentity.crmid')
	->where(['and', ['vtiger_crmentity.deleted' => 1], ['or', ['not', ['u_#__crmentity_label.label' => null]], ['not', ['u_#__crmentity_search_label.searchlabel' => null]]]])
	->createCommand()->query();
while ($row = $dataReader->read()) {
	$db = App\Db::getInstance();
	if ($row['label'] !== null) {
		$db->createCommand()->delete('u_#__crmentity_label', ['crmid' => $row['crmid']])->execute();
	}
	if ($row['searchlabel'] !== null) {
		$db->createCommand()->delete('u_#__crmentity_search_label', ['crmid' => $row['crmid']])->execute();
	}
	--$limit;
	if (0 === $limit) {
		return;
	}
}
$dataReader->close();
