<?php
/**
 * Model of tree.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Class tree model for module knowledge base.
 */
class KnowledgeBase_KnowledgeBase_Model extends \App\Base
{
	/**
	 * Get instance.
	 *
	 * @param string $moduleName
	 *
	 * @return \self
	 */
	public static function getInstance($moduleName)
	{
		return new static(['moduleName' => $moduleName]);
	}

	/**
	 * Get folders.
	 *
	 * @return array
	 */
	public function getCategories()
	{
		return App\Fields\Tree::getValuesById($this->getTemplate());
	}

	/**
	 * Get categories by arent.
	 *
	 * @return void
	 */
	public function getCategoriesByParent()
	{
		if (App\Cache::has(static::class, 'CategoriesByParent')) {
			return App\Cache::get(static::class, 'CategoriesByParent');
		}
		$categories = [[]];
		foreach ($this->getCategories() as $row) {
			$parent = App\Fields\Tree::getParentIdx($row);
			$categories[$parent][] = $row['tree'];
		}
		App\Cache::save(static::class, 'CategoriesByParent', $categories);
		return $categories;
	}

	/**
	 * Get template.
	 *
	 * @return array
	 */
	public function getTemplate()
	{
		return $this->getTreeField()['fieldparams'];
	}

	/**
	 * Get tree field.
	 *
	 * @return array
	 */
	public function getTreeField()
	{
		if (App\Cache::has(static::class, 'TreeField')) {
			return App\Cache::get(static::class, 'TreeField');
		}
		$field = (new \App\Db\Query())->select(['tablename', 'columnname', 'fieldname', 'fieldlabel', 'fieldparams'])->from('vtiger_field')->where(['uitype' => 302, 'tabid' => \App\Module::getModuleId($this->get('moduleName'))])->one();
		App\Cache::save(static::class, 'TreeField', $field);
		return $field;
	}

	/**
	 * Get data form ajax.
	 *
	 * @return void
	 */
	public function getData()
	{
		$allCategories = $this->getCategoriesByParent();
		$categories = $this->isEmpty('parentCategory') ? $allCategories[0] : $allCategories[$this->get('parentCategory')] ?? [];
		$featured = [];
		if ($categories) {
			foreach ($this->getFeaturedRecords($categories) as $row) {
				$featured[$row['category']][] = $row;
			}
		}
		return [
			'showAccounts' => 'Faq' === $this->get('moduleName'),
			'categories' => $categories,
			'featured' => $featured,
			'records' => $this->getRecordsByParentCategory(),
		];
	}

	/**
	 * Get featured records.
	 *
	 * @param string[] $categories
	 *
	 * @return array
	 */
	public function getFeaturedRecords(array $categories): array
	{
		$queryGenerator = new App\QueryGenerator('KnowledgeBase');
		$queryGenerator->setFields(['id', 'category', 'subject']);
		$queryGenerator->addNativeCondition(['knowledgebase_status' => 'PLL_ACCEPTED']);
		$queryGenerator->addNativeCondition(['category' => $categories]);
		$queryGenerator->addNativeCondition(['featured' => 1]);
		if ($this->has('filterField') && $this->has('filterValue')) {
			$queryGenerator->addNativeCondition([$this->get('filterField') => $this->get('filterValue')]);
		}
		$queryGenerator->setLimit(50);
		return $queryGenerator->createQuery()->all();
	}

	/**
	 * Get record list query.
	 *
	 * @return App\Db\Query
	 */
	public function getListQuery(): App\Db\Query
	{
		$queryGenerator = new App\QueryGenerator('KnowledgeBase');
		$queryGenerator->setFields(['id', 'assigned_user_id', 'subject', 'introduction', 'modifiedtime', 'category']);
		$queryGenerator->addNativeCondition(['knowledgebase_status' => 'PLL_ACCEPTED']);
		if ($this->has('parentCategory')) {
			$queryGenerator->addNativeCondition(['category' => $this->get('parentCategory')]);
		}
		if ($this->has('filterField') && $this->has('filterValue')) {
			$queryGenerator->addNativeCondition([$this->get('filterField') => $this->get('filterValue')]);
		}
		$queryGenerator->setLimit(Config\Modules\KnowledgeBase::$knowledgeBaseArticleLimit);
		return $queryGenerator->createQuery();
	}

	/**
	 * Parse record list for display.
	 *
	 * @param yii\db\DataReader $dataReader
	 *
	 * @return array
	 */
	public function parseForDisplay(yii\db\DataReader $dataReader): array
	{
		$rows = [];
		while ($row = $dataReader->read()) {
			$introduction = $row['introduction'] ?? '';
			$rows[$row['id']] = [
				'assigned_user_id' => App\Fields\Owner::getLabel($row['assigned_user_id']),
				'subject' => $row['subject'],
				'introduction' => $introduction ? nl2br(\App\Utils\Completions::decode(\App\Purifier::purifyHtml($introduction))) : '',
				'category' => $row['category'],
				'full_time' => App\Fields\DateTime::formatToDisplay($row['modifiedtime']),
				'short_time' => \Vtiger_Util_Helper::formatDateDiffInStrings($row['modifiedtime']),
			];
		}
		$dataReader->close();
		return $rows;
	}

	/**
	 * Get records by parent category.
	 *
	 * @return array
	 */
	public function getRecordsByParentCategory(): array
	{
		if ($this->isEmpty('parentCategory') && !($this->has('filterField') && $this->has('filterValue'))) {
			return [];
		}
		return $this->parseForDisplay($this->getListQuery()->createCommand()->query());
	}

	/**
	 * Article search.
	 *
	 * @return array
	 */
	public function search(): array
	{
		$query = $this->getListQuery();
		$value = $this->get('value');
		$query->addSelect(['matcher' => new \yii\db\Expression('MATCH(subject,content,introduction) AGAINST(:searchValue IN BOOLEAN MODE)', [':searchValue' => $value])]);
		$query->andWhere('MATCH(subject,content,introduction) AGAINST(:findvalue IN BOOLEAN MODE)', [':findvalue' => $value]);
		$query->addOrderBy(['matcher' => \SORT_DESC]);
		$dataReader = $query->createCommand()->query();
		return $this->parseForDisplay($dataReader);
	}
}
