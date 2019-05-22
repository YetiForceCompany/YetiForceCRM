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
	public $parentsId = [];
	public $categoriesYF = [];
	public $categoriesMagento = [];
	public $categoriesMagentoParsed = [];
	public $mapCategoryYF = [];
	public $mapCategoryMagento = [];

	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$this->getCategoriesYF();
		$this->getCategoriesMagento();
		$this->getCategoryMappingYF();
		$this->getCategoryMappingMagento();
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
	 * @param bool     $master
	 * @param array    $categoriesMagento
	 * @param bool|int $globalParentId
	 */
	public function updateCategoriesYF(bool $master, array $categoriesMagento, $globalParentId = false): void
	{
		if (!empty($categoriesMagento)) {
			$rootCategory = $globalParentId;
			$categoryData = $categoriesMagento;
			if (false === $globalParentId) {
				$rootCategory = current($categoriesMagento)['id'];
				$categoryData = current($categoriesMagento)['children_data'];
			}
			foreach ($categoryData as $categoryMagento) {
				$deletedParent = false;
				if (!empty($categoryMagento['parent_id']) && $rootCategory !== $categoryMagento['parent_id'] && isset($this->mapCategoryYF[$categoryMagento['parent_id']])) {
					$this->addParentId($categoryMagento['id'], $categoryMagento['parent_id']);
				} elseif (false !== $rootCategory && $rootCategory === $categoryMagento['parent_id']) {
					$this->addParentId($categoryMagento['id'], 'root');
				}
				if (isset($this->mapCategoryYF[$categoryMagento['id']])) {
					if ($master) {
						if (!isset($this->categoriesYF[$this->mapCategoryYF[$categoryMagento['id']]])) {
							$deletedParent = $this->deleteCategoryMagento($categoryMagento);
						}
					} else {
						if (isset($this->categoriesYF[$this->mapCategoryYF[$categoryMagento['id']]])) {
							$categoryYF = $this->categoriesYF[$this->mapCategoryYF[$categoryMagento['id']]];
							if ($this->hasChanges($categoryYF, $categoryMagento)) {
								$this->updateCategoryYF($categoryYF, $categoryMagento);
							}
						} else {
							$this->saveCategoryYF($categoryMagento);
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
					$this->updateCategoriesYF($master, $categoryMagento['children_data'], $rootCategory);
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
		$changed = false;
		if (
			$categoryYF['name'] !== $categoryMagento['name'] ||
			(int) $categoryYF['is_active'] !== (int) $categoryMagento['is_active'] ||
			$this->hasChangedCategory($categoryYF, $categoryMagento)
		) {
			$changed = true;
		}
		return $changed;
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
		$changed = false;
		if (1 !== $categoryMagento['parent_id'] && isset($this->mapCategoryMagento[$categoryYF['parent_id']]) && $this->mapCategoryMagento[$categoryYF['parent_id']] !== $categoryMagento['parent_id']) {
			$changed = true;
		}
		return $changed;
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
		if (\is_array($this->parentsId)) {
			do {
				if (!empty($this->parentsId[$recordCategory]) && 'root' !== $this->parentsId[$recordCategory]) {
					$parent[] = $this->mapCategoryYF[$this->parentsId[$recordCategory]];
					$recordCategory = $this->parentsId[$recordCategory];
				} elseif (!empty($this->parentsId[$recordCategory])) {
					$recordCategory = $this->parentsId[$recordCategory];
				}
			} while (!('root' === $recordCategory || 0 === $recordCategory || 1 === $recordCategory));
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
		$categoriesList = $this->connector->request('GET', '/rest/all/V1/categories');
		$this->categoriesMagento['children_data'] = [\json_decode($categoriesList, true)];
		$this->parseMagentoCategory($this->categoriesMagento);
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
		$templateId = \Vtiger_Field_Model::getInstance('pscategory', \Vtiger_Module_Model::getInstance('Products'))->getFieldParams();
		$categoriesYF = (new Query())
			->where(['templateid' => $templateId])
			->from('vtiger_trees_templates_data')
			->createCommand()->queryAll();
		foreach ($categoriesYF as $categoryYF) {
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
		$templateId = \Vtiger_Field_Model::getInstance('pscategory', \Vtiger_Module_Model::getInstance('Products'))->getFieldParams();
		$categoriesYF = (new Query())
			->where(['templateid' => $templateId])
			->from('vtiger_trees_templates_data')
			->createCommand()->queryAll();
		$maxId = 1;
		foreach ($categoriesYF as $category) {
			$treeId = (int) str_replace('T', '', $category['tree']);
			if ($treeId >= $maxId) {
				$maxId = $treeId + 1;
			}
		}
		return $maxId;
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
			$categoryRequest = $this->connector->request('POST', '/rest/V1/categories', [
				'category' => [
					'name' => $category['name'],
					'parent_id' => $this->mapCategoryMagento[$category['parent_id']] ?? 0,
					'is_active' => $category['is_active'],
					'include_in_menu' => '1',
					'isActive' => 'true',
					'level' => $category['level'],
				]]);
			$this->saveCategoryMappingYF($categoryRequest['id'], $category['id']);
			$result = true;
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			\App\Log::error('Error during saving magento category: ' . $e->getResponse());
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
		$templateId = \Vtiger_Field_Model::getInstance('pscategory', \Vtiger_Module_Model::getInstance('Products'))->getFieldParams();
		$result = (new Query())->createCommand()->insert('vtiger_trees_templates_data', [
			'templateid' => $templateId,
			'tree' => 'T' . $categoryId,
			'parentTree' => 'T' . $categoryId,
			'name' => $category['name'],
			'depth' => $category['level'] - 1,
			'label' => $category['name'],
			'state' => '{"loaded":true,"opened":false,"selected":false,"disabled":false}'
		])->execute();
		$this->saveCategoryMappingYF($category['id'], $categoryId);
		if (isset($this->mapCategoryYF[$category['parent_id']])) {
			$category['parent_id'] = $this->mapCategoryYF[$category['parent_id']];
		} else {
			$category['parent_id'] = $categoryId;
		}
		$parentId = $this->parseParentYF($category);
		(new Query())->createCommand()->update('vtiger_trees_templates_data', ['parentTree' => $parentId], ['tree' => 'T' . $categoryId, 'templateid' => $templateId])->execute();
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
		} catch (\GuzzleHttp\Exception\ClientException $e) {
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
		$templateId = \Vtiger_Field_Model::getInstance('pscategory', \Vtiger_Module_Model::getInstance('Products'))->getFieldParams();
		$parentId = $this->parseParentYF($categoryMagento);
		$result = (new Query())->createCommand()->update('vtiger_trees_templates_data', [
			'parentTree' => $parentId,
			'name' => $categoryMagento['name'],
			'depth' => $categoryMagento['level'],
			'label' => $categoryMagento['name'],
			'state' => '{"loaded":true,"opened":false,"selected":false,"disabled":false}'
		], ['tree' => 'T' . $categoryYF['id'], 'templateid' => $templateId])->execute();
		if (!empty($categoryMagento['children_data'])) {
			foreach ($categoryMagento['children_data'] as $categoryChild) {
				$childParent = $this->categoriesYF[$this->mapCategoryYF[$categoryChild['parent_id']]];
				$childParentId = str_replace('::', '::T', $childParent['full_parent_id'] . '::' . $this->mapCategoryYF[$categoryChild['id']]);
				(new Query())->createCommand()->update('vtiger_trees_templates_data', [
					'parentTree' => $childParentId
				], ['tree' => 'T' . $this->mapCategoryYF[$categoryChild['id']], 'templateid' => $templateId])->execute();
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
		if (1 !== $categoryMagento['id'] && 2 !== $categoryMagento['id']) {
			try {
				$this->connector->request('DELETE', '/rest/all/V1/categories/' . $categoryMagento['id'], []);
				$result = true;
			} catch (\GuzzleHttp\Exception\ClientException $e) {
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
		\App\Db::getInstance()->createCommand()->delete('vtiger_trees_templates_data', ['tree' => 'T' . $categoryYF['id'], 'templateid' => \Vtiger_Field_Model::getInstance('pscategory', \Vtiger_Module_Model::getInstance('Products'))->getFieldParams()])->execute();
		\App\Db::getInstance()->createCommand()->delete('s_#__magento_record', ['crmid' => $categoryYF['id']])->execute();
	}

	/**
	 * Method to return category mapping (as key is YF id).
	 */
	public function getCategoryMappingMagento()
	{
		$this->mapCategoryMagento = (new Query())
			->select(['crmid', 'id'])
			->where(['type' => 'category'])
			->from('s_#__magento_record')
			->createCommand()->queryAllByGroup(0) ?? [];
	}

	/**
	 * Method to return category mapping (as key is Magento id).
	 */
	public function getCategoryMappingYF()
	{
		$this->mapCategoryYF = (new Query())
			->select(['id', 'crmid'])
			->where(['type' => 'category'])
			->from('s_#__magento_record')
			->createCommand()->queryAllByGroup(0) ?? [];
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
		$result = false;
		try {
			$this->connector->request('PUT', '/rest/all/V1/categories/' . $categoryMagento['id'] . '/move', ['parentId' => $this->mapCategoryMagento[$categoryYF['parent_id']] ?? 0]);
			$result = true;
		} catch (\GuzzleHttp\Exception\ClientException $e) {
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
			$result = (new Query())->createCommand()->insert('s_#__magento_record', [
				'id' => $categoryIdMagento,
				'crmid' => $categoryIdYF,
				'type' => 'category'
			])->execute();
		}
		$this->getCategoryMappingYF();
		$this->getCategoryMappingMagento();
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
		return (new Query())->createCommand()->update('s_#__magento_record', [
			'id' => $categoryIdMagento
		], ['crmid' => $categoryIdYF])->execute();
	}
}
