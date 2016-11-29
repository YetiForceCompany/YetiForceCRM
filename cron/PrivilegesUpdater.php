<?php
/**
 * Multi reference value cron
 * @package YetiForce.Cron
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
$limit = AppConfig::performance('CRON_MAX_NUMERS_RECORD_PRIVILEGES_UPDATER');
$dataReader = (new \App\Db\Query())->select('crmid, setype')
		->from('vtiger_crmentity')
		->where(['users' => null])
		->limit($limit)
		->createCommand()->query();
while ($row = $dataReader->read()) {
	\App\PrivilegeUpdater::update($row['crmid'], $row['setype']);
	$limit--;
	if (0 === $limit) {
		return;
	}
}
$dataReader = (new \App\Db\Query())
		->from('u_#__crmentity_search_label')
		->where(['userid' => ''])
		->limit($limit)
		->createCommand()->query();
while ($row = $dataReader->read()) {
	\App\PrivilegeUpdater::updateSearch($row['crmid'], $row['setype']);
	$limit--;
	if (0 === $limit) {
		return;
	}
}
$dataReader = (new \App\Db\Query())
		->from('s_#__privileges_updater')
		->orderBy(['priority' => SORT_DESC])
		->limit($limit)
		->createCommand()->query();
while ($row = $dataReader->read()) {
	$db = App\Db::getInstance();
	$crmid = $row['crmid'];
	if (0 === $row['type']) {
		\App\PrivilegeUpdater::update($crmid, $row['module']);
		$limit--;
		if (0 === $limit) {
			return;
		}
	} else {
		$dataReaderCrm = (new \App\Db\Query())->select(['crmid'])
				->from('vtiger_crmentity')
				->where(['and', ['deleted' => 0], ['setype' => $row['module']], ['>', 'crmid', $crmid]])
				->limit($limit)
				->createCommand()->query();
		while ($rowCrm = $dataReaderCrm->read()) {
			\App\PrivilegeUpdater::update($rowCrm['crmid'], $row['module']);
			$affected = $db->createCommand()->update('s_#__privileges_updater', ['crmid' => $rowCrm['crmid']], [
					'module' => $row['module'],
					'type' => 1,
					'crmid' => $crmid
				])->execute();
			$crmid = $rowCrm['crmid'];
			$limit--;
			if (0 === $limit || $affected === 0) {
				return;
			}
		}
	}
	$db->createCommand()->delete('s_#__privileges_updater', [
		'module' => $row['module'],
		'type' => $row['type'],
		'crmid' => $crmid
	])->execute();
}

