<?php

/**
 * Synchronize categories for products.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator;

use App\Db\Query;

/**
 * Category class.
 */
class Category extends Base
{
	/**
	 * Map with parents id.
	 *
	 * @var array
	 */
	public $parentsId = [];
	/**
	 * Categories YetiForce.
	 *
	 * @var array
	 */
	public $categoriesYF = [];
	/**
	 * Categories Magento.
	 *
	 * @var array
	 */
	public $categoriesMagento = [];
	/**
	 * Parsed Magento categories (id as array key).
	 *
	 * @var array
	 */
	public $categoriesMagentoParsed = [];
	/**
	 * Category Map (Magento id as key).
	 *
	 * @var array
	 */
	public $mapCategoryYF = [];
	/**
	 * Category Map (YetiForce id as key).
	 *
	 * @var array
	 */
	public $mapCategoryMagento = [];
	/**
	 * Yetiforce category template id.
	 *
	 * @var int
	 */
	public $templateId;

	/**
	 * Last YetiForce category ID.
	 *
	 * @var int
	 */
	public $lastCategoryIdYF;

	/**
	 * Non editable magento categories id.
	 *
	 * @var array
	 */
	private static $nonEditable = [1, 2];
	/**
	 * Global root category.
	 *
	 * @var bool|int
	 */
	public $rootCategory = false;

	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$this->templateId = \Vtiger_Field_Model::getInstance('category_multipicklist', \Vtiger_Module_Model::getInstance('Products'))->getFieldParams();
		$this->getCategoriesYF();
		$this->getCategoriesMagento();
		$this->getCategoryMapping();
		if ('magento' === \App\Config::component('Magento', 'masterSource')) {
			$this->updateCategoriesMagento(true);
			$this->updateCategoriesYF(false, $this->categoriesMagento['children_data']);
		} else {
			$this->updateCategoriesYF(true, $this->categoriesMagento['children_data']);
			$this->updateCategoriesMagento(false);
		}
	}

	/**
	 * Method to add/update in YF parsed Magento categories.
	 *
	 * @param bool  $master
	 * @param array $categoriesMagento
	 *
	 * @throws \yii\db\Exception
	 */
	public function updateCategoriesYF(bool $master, array $categoriesMagento): void
	{
		if (!empty($categoriesMagento)) {
			$categoryData = $categoriesMagento;
			if (false === $this->rootCategory) {
				$this->rootCategory = current($categoriesMagento)['id'];
				$categoryData = current($categoriesMagento)['children_data'];
			}
			foreach ($categoryData as $categoryMagento) {
				$deletedParent = false;
				$this->addParentId($categoryMagento['id'], $categoryMagento['parent_id']);
				if (isset($this->mapCategoryYF[$categoryMagento['id']])) {
					if ($master) {
						if (!isset($this->categoriesYF[$this->mapCategoryYF[$categoryMagento['id']]])) {
							$deletedParent = $this->deleteCategoryMagento($categoryMagento);
						}
					} else {
						$categoryYF = $this->categoriesYF[$this->mapCategoryYF[$categoryMagento['id']]];
						if ($this->hasChanges($categoryYF, $categoryMagento)) {
							$this->updateCategoryYF($categoryYF, $categoryMagento);
						}
					}
				} else {
					if (!$master) {
						$this->saveCategoryYF($categoryMagento);
					} else {
						$deletedParent = $this->deleteCategoryMagento($categoryMagento);
					}
				}
				if (!$deletedParent && !empty($categoryMagento['children_data'])) {
					$this->updateCategoriesYF($master, $categoryMagento['children_data']);
				}
			}
		}
	}

	/**
	 * Method to add/update in Magento parsed YF categories.
	 *
	 * @param bool $master
	 */
	public function updateCategoriesMagento(bool $master = false): void
	{
		foreach ($this->categoriesYF as $categoryYF) {
			if (isset($this->mapCategoryMagento[$categoryYF['id']], $this->categoriesMagentoParsed[$this->mapCategoryMagento[$categoryYF['id']]]) && \in_array($categoryYF['id'], $this->mapCategoryYF, false)) {
				$categoryMagento = $this->categoriesMagentoParsed[$this->mapCategoryMagento[$categoryYF['id']]];
				if ($this->hasChanges($categoryYF, $categoryMagento)) {
					if (!$master) {
						$this->updateCategoryMagento($categoryYF, $categoryMagento);
					}
				}
			} else {
				if ($master) {
					$this->deleteCategoryYF($categoryYF);
				} else {
					$this->saveCategoryMagento($categoryYF);
				}
			}
		}
	}

	/**
	 * Method to compare changes of given two categories.
	 *
	 * @param array $categoryYF
	 * @param array $categoryMagento
	 *
	 * @return bool
	 */
	public function hasChanges(array $categoryYF, array $categoryMagento)
	{
		return $categoryYF['name'] !== $categoryMagento['name'] ||
			(int) $categoryYF['is_active'] !== (int) $categoryMagento['is_active'] ||
			$this->hasChangedCategory($categoryYF, $categoryMagento);
	}

	/**
	 * Method to check if is parent category changed.
	 *
	 * @param array $categoryYF
	 * @param array $categoryMagento
	 *
	 * @return bool
	 */
	public function hasChangedCategory(array $categoryYF, array $categoryMagento)
	{
		return isset($this->mapCategoryMagento[$categoryYF['parent_id']]) && $this->mapCategoryMagento[$categoryYF['parent_id']] !== $categoryMagento['parent_id'];
	}

	/**
	 * Add parent id to array.
	 *
	 * @param int        $recordId
	 * @param int|string $parentId
	 */
	public function addParentId(int $recordId, $parentId): void
	{
		$this->parentsId[$recordId] = $parentId;
	}

	/**
	 * Method to return parent YF ids for record.
	 *
	 * @param int $categoryId
	 *
	 * @return array
	 */
	public function getParentsIds($categoryId)
	{
		$parent = [];
		$recordCategory = $categoryId;
		if (\is_array($this->parentsId) && false !== $this->rootCategory) {
			do {
				if (!empty($this->parentsId[$recordCategory]) && $this->rootCategory !== $this->parentsId[$recordCategory]) {
					$parent[] = $this->mapCategoryYF[$this->parentsId[$recordCategory]];
					$recordCategory = $this->parentsId[$recordCategory];
				} elseif (!empty($this->parentsId[$recordCategory])) {
					$recordCategory = $this->parentsId[$recordCategory];
				}
			} while (!($this->rootCategory === $recordCategory));
		}
		$parent = array_reverse($parent);
		$parent[] = $this->mapCategoryYF[$categoryId];
		return $parent;
	}

	/**
	 * Method to return Magento categories parsed to array.
	 *
	 * @return array
	 */
	public function getCategoriesMagento()
	{
		try {
			$categoriesList = $this->connector->request('GET', '/rest/all/V1/categories');
			$this->categoriesMagento['children_data'] = [\App\Json::decode($categoriesList)];
			$this->parseMagentoCategory($this->categoriesMagento);
		} catch (\Throwable $ex) {
			\App\Log::error('Error while getting categories from magento: ' . $ex->getMessage(), 'Integrations/Magento');
		}

		return $this->categoriesMagento;
	}

	/**
	 * Method to return YF categories parsed to array.
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return array
	 */
	public function getCategoriesYF(): array
	{
		foreach ($this->getTemplatesData() as $categoryYF) {
			$treeID = (int) str_replace('T', '', $categoryYF['tree']);
			$parentTreeID = str_replace($categoryYF['tree'], '', $categoryYF['parentTree']);
			$parentTreeID = array_filter(explode('::', str_replace('T', '', $parentTreeID)));
			$this->categoriesYF[$treeID] = [
				'id' => $treeID,
				'parent_id' => (int) end($parentTreeID),
				'full_parent_id' => implode('::', $parentTreeID),
				'name' => $categoryYF['label'],
				'is_active' => 1,
				'level' => $categoryYF['depth'],
			];
		}
		return $this->categoriesYF;
	}

	/**
	 * Method to parse Magento categories to array with id key.
	 *
	 * @param array $category
	 */
	public function parseMagentoCategory(array $category)
	{
		if (!empty($category['children_data'])) {
			foreach ($category['children_data'] as $childrenCategory) {
				$this->parseMagentoCategory($childrenCategory);
				unset($childrenCategory['children_data']);
				$this->categoriesMagentoParsed[$childrenCategory['id']] = $childrenCategory;
			}
		}
	}

	/**
	 * Method to return last categories tree id.
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function getLastIdYF(): int
	{
		if (empty($this->lastCategoryIdYF)) {
			$this->lastCategoryIdYF = 0;
			foreach ($this->getTemplatesData() as $category) {
				$treeId = (int) str_replace('T', '', $category['tree']);
				if ($treeId >= $this->lastCategoryIdYF) {
					$this->lastCategoryIdYF = $treeId;
				}
			}
		}
		return ++$this->lastCategoryIdYF;
	}

	/**
	 * Get templates data from YetiForce.
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return array
	 */
	public function getTemplatesData()
	{
		return (new Query())
			->where(['templateid' => $this->templateId])
			->from('vtiger_trees_templates_data')
			->createCommand()->queryAll();
	}

	/**
	 * Save category in magento.
	 *
	 * @param array $category
	 *
	 * @return bool
	 */
	public function saveCategoryMagento(array $category): bool
	{
		$result = false;
		try {
			$categoryRequest = \App\Json::decode($this->connector->request('POST', '/rest/V1/categories', [
				'category' => [
					'name' => $category['name'],
					'parent_id' => $this->mapCategoryMagento[$category['parent_id']] ?? 0,
					'is_active' => $category['is_active'],
					'include_in_menu' => '1',
					'isActive' => 'true',
					'level' => $category['level'] + 1,
				]]));
			$this->saveCategoryMappingYF($categoryRequest['id'], $category['id']);
			$result = true;
		} catch (\Throwable $ex) {
			\App\Log::error('Error during saving magento category: ' . $ex->getMessage(), 'Integrations/Magento');
		}
		return $result;
	}

	/**
	 * Save category in YF.
	 *
	 * @param array $category
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function saveCategoryYF(array $category): int
	{
		$categoryId = $this->getLastIdYF();
		$dbCommand = \App\Db::getInstance()->createCommand();
		if (isset($this->mapCategoryYF[$category['parent_id']])) {
			$category['parent_id'] = $this->mapCategoryYF[$category['parent_id']];
		} else {
			$category['parent_id'] = $categoryId;
		}
		$this->saveCategoryMappingYF($category['id'], $categoryId);
		$result = $dbCommand->insert('vtiger_trees_templates_data', [
			'templateid' => $this->templateId,
			'tree' => 'T' . $categoryId,
			'parentTree' => $this->parseParentYF($category),
			'name' => $category['name'],
			'depth' => $category['level'] - 1,
			'label' => $category['name'],
			'state' => '{"loaded":true,"opened":false,"selected":false,"disabled":' . (bool) !$category['is_active'] . '}'
		])->execute();
		$this->getCategoriesYF();
		return $result;
	}

	/**
	 * Method to update Magento category.
	 *
	 * @param array $categoryYF
	 * @param array $categoryMagento
	 *
	 * @return bool
	 */
	public function updateCategoryMagento(array $categoryYF, array $categoryMagento): bool
	{
		if ($this->hasChangedCategory($categoryYF, $categoryMagento)) {
			$this->updateCategoryParentMagento($categoryYF, $categoryMagento);
		}
		try {
			$this->connector->request('PUT', '/rest/all/V1/categories/' . $categoryMagento['id'], [
				'category' => [
					'name' => $categoryYF['name'],
					'isActive' => $categoryYF['is_active'],
				]
			]);
			$result = true;
		} catch (\Throwable $ex) {
			\App\Log::error('Error during updating magento category: ' . $ex->getMessage(), 'Integrations/Magento');
			$result = false;
		}
		return $result;
	}

	/**
	 * Method to update YF category.
	 *
	 * @param array $categoryYF
	 * @param array $categoryMagento
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function updateCategoryYF(array $categoryYF, array $categoryMagento): int
	{
		$parentId = $this->parseParentYF($categoryMagento);
		$dbCommand = \App\Db::getInstance()->createCommand();
		$result = $dbCommand->update('vtiger_trees_templates_data', [
			'parentTree' => $parentId,
			'name' => $categoryMagento['name'],
			'depth' => $categoryMagento['level'],
			'label' => $categoryMagento['name'],
			'state' => '{"loaded":true,"opened":false,"selected":false,"disabled":' . (bool) !$categoryMagento['is_active'] . '}'
		], ['tree' => 'T' . $categoryYF['id'], 'templateid' => $this->templateId])->execute();
		if (!empty($categoryMagento['children_data'])) {
			foreach ($categoryMagento['children_data'] as $categoryChild) {
				$dbCommand->update('vtiger_trees_templates_data', [
					'parentTree' => str_replace('::', '::T', $this->categoriesYF[$this->mapCategoryYF[$categoryChild['parent_id']]]['full_parent_id'] . '::' . $this->mapCategoryYF[$categoryChild['id']])
				], ['tree' => 'T' . $this->mapCategoryYF[$categoryChild['id']], 'templateid' => $this->templateId])->execute();
			}
		}
		$this->getCategoriesYF();
		return $result;
	}

	/**
	 * Parse given array with parents id to YF parent tree DB format.
	 *
	 * @param array $category
	 *
	 * @return string
	 */
	public function parseParentYF(array $category): string
	{
		$parentsId = $this->getParentsIds($category['id']);
		if (!empty($parentsId)) {
			$parentsId[0] = 'T' . $parentsId[0];
			$parentId = implode('::T', $parentsId);
		} else {
			$parentId = 'T' . $category['parent_id'];
		}
		return $parentId;
	}

	/**
	 * Method to delete given Magento category.
	 *
	 * @param array $categoryMagento
	 *
	 * @return bool
	 */
	public function deleteCategoryMagento($categoryMagento): bool
	{
		$result = false;
		if (!\in_array($categoryMagento['id'], static::$nonEditable)) {
			try {
				$this->connector->request('DELETE', '/rest/all/V1/categories/' . $categoryMagento['id'], []);
				$result = true;
			} catch (\Throwable $ex) {
				\App\Log::error('Error during deleting magento category: ' . $ex->getMessage(), 'Integrations/Magento');
			}
		}
		return $result;
	}

	/**
	 * Method to delete category in YF.
	 *
	 * @param array $categoryYF
	 *
	 * @throws \yii\db\Exception
	 */
	public function deleteCategoryYF(array $categoryYF)
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$dbCommand->delete('vtiger_trees_templates_data', ['tree' => 'T' . $categoryYF['id'], 'templateid' => $this->templateId])->execute();
		$dbCommand->delete('i_#__magento_record', ['crmid' => $categoryYF['id']])->execute();
	}

	/**
	 * Method to return category mapping (as key is YF id).
	 */
	public function getCategoryMapping()
	{
		$this->mapCategoryMagento = (new Query())
			->select(['crmid', 'id'])
			->where(['type' => 'category'])
			->from('i_#__magento_record')
			->createCommand()->queryAllByGroup(0) ?? [];
		$this->mapCategoryMagento[0] = 0;
		$this->mapCategoryYF = \array_flip($this->mapCategoryMagento);
	}

	/**
	 * Method to update Magento parent id for given category.
	 *
	 * @param array $categoryYF
	 * @param array $categoryMagento
	 *
	 * @return bool
	 */
	public function updateCategoryParentMagento($categoryYF, $categoryMagento): bool
	{
		try {
			$this->connector->request('PUT', '/rest/all/V1/categories/' . $categoryMagento['id'] . '/move', ['parentId' => $this->mapCategoryMagento[$categoryYF['parent_id']] ?? 0]);
			$result = true;
		} catch (\Throwable $ex) {
			\App\Log::error('Error during moving magento category: ' . $ex->getMessage(), 'Integrations/Magento');
			$result = false;
		}
		return $result;
	}

	/**
	 * Method to save mapping in YF.
	 *
	 * @param int $categoryIdMagento
	 * @param int $categoryIdYF
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function saveCategoryMappingYF(int $categoryIdMagento, int $categoryIdYF): int
	{
		if (isset($this->mapCategoryYF[$categoryIdMagento]) || isset($this->mapCategoryMagento[$categoryIdYF])) {
			$result = $this->updateCategoryMappingYF($categoryIdMagento, $categoryIdYF);
		} else {
			$result = \App\Db::getInstance()->createCommand()->insert('i_#__magento_record', [
				'id' => $categoryIdMagento,
				'crmid' => $categoryIdYF,
				'type' => 'category'
			])->execute();
		}
		$this->mapCategoryMagento[$categoryIdYF] = $categoryIdMagento;
		$this->mapCategoryYF[$categoryIdMagento] = $categoryIdYF;
		return $result;
	}

	/**
	 * Method to update mapping in YF.
	 *
	 * @param $categoryIdMagento
	 * @param $categoryIdYF
	 *
	 * @throws \yii\db\Exception
	 *
	 * @return int
	 */
	public function updateCategoryMappingYF($categoryIdMagento, $categoryIdYF): int
	{
		return \App\Db::getInstance()->createCommand()->update('i_#__magento_record', [
			'id' => $categoryIdMagento
		], ['crmid' => $categoryIdYF])->execute();
	}
}
