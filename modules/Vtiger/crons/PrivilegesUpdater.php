<?php
/**
 * Privileges updater cron.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Vtiger_PrivilegesUpdater_Cron class.
 */
class Vtiger_PrivilegesUpdater_Cron extends \App\CronHandler
{
	/** {@inheritdoc} */
	public function process()
	{
		$limit = App\Config::performance('CRON_MAX_NUMBERS_RECORD_PRIVILEGES_UPDATER');
		$query = (new \App\Db\Query())->select(['crmid', 'setype'])
			->from('vtiger_crmentity')
			->innerJoin('vtiger_tab', 'vtiger_tab.name = vtiger_crmentity.setype')
			->where(['vtiger_tab.presence' => 0, 'deleted' => 0, 'users' => null])
			->limit($limit);
		foreach ($query->batch(100) as $rows) {
			$this->updateLastActionTime();
			foreach ($rows as $row) {
				\App\PrivilegeUpdater::update($row['crmid'], $row['setype']);
				--$limit;
				if (0 === $limit) {
					return;
				}
			}
		}

		$query = (new \App\Db\Query())->from('u_#__crmentity_search_label')->where(['userid' => null])->limit($limit);
		foreach ($query->batch(100) as $rows) {
			$this->updateLastActionTime();
			foreach ($rows as $row) {
				\App\PrivilegeUpdater::updateSearch($row['crmid'], \App\Module::getModuleName($row['tabid']));
				--$limit;
				if (0 === $limit) {
					return;
				}
			}
		}

		$query = (new \App\Db\Query())->from('s_#__privileges_updater')->orderBy(['priority' => SORT_DESC])->limit($limit);
		foreach ($query->batch(100) as $rows) {
			$this->updateLastActionTime();
			foreach ($rows as $row) {
				$db = App\Db::getInstance();
				$crmid = $row['crmid'];
				if (0 === (int) $row['type']) {
					\App\PrivilegeUpdater::update($crmid, $row['module']);
					--$limit;
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
							'crmid' => $crmid,
						])->execute();
						$crmid = $rowCrm['crmid'];
						--$limit;
						if (0 === $limit || 0 === (int) $affected) {
							return;
						}
					}
					$dataReaderCrm->close();
				}
				$db->createCommand()->delete('s_#__privileges_updater', [
					'module' => $row['module'],
					'type' => $row['type'],
					'crmid' => $crmid,
				])->execute();
			}
		}
	}
}
