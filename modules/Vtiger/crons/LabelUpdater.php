<?php
/**
 * Label updater cron.
 *
 * @package   Cron
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
		if (!$this->newEntries()) {
			return;
		}
		$this->updateEntries();
	}

	/**
	 * Generate labels for new entries.
	 *
	 * @return bool
	 */
	public function newEntries(): bool
	{
		$query = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype',
			'u_#__crmentity_label.label', 'u_#__crmentity_search_label.searchlabel', ])
			->from('vtiger_crmentity')
			->leftJoin('u_#__crmentity_label', ' u_#__crmentity_label.crmid = vtiger_crmentity.crmid')
			->leftJoin('u_#__crmentity_search_label', 'u_#__crmentity_search_label.crmid = vtiger_crmentity.crmid')
			->where(['and',
				['vtiger_crmentity.deleted' => 0],
				['or',
					['u_#__crmentity_label.label' => null],
					['u_#__crmentity_search_label.searchlabel' => null]
				],
			])
			->limit($this->limit);
		$skipModules = (new App\Db\Query())->select(['vtiger_tab.name'])->from('vtiger_tab')
			->leftJoin('vtiger_entityname', 'vtiger_tab.tabid = vtiger_entityname.tabid')
			->where(['vtiger_tab.isentitytype' => 1])
			->andWhere([
				'or',
				['vtiger_tab.presence' => 1],
				['vtiger_entityname.modulename' => null],
				['vtiger_entityname.fieldname' => '', 'vtiger_entityname.searchcolumn' => ''],
			])
			->column();
		if ($skipModules) {
			$query->andWhere(['not in', 'vtiger_crmentity.setype', $skipModules]);
		}
		foreach ($query->batch(100) as $rows) {
			foreach ($rows as $row) {
				$updater = false;
				if (null === $row['label'] && null !== $row['searchlabel']) {
					$updater = 'label';
				} elseif (null === $row['searchlabel'] && null !== $row['label']) {
					$updater = 'searchlabel';
				}
				\App\Record::updateLabel($row['setype'], $row['crmid'], true, $updater);
				--$this->limit;
			}
			if ($this->checkTimeout()) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Generate labels for update entries.
	 *
	 * @return bool
	 */
	public function updateEntries(): bool
	{
		$query = (new App\Db\Query())->select(['vtiger_crmentity.crmid'])
			->from('vtiger_crmentity')
			->leftJoin('u_#__crmentity_search_label', 'u_#__crmentity_search_label.crmid = vtiger_crmentity.crmid')
			->where(['and',
				['vtiger_crmentity.deleted' => 1],
				['not', ['u_#__crmentity_search_label.searchlabel' => null]]
			])
			->limit($this->limit);
		$createCommand = App\Db::getInstance()->createCommand();
		foreach ($query->batch(50) as $rows) {
			$createCommand->delete('u_#__crmentity_search_label', ['crmid' => array_column($rows, 'crmid')])->execute();
			if ($this->checkTimeout()) {
				return false;
			}
		}
		return true;
	}
}
