<?php

/**
 * Comarch product group synchronization file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription.
 * File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Comarch\Xl\Synchronizer;

/**
 * Comarch product group synchronization class.
 */
class ProductGroup extends \App\Integrations\Comarch\Synchronizer
{
	/** @var array Cache for data from the API */
	private $cache;
	/** @var array ID by name cache from the API */
	private $cacheList = [];
	/** @var array ID by name cache from the API */
	private $parentTree = [];
	/** @var array Field values */
	private $fieldValues = [];
	/** @var \Settings_Picklist_Field_Model */
	private $fieldModel;

	/** {@inheritdoc} */
	public function process(): void
	{
		$this->fieldModel = \Vtiger_Field_Model::getInstance(
			'pscategory',
			\Vtiger_Module_Model::getInstance('Products')
		);
		if ($this->fieldModel->isActiveField()) {
			$this->getAllFromApi();
			if (null !== $this->cache) {
				$this->import();
			} else {
				$this->controller->log('Skip import ' . $this->name, []);
			}
		}
	}

	/**
	 * Import account type from API.
	 *
	 * @return void
	 */
	public function import(): void
	{
		if ($this->config->get('log_all')) {
			$this->controller->log('Start import ' . $this->name, []);
		}
		$recordModel = \Settings_TreesManager_Record_Model::getInstanceById($this->fieldModel->getFieldParams());
		$this->loadTreeValues();
		$i = 0;
		foreach ($this->cache as $id => $value) {
			$key = $this->findTree($id);
			if (empty($key)) {
				try {
					if (empty($value['parent'])) {
						$newId = $recordModel->addValue($value['label']);
					} else {
						$newId = $recordModel->addValue($value['label'], $this->findTree($value['parent']));
					}
					$this->cacheList[$id] = $newId;
					++$i;
				} catch (\Throwable $ex) {
					$this->controller->log('Import ' . $this->name, ['API' => $value], $ex);
					\App\Log::error("Error during import {$this->name}: \n{$ex->__toString()}", self::LOG_CATEGORY);
				}
			}
		}
		if ($this->config->get('log_all')) {
			$this->controller->log('End import ' . $this->name, ['imported' => $i]);
		}
	}

	/** {@inheritdoc} */
	public function getYfValue($apiValue, array $field)
	{
		$this->loadCacheList();
		return $this->cacheList[$apiValue] ?? null;
	}

	/** {@inheritdoc} */
	public function getApiValue($yfValue, array $field)
	{
		$this->loadCacheList();
		$key = array_search($yfValue, $this->cacheList);
		return $key ?? null;
	}

	/**
	 * Get tree values.
	 */
	private function loadTreeValues(): void
	{
		$moduleName = $this->fieldModel->getModuleName();
		$values = [];
		foreach (\App\Fields\Tree::getValuesById($this->fieldModel->getFieldParams()) as $value) {
			$this->parentTree[$value['tree']] = \App\Fields\Tree::getParentIdx($value);
			$label = mb_strtolower($value['label']);
			$translated = mb_strtolower(\App\Language::translate($value['label'], $moduleName));
			if (isset($values[$label])) {
				$values[$label][] = $value['tree'];
			} else {
				$values[$label] = [$value['tree']];
			}
			if ($translated !== $label) {
				if (isset($values[$translated])) {
					$values[$translated][] = $value['tree'];
				} else {
					$values[$translated] = [$value['tree']];
				}
			}
		}
		$this->fieldValues = $values;
	}

	/**
	 * Find tree key.
	 *
	 * @param int $id
	 *
	 * @return string|null
	 */
	private function findTree(int $id): ?string
	{
		if (!isset($this->cache[$id])) {
			return null;
		}
		if (!empty($this->cacheList[$id])) {
			return $this->cacheList[$id];
		}
		$return = '';
		$value = $this->cache[$id];
		$name = mb_strtolower($value['label']);
		$create = empty($this->fieldValues[$name]);
		if (!$create) {
			if (empty($value['parent'])) {
				$create = false;
				foreach ($this->fieldValues[$name] as $parent) {
					if (empty($this->parentTree[$parent])) {
						$return = $parent;
						break;
					}
				}
			} else {
				foreach ($this->fieldValues[$name] as $tree) {
					if (!empty($this->parentTree[$tree]) && ($parentTree = $this->parentTree[$tree])) {
						if ($parentTree === $this->findTree($value['parent'])) {
							$return = $tree;
							break;
						}
					}
				}
			}
			if ($return) {
				$this->cacheList[$id] = $return;
			}
		}
		return $return;
	}

	/**
	 * Get all unit measure from API.
	 *
	 * @return array|null
	 */
	private function getAllFromApi(): ?array
	{
		if (null === $this->cache) {
			$this->cache = [];
			try {
				foreach ($this->getFromApi('Product/GetChildrenGroup/0') as $row) {
					$this->cache[$row['tgD_GidNumer']] = [
						'label' => $row['tgD_Kod']
					];
				}
			} catch (\Throwable $ex) {
				$this->controller->log('Get ' . $this->name, null, $ex);
				\App\Log::error("Error during getAllFromApi {$this->name}: \n{$ex->__toString()}", self::LOG_CATEGORY);
			}
			foreach ($this->cache as $id => $row) {
				try {
					if ($childrens = $this->getFromApi('Product/GetChildrenGroup/' . $id)) {
						foreach ($childrens as $children) {
							$this->cache[$children['tgD_GidNumer']] = [
								'label' => $children['tgD_Kod'],
								'parent' => $id,
							];
						}
					}
				} catch (\Throwable $ex) {
					$this->controller->log('Get ' . $this->name, null, $ex);
					\App\Log::error("Error during getAllFromApi {$this->name}: \n{$ex->__toString()}", self::LOG_CATEGORY);
				}
			}
		}
		return $this->cache;
	}

	/**
	 * Load cache list.
	 *
	 * @return void
	 */
	private function loadCacheList(): void
	{
		if (empty($this->cacheList)) {
			$this->process();
		}
	}
}
