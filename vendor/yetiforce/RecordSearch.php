<?php
namespace App;

/**
 * Record search basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class RecordSearch
{

	public $searchValue;
	public $moduleName;
	public $limit;
	public $userId;
	public $useCache = true;
	public $entityName = true;
	public $table = 'searchLabel'; //searchLabel, label
	public $operator = 'contains'; // contains,begin,ends,fulltext 
	public $checkPermissions = true;

	/**
	 * Construct
	 */
	public function __construct($searchValue, $moduleName = false, $limit = false)
	{
		$this->searchValue = $searchValue;
		$this->moduleName = $moduleName;
		if (!$limit) {
			$limit = 20;
		}
		$this->limit = $limit;
		$this->userId = \App\User::getCurrentUserId();
	}

	/**
	 * Search record
	 * @return array
	 */
	public function search()
	{
		$cacheKey = "$this->searchValue,$this->moduleName,$this->limit";
		if ($this->useCache && Cache::has('RecordSearch', $cacheKey)) {
			return Cache::get('RecordSearch', $cacheKey);
		}
		$query = $this->getQuery();
		if ($this->limit) {
			$query->limit($this->limit);
		}
		$crmIds = $this->limit === 1 ? $query->one() : $query->all();
		if ($this->useCache) {
			Cache::save('RecordSearch', $cacheKey, $crmIds, Cache::LONG);
		}
		return $crmIds;
	}

	/**
	 * Get query
	 * @return Db\Query()|boolean
	 */
	public function getQuery()
	{
		switch ($this->table) {
			case 'searchLabel': return $this->getSearchLabelQuery();
			case 'label': return $this->getLabelQuery();
		}
		return false;
	}

	/**
	 * Get search label query
	 * @return Db\Query()
	 */
	public function getSearchLabelQuery()
	{
		$query = (new Db\Query())->select(['csl.crmid', 'csl.setype', 'csl.searchlabel'])
				->from('u_#__crmentity_search_label csl')->innerJoin('vtiger_tab', 'csl.setype = vtiger_tab.name');
		$where = ['and', ['vtiger_tab.presence' => 0]];
		if ($this->checkPermissions) {
			$where[] = ['like', 'csl.userid', ",$this->userId,"];
		}
		switch ($this->operator) {
			case 'begin': $where[] = ['like', 'csl.searchlabel', "$this->searchValue%", false];
				break;
			case 'ends': $where[] = ['like', 'csl.searchlabel', "%$this->searchValue", false];
				break;
			default:
			case 'contains': $where[] = ['like', 'csl.searchlabel', $this->searchValue];
				break;
		}
		if ($this->moduleName) {
			$where[] = ['csl.setype' => $this->moduleName];
		} elseif ($this->entityName) {
			$where[] = ['vtiger_entityname.turn_off' => 1];
			$query->innerJoin('vtiger_entityname', 'csl.setype = vtiger_entityname.modulename');
			if (\AppConfig::search('GLOBAL_SEARCH_SORTING_RESULTS') === 2) {
				$query->orderBy('vtiger_entityname.sequence');
			}
		}
		return $query->where($where);
	}

	/**
	 * Get label query
	 * @return Db\Query()
	 */
	public function getLabelQuery()
	{
		$query = (new \App\Db\Query())->select(['cl.crmid', 'cl.label'])
				->from('u_#__crmentity_label cl')->innerJoin('vtiger_crmentity', 'cl.crmid = vtiger_crmentity.crmid');
		switch ($this->operator) {
			case 'contains': $where[] = ['like', 'cl.label', $this->searchValue];
				break;
			case 'begin': $where[] = ['like', 'cl.label', "$this->searchValue%", false];
				break;
			case 'ends': $where[] = ['like', 'cl.label', "%$this->searchValue", false];
				break;
		}
		if ($this->moduleName) {
			$where[] = ['vtiger_crmentity.setype' => $this->moduleName];
		}
		if ($this->checkPermissions) {
			$where[] = ['like', 'vtiger_crmentity.users', ",$this->userId,"];
		}
		return $query->where($where);
	}
}
