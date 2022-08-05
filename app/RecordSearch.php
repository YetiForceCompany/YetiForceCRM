<?php
/**
 * Record search basic class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

class RecordSearch
{
	/** @var string Table name for search mechanisms */
	public const SEARCH_TABLE_NAME = 'u_#__crmentity_search_label';

	/** @var string Table name for labels */
	public const LABEL_TABLE_NAME = 'u_#__crmentity_label';

	/** @var int Search mode for labels */
	public const LABEL_MODE = 0;

	/** @var int Search mode for search engines */
	public const SEARCH_MODE = 1;

	/**
	 * Operators.
	 */
	public const OPERATORS = [
		'PLL_FULLTEXT_BEGIN' => 'FulltextBegin',
		'PLL_FULLTEXT_WORD' => 'FulltextWord',
		'PLL_CONTAINS' => 'Contain',
		'PLL_STARTS_WITH' => 'Begin',
		'PLL_ENDS_WITH' => 'End',
	];

	public $searchValue;
	public $moduleName;
	public $limit;
	public $userId;
	public $entityName = true;
	public $table = self::SEARCH_MODE;
	public $operator = 'Contain';

	public $checkPermissions = true;

	/**
	 * Construct.
	 *
	 * @param mixed $searchValue
	 * @param mixed $moduleName
	 * @param int   $limit
	 */
	public function __construct($searchValue, $moduleName = false, int $limit = null)
	{
		$this->searchValue = $searchValue;
		$this->moduleName = $moduleName;
		if (!$limit) {
			$limit = \App\Config::search('GLOBAL_SEARCH_MODAL_MAX_NUMBER_RESULT', 20);
		}
		$this->limit = (int) $limit;
		$this->userId = \App\User::getCurrentUserId();
	}

	/**
	 * Set search mode.
	 *
	 * @param int $mode
	 *
	 * @return self
	 */
	public function setMode(int $mode): self
	{
		$this->table = $mode;
		return $this;
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
		return $query->all();
	}

	/**
	 * Get query.
	 *
	 * @return Db\Query()|bool
	 */
	public function getQuery()
	{
		switch ($this->table) {
			case self::SEARCH_MODE:
				return $this->getSearchLabelQuery();
			case self::LABEL_MODE:
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
	public function getSearchLabelQuery(): Db\Query
	{
		$query = (new Db\Query())->select(['csl.crmid', 'setype' => 'vtiger_tab.name', 'csl.searchlabel'])
			->from('u_#__crmentity_search_label csl')
			->innerJoin('vtiger_tab', 'csl.tabid = vtiger_tab.tabid')
			->innerJoin('vtiger_entityname', 'vtiger_tab.tabid = vtiger_entityname.tabid')
			->where(['vtiger_tab.presence' => 0])->indexBy('crmid');
		$searchableModules = self::getSearchableModules();
		if ($this->moduleName) {
			$modules = \is_array($this->moduleName) ? $this->moduleName : [$this->moduleName];
			$modules = array_intersect($modules, array_keys($searchableModules));
			if (\count($modules) !== \count($searchableModules)) {
				$query->andWhere(['vtiger_tab.name' => $modules]);
			}
		} else {
			$modules = array_keys($searchableModules);
		}
		$where = ['and'];
		if (2 === \App\Config::search('GLOBAL_SEARCH_SORTING_RESULTS')) {
			$query->orderBy('vtiger_entityname.sequence');
		}
		foreach ($modules as $moduleName) {
			$moduleModel = $searchableModules[$moduleName];
			if (method_exists($moduleModel, 'searchRecordCondition')) {
				$moduleModel->searchRecordCondition($query, $this);
			}
		}

		if ($this->checkPermissions) {
			$where[] = ['like', 'csl.userid', ",{$this->userId},"];
		}
		switch ($this->operator) {
			case 'Begin':
				$where[] = ['like', 'csl.searchlabel', "{$this->searchValue}%", false];
				break;
			case 'End':
				$where[] = ['like', 'csl.searchlabel', "%{$this->searchValue}", false];
				break;
			case 'FulltextBegin':
				if ($word = $this->parseWordForFullText($this->searchValue)) {
					$query->andWhere('MATCH(csl.searchlabel) AGAINST(:findvalue IN BOOLEAN MODE)', [':findvalue' => $word . '*']);
				} else {
					$query->andWhere(new \yii\db\Expression('1=0'));
				}
				$query->addSelect(['matcher' => new \yii\db\Expression('MATCH(csl.searchlabel) AGAINST(:searchValue IN BOOLEAN MODE)', [':searchValue' => $word . '*'])]);
				$query->addOrderBy(['matcher' => SORT_DESC]);
				break;
			case 'FulltextWord':
				$words = [$this->searchValue];
				if (preg_match('/[><()~*"@+-]/', $this->searchValue)) {
					$words = preg_split('/\s/', $this->searchValue, -1, PREG_SPLIT_NO_EMPTY);
					foreach ($words as $key => $word) {
						$words[$key] = $this->parseWordForFullText($word);
					}
				}
				$conditions = ['or'];
				$matcher = $params = [];
				foreach ($words as $key => $word) {
					$matcher[] = "MATCH(csl.searchlabel) AGAINST(:searchValue{$key} IN BOOLEAN MODE)";
					$params[":searchValue{$key}"] = $word;
					$conditions[] = new \yii\db\Expression("MATCH(csl.searchlabel) AGAINST(:findvalue{$key} IN BOOLEAN MODE)", [":findvalue{$key}" => $word]);
				}
				$query->addSelect(['matcher' => new \yii\db\Expression(implode('+', $matcher), $params)]);
				$query->andWhere($conditions);
				$query->addOrderBy(['matcher' => SORT_DESC]);
				break;
			default:
				if (false !== strpos($this->searchValue, '*') || false !== strpos($this->searchValue, '_')) {
					$where[] = ['like', 'csl.searchlabel', str_replace('*', '%', "%{$this->searchValue}%"), false];
				} else {
					$where[] = ['like', 'csl.searchlabel', $this->searchValue];
				}
				break;
		}
		return $query->andWhere($where);
	}

	/**
	 * Parse text for Full-Text Search.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function parseWordForFullText(string $text): string
	{
		if (($word = preg_replace('/[><()~*"@+-]/', ' +', $text)) !== $text) {
			$words = preg_split('/\s|\+/', $word, -1, PREG_SPLIT_NO_EMPTY);
			$word = $words ? '+' . implode(' +', $words) : '';
		}
		return $word;
	}

	/**
	 * Get label query.
	 *
	 * @return Db\Query()
	 */
	public function getLabelQuery(): Db\Query
	{
		$query = (new \App\Db\Query())->select(['cl.crmid', 'cl.label', 'vtiger_crmentity.setype'])
			->from('u_#__crmentity_label cl')->innerJoin('vtiger_crmentity', 'cl.crmid = vtiger_crmentity.crmid');
		$where = ['and'];
		if ($this->moduleName) {
			$where[] = ['vtiger_crmentity.setype' => $this->moduleName];
		}
		switch ($this->operator) {
			case 'Begin':
				$where[] = ['like', 'cl.label', "{$this->searchValue}%", false];
				break;
			case 'End':
				$where[] = ['like', 'cl.label', "%{$this->searchValue}", false];
				break;
			default:
			case 'Contain':
				$where[] = ['like', 'cl.label', $this->searchValue];
				break;
			case 'FulltextBegin':
				$query->addSelect(['matcher' => new \yii\db\Expression('MATCH(cl.label) AGAINST(:searchValue IN BOOLEAN MODE)', [':searchValue' => $this->searchValue . '*'])]);
				$query->andWhere('MATCH(cl.label) AGAINST(:findvalue IN BOOLEAN MODE)', [':findvalue' => $this->searchValue . '*']);
				$query->addOrderBy(['matcher' => SORT_DESC]);
				break;
			case 'FulltextWord':
				$query->addSelect(['matcher' => new \yii\db\Expression('MATCH(cl.label) AGAINST(:searchValue IN BOOLEAN MODE)', [':searchValue' => $this->searchValue])]);
				$query->andWhere('MATCH(cl.label) AGAINST(:findvalue IN BOOLEAN MODE)', [':findvalue' => $this->searchValue]);
				$query->addOrderBy(['matcher' => SORT_DESC]);
				break;
		}
		if ($this->checkPermissions) {
			$where[] = ['like', 'vtiger_crmentity.users', ",$this->userId,"];
		}
		return $query->andWhere($where);
	}

	/**
	 * Get field model for search mechanism.
	 *
	 * @return \Vtiger_Field_Model
	 */
	public static function getSearchField(): \Vtiger_Field_Model
	{
		return (new \Vtiger_Field_Model())->set('name', 'search')->set('uitype', 1)->set('typeofdata', 'V~O')->set('maximumlength', 255);
	}

	/**
	 * Function to get the list of records matching the search key.
	 * Used in the global search engine.
	 *
	 * @param string          $searchKey
	 * @param string|string[] $module
	 * @param int             $limit
	 * @param string          $operator
	 *
	 * @return array
	 */
	public static function getSearchResult($searchKey, $module = null, int $limit = null, string $operator = null): array
	{
		$matchingRecords = [];
		$recordSearch = new self($searchKey, $module, $limit);
		if ($operator) {
			$recordSearch->operator = $operator;
		}
		$rows = $recordSearch->search();
		$labels = \App\Record::getLabel(array_keys($rows));
		foreach ($rows as $row) {
			$recordMeta = \vtlib\Functions::getCRMRecordMetadata($row['crmid']);
			$row['id'] = $row['crmid'];
			$row['label'] = \App\Purifier::decodeHtml($labels[$row['crmid']]);
			$row['assigned_user_id'] = $recordMeta['smownerid'];
			$row['createdtime'] = $recordMeta['createdtime'];
			$row['permitted'] = \App\Privilege::isPermitted($row['setype'], 'DetailView', $row['crmid']);
			$moduleName = $row['setype'];
			$recordInstance = \Vtiger_Record_Model::getCleanInstance($moduleName);
			$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row);
		}
		return $matchingRecords;
	}

	/**
	 * The function creates a query gets data from the module with user rights, taking into account special tables.
	 * Mainly used in autocomplete mechanisms.
	 *
	 * @param string $searchValue
	 * @param string $moduleName
	 * @param int    $limit
	 * @param int    $userId
	 *
	 * @return QueryGenerator
	 */
	public static function getQueryByModule(string $searchValue, string $moduleName, int $limit, int $userId = null): QueryGenerator
	{
		if (isset(self::getSearchableModules()[$moduleName])) {
			$searchTableName = self::SEARCH_TABLE_NAME;
			$searchColumnName = "{$searchTableName}.searchlabel";
		} else {
			$searchTableName = self::LABEL_TABLE_NAME;
			$searchColumnName = "{$searchTableName}.label";
		}
		$queryGenerator = new \App\QueryGenerator($moduleName, $userId);
		$queryGenerator->setFields(['id'])
			->setCustomColumn(['search_label' => $searchColumnName])
			->addJoin(['INNER JOIN', $searchTableName, "{$queryGenerator->getColumnName('id')} = {$searchTableName}.crmid"])
			->addNativeCondition(['like', $searchColumnName, $searchValue])
			->setLimit($limit);

		return $queryGenerator;
	}

	/**
	 * Function to get the list of all searchable modules.
	 *
	 * @param int|null $userId
	 *
	 * @return Vtiger_Module_Model[] List of Vtiger_Module_Model instances
	 */
	public static function getSearchableModules(int $userId = null): array
	{
		$userId = $userId ?: \App\User::getCurrentUserId();
		if (Cache::staticHas('getSearchableModules', $userId)) {
			return Cache::staticGet('getSearchableModules', $userId);
		}
		$searchableModules = [];
		$userPrivModel = \Users_Privileges_Model::getInstanceById($userId);
		$dataReader = (new \App\Db\Query())->select(['vtiger_tab.name'])->from('vtiger_entityname')
			->innerJoin('vtiger_tab', 'vtiger_tab.tabid=vtiger_entityname.tabid')
			->where(['vtiger_tab.presence' => 0, 'turn_off' => 1])
			->andWhere(['not', ['vtiger_tab.name' => 'Users']])->createCommand()->query();
		while ($moduleName = $dataReader->readColumn(0)) {
			if ($userPrivModel->hasModuleActionPermission($moduleName, 'DetailView')) {
				$searchableModules[$moduleName] = \Vtiger_Module_Model::getInstance($moduleName);
			}
		}
		Cache::staticSave('getSearchableModules', $userId, $searchableModules);
		return $searchableModules;
	}
}
