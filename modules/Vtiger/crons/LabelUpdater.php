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
		$this->newEntries();
		$this->blankEntries();
		$this->updateEntries();
	}

	/**
	 * Generate labels for new entries.
	 *
	 * @return void
	 */
	public function newEntries(): void
	{
		$query = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype',
			'u_#__crmentity_label.label', 'u_#__crmentity_search_label.searchlabel', ])
			->from('vtiger_crmentity')
			->innerJoin('vtiger_tab', 'vtiger_tab.name = vtiger_crmentity.setype')
			->leftJoin('u_#__crmentity_label', ' u_#__crmentity_label.crmid = vtiger_crmentity.crmid')
			->leftJoin('u_#__crmentity_search_label', 'u_#__crmentity_search_label.crmid = vtiger_crmentity.crmid')
			->where(['and',
				['vtiger_crmentity.deleted' => 0],
				['or',
					['u_#__crmentity_label.label' => null],
					['u_#__crmentity_search_label.searchlabel' => null]
				],
				['vtiger_tab.presence' => 0]
			])
			->limit($this->limit);
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
				if (0 === $this->limit) {
					return;
				}
			}
			if ($this->checkTimeout()) {
				return;
			}
		}
	}

	/**
	 * Generate labels for blank entries.
	 *
	 * @return void
	 */
	public function blankEntries(): void
	{
		$query = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.setype'])
			->from('vtiger_crmentity')
			->innerJoin('vtiger_tab', 'vtiger_tab.name = vtiger_crmentity.setype')
			->leftJoin('u_#__crmentity_label', ' u_#__crmentity_label.crmid = vtiger_crmentity.crmid')
			->leftJoin('u_#__crmentity_search_label', 'u_#__crmentity_search_label.crmid = vtiger_crmentity.crmid')
			->where(['and',
				['vtiger_crmentity.deleted' => 0],
				['or',
					['u_#__crmentity_label.label' => ''],
					['u_#__crmentity_search_label.searchlabel' => '']
				],
				['vtiger_tab.presence' => 0]
			])
			->limit($this->limit);
		foreach ($query->batch(100) as $rows) {
			foreach ($rows as $row) {
				\App\Record::updateLabel($row['setype'], $row['crmid']);
				--$this->limit;
				if (0 === $this->limit) {
					return;
				}
			}
			if ($this->checkTimeout()) {
				return;
			}
		}
	}

	/**
	 * Generate labels for update entries.
	 *
	 * @return void
	 */
	public function updateEntries(): void
	{
		$query = (new App\Db\Query())->select(['vtiger_crmentity.crmid', 'u_#__crmentity_label.label', 'u_#__crmentity_search_label.searchlabel'])
			->from('vtiger_crmentity')
			->leftJoin('u_#__crmentity_label', ' u_#__crmentity_label.crmid = vtiger_crmentity.crmid')
			->leftJoin('u_#__crmentity_search_label', 'u_#__crmentity_search_label.crmid = vtiger_crmentity.crmid')
			->where(['and',
				['vtiger_crmentity.deleted' => 1],
				['or',
					['not', ['u_#__crmentity_label.label' => null]],
					['not', ['u_#__crmentity_search_label.searchlabel' => null]]
				]
			])
			->limit($this->limit);
		foreach ($query->batch(100) as $rows) {
			foreach ($rows as $row) {
				$db = App\Db::getInstance();
				if (null !== $row['label']) {
					$db->createCommand()->delete('u_#__crmentity_label', ['crmid' => $row['crmid']])->execute();
				}
				if (null !== $row['searchlabel']) {
					$db->createCommand()->delete('u_#__crmentity_search_label', ['crmid' => $row['crmid']])->execute();
				}
				if (0 === $this->limit) {
					return;
				}
			}
			if ($this->checkTimeout()) {
				return;
			}
		}
	}
}
