<?php
/**
 * Label updater cron.
 *
 * @package   Cron
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Vtiger_LabelUpdater_Cron class.
 */
class Vtiger_LabelUpdater_Cron extends \App\CronHandler
{
	/**
	 * The maximum number of record labels that cron can update during a single execution.
	 *
	 * @var int
	 */
	private $limit;

	/** {@inheritdoc} */
	public function process()
	{
		$this->limit = App\Config::performance('CRON_MAX_NUMBERS_RECORD_LABELS_UPDATER');
		$this->addLabels();
		if ($this->limit && !$this->checkTimeout()) {
			$this->addSearchLabel();
		}
		if (!$this->checkTimeout()) {
			$this->clear();
		}
	}

	/**
	 * Add labels for records.
	 *
	 * @return void
	 */
	private function addLabels(): void
	{
		$query = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype'])
			->from('vtiger_crmentity')
			->innerJoin('vtiger_tab', 'vtiger_tab.name = vtiger_crmentity.setype')
			->innerJoin('vtiger_entityname', 'vtiger_tab.tabid = vtiger_entityname.tabid')
			->leftJoin('u_#__crmentity_label', 'u_#__crmentity_label.crmid = vtiger_crmentity.crmid')
			->where(['and',
				['vtiger_crmentity.deleted' => 0],
				['vtiger_tab.presence' => 0],
				['not', ['vtiger_entityname.fieldname' => '']],
				['u_#__crmentity_label.label' => null],
			])->limit($this->limit);
		foreach ($query->batch(100) as $rows) {
			$this->updateLastActionTime();
			foreach ($rows as $row) {
				\App\Record::updateLabel($row['setype'], $row['crmid']);
				--$this->limit;
				if ($this->checkTimeout()) {
					break;
				}
			}
		}
	}

	/**
	 * Add search labels for records.
	 *
	 * @return void
	 */
	private function addSearchLabel(): void
	{
		$query = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype'])
			->from('vtiger_crmentity')
			->innerJoin('vtiger_tab', 'vtiger_tab.name = vtiger_crmentity.setype')
			->innerJoin('vtiger_entityname', 'vtiger_tab.tabid = vtiger_entityname.tabid')
			->leftJoin('u_#__crmentity_search_label', 'u_#__crmentity_search_label.crmid = vtiger_crmentity.crmid')
			->where(['and',
				['vtiger_crmentity.deleted' => 0],
				['vtiger_tab.presence' => 0],
				['vtiger_entityname.turn_off' => 1],
				['not', ['vtiger_entityname.searchcolumn' => '']],
				['u_#__crmentity_search_label.searchlabel' => null],
			])
			->limit($this->limit);
		foreach ($query->batch(100) as $rows) {
			$this->updateLastActionTime();
			foreach ($rows as $row) {
				\App\Record::updateLabel($row['setype'], $row['crmid']);
				if ($this->checkTimeout()) {
					break;
				}
			}
		}
	}

	/**
	 * Clear data.
	 */
	private function clear()
	{
		$query = (new App\Db\Query())->select(['crmid'])->from('vtiger_crmentity')
			->leftJoin('vtiger_tab', 'vtiger_tab.name = vtiger_crmentity.setype')
			->leftJoin('vtiger_entityname', 'vtiger_tab.tabid = vtiger_entityname.tabid')
			->where(['or',
				['vtiger_crmentity.deleted' => 1],
				['vtiger_tab.presence' => 1],
				['vtiger_tab.name' => null],
				['vtiger_entityname.searchcolumn' => ''],
				['vtiger_entityname.turn_off' => 0],
			]);
		App\Db::getInstance()->createCommand()->delete('u_#__crmentity_search_label', ['crmid' => $query])->execute();
	}
}
