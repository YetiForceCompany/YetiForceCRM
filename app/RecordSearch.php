<?php

namespace App;

/**
 * Record search basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class RecordSearch
{
	public $searchValue;
	public $moduleName;
	public $limit;
	public $userId;
	public $entityName = true;
	public $table = 'searchLabel'; //searchLabel, label
	public $operator = 'Contain'; // Contain, Begin, End, FulltextBegin, FulltextWord
	public $checkPermissions = true;
	private $moduleConditions = ['Leads' => ['where' => ['vtiger_leaddetails.converted' => 0], 'innerJoin' => ['vtiger_leaddetails' => 'csl.crmid = vtiger_leaddetails.leadid']]];

	/**
	 * Construct.
	 *
	 * @param mixed $searchValue
	 * @param mixed $moduleName
	 * @param mixed $limit
	 */
	public function __construct($searchValue, $moduleName = false, $limit = false)
	{
		$this->searchValue = $searchValue;
		$this->moduleName = $moduleName;
		if (!$limit) {
			$limit = 20;
		}
		$this->limit = (int) $limit;
		$this->userId = \App\User::getCurrentUserId();
	}

	/**
	 * Search record.
	 *
	 * @return array
	 */
	public function search()
	{
		$query = $this->getQuery();
		if ($this->limit) {
			$query->limit($this->limit);
		}
		return 1 === $this->limit ? $query->one() : $query->all();
	}

	/**
	 * Get query.
	 *
	 * @return Db\Query()|bool
	 */
	public function getQuery()
	{
		switch ($this->table) {
			case 'searchLabel':
				return $this->getSearchLabelQuery();
			case 'label':
				return $this->getLabelQuery();
			default:
				return false;
		}
	}

	/**
	 * Get search label query.
	 *
	 * @return Db\Query()
	 */
	public function getSearchLabelQuery()
	{
		$query = (new Db\Query())->select(['csl.crmid', 'csl.setype', 'csl.searchlabel'])
			->from('u_#__crmentity_search_label csl')->innerJoin('vtiger_tab', 'csl.setype = vtiger_tab.name');
		$where = ['and', ['vtiger_tab.presence' => 0]];
		if ($this->moduleName) {
			$where[] = ['csl.setype' => $this->moduleName];
			if (\is_string($this->moduleName) && isset($this->moduleConditions[$this->moduleName])) {
				$where[] = $this->moduleConditions[$this->moduleName]['where'];
				if (isset($this->moduleConditions[$this->moduleName]['innerJoin'])) {
					foreach ($this->moduleConditions[$this->moduleName]['innerJoin'] as $table => $on) {
						$query->innerJoin($table, $on);
					}
				}
			}
		} elseif ($this->entityName) {
			$where[] = ['vtiger_entityname.turn_off' => 1];
			$query->innerJoin('vtiger_entityname', 'csl.setype = vtiger_entityname.modulename');
			if (2 === \App\Config::search('GLOBAL_SEARCH_SORTING_RESULTS')) {
				$query->orderBy('vtiger_entityname.sequence');
			}
		}
		if ($this->checkPermissions) {
			$where[] = ['like', 'csl.userid', ",$this->userId,"];
		}
		switch ($this->operator) {
			case 'Begin':
				$where[] = ['like', 'csl.searchlabel', "$this->searchValue%", false];
				break;
			case 'End':
				$where[] = ['like', 'csl.searchlabel', "%$this->searchValue", false];
				break;
			default:
			case 'Contain':
				if (false !== strpos($this->searchValue, '*') || false !== strpos($this->searchValue, '_')) {
					$where[] = ['like', 'csl.searchlabel', str_replace('*', '%', "%{$this->searchValue}%"), false];
				} else {
					$where[] = ['like', 'csl.searchlabel', $this->searchValue];
				}
				break;
			case 'FulltextBegin':
				$query->addSelect(['matcher' => new \yii\db\Expression('MATCH(csl.searchlabel) AGAINST(:searchValue IN BOOLEAN MODE)', [':searchValue' => $this->searchValue . '*'])]);
				$query->andWhere('MATCH(csl.searchlabel) AGAINST(:findvalue IN BOOLEAN MODE)', [':findvalue' => $this->searchValue . '*']);
				$query->addOrderBy(['matcher' => \SORT_DESC]);
				break;
			case 'FulltextWord':
				$query->addSelect(['matcher' => new \yii\db\Expression('MATCH(csl.searchlabel) AGAINST(:searchValue IN BOOLEAN MODE)', [':searchValue' => $this->searchValue])]);
				$query->andWhere('MATCH(csl.searchlabel) AGAINST(:findvalue IN BOOLEAN MODE)', [':findvalue' => $this->searchValue]);
				$query->addOrderBy(['matcher' => \SORT_DESC]);
				break;
		}
		return $query->andWhere($where);
	}

	/**
	 * Get label query.
	 *
	 * @return Db\Query()
	 */
	public function getLabelQuery()
	{
		$query = (new \App\Db\Query())->select(['cl.crmid', 'cl.label'])
			->from('u_#__crmentity_label cl')->innerJoin('vtiger_crmentity', 'cl.crmid = vtiger_crmentity.crmid');
		if ($this->moduleName) {
			$where[] = ['vtiger_crmentity.setype' => $this->moduleName];
		}
		switch ($this->operator) {
			case 'Begin':
				$where[] = ['like', 'cl.label', "$this->searchValue%", false];
				break;
			case 'End':
				$where[] = ['like', 'cl.label', "%$this->searchValue", false];
				break;
			default:
			case 'Contain':
				$where[] = ['like', 'cl.label', $this->searchValue];
				break;
			case 'FulltextBegin':
				$query->addSelect(['matcher' => new \yii\db\Expression('MATCH(cl.label) AGAINST(:searchValue IN BOOLEAN MODE)', [':searchValue' => $this->searchValue . '*'])]);
				$query->andWhere('MATCH(cl.label) AGAINST(:findvalue IN BOOLEAN MODE)', [':findvalue' => $this->searchValue . '*']);
				$query->addOrderBy(['matcher' => \SORT_DESC]);
				break;
			case 'FulltextWord':
				$query->addSelect(['matcher' => new \yii\db\Expression('MATCH(cl.label) AGAINST(:searchValue IN BOOLEAN MODE)', [':searchValue' => $this->searchValue])]);
				$query->andWhere('MATCH(cl.label) AGAINST(:findvalue IN BOOLEAN MODE)', [':findvalue' => $this->searchValue]);
				$query->addOrderBy(['matcher' => \SORT_DESC]);
				break;
		}
		if ($this->checkPermissions) {
			$where[] = ['like', 'vtiger_crmentity.users', ",$this->userId,"];
		}
		return $query->andWhere($where);
	}
}
