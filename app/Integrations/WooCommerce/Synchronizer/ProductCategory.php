<?php

/**
 * WooCommerce product categories synchronization file.
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

namespace App\Integrations\WooCommerce\Synchronizer;

/**
 * WooCommerce product categories synchronization class.
 */
class ProductCategory extends Base
{
	/** @var int Records limit per page */
	const RECORDS_LIMIT_PER_PAGE = 100;
	/** @var array Category cache. */
	protected $cache = [];

	/** {@inheritdoc} */
	public function process(): void
	{
		if (\App\Module::isModuleActive('ProductCategory')) {
			$this->getAllFromApi();
			$direction = (int) $this->config->get('directions_categories');
			if ($this->config->get('master')) {
				if (self::DIRECTION_TWO_WAY === $direction || self::DIRECTION_YF_TO_API === $direction) {
					$this->export();
				}
				if (self::DIRECTION_TWO_WAY === $direction || self::DIRECTION_API_TO_YF === $direction) {
					$this->import();
				}
			} else {
				if (self::DIRECTION_TWO_WAY === $direction || self::DIRECTION_API_TO_YF === $direction) {
					$this->import();
				}
				if (self::DIRECTION_TWO_WAY === $direction || self::DIRECTION_YF_TO_API === $direction) {
					$this->export();
				}
			}
		}
	}

	/**
	 * Import products category from API.
	 *
	 * @return void
	 */
	public function import(): void
	{
		if ($this->config->get('logAll')) {
			$this->controller->log('Start import tags', []);
		}
		$i = 0;
		foreach ($this->cache as $category) {
			try {
				$yfId = $this->getYfId($category['id'], 'ProductCategory');
				if (empty($yfId)) {
					$this->saveCategory($category['id'], $category);
					++$i;
				} elseif (!$this->config->get('master')) {
					$this->saveCategory($category['id'], $category, $yfId);
					++$i;
				}
			} catch (\Throwable $ex) {
				$this->controller->log('Import category', $category, $ex);
				\App\Log::error(
					'Error during import category: ' . PHP_EOL . $ex->__toString(),
					self::LOG_CATEGORY
				);
			}
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('End import tags', ['imported' => $i]);
		}
	}

	/**
	 * Export products category to API.
	 *
	 * @return void
	 */
	public function export(): void
	{
		$queryGenerator = $this->getFromYf('ProductCategory');
		$queryGenerator->setFields(['id', 'woocommerce_id', 'category', 'description', 'alias', 'parent_id']);
		$queryGenerator->addCondition('active', 1, 'e');
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			try {
				if (empty($row['woocommerce_id'])) {
					$this->connector->request('POST', 'products/categories', [
						'name' => $row['category'],
						'slug' => $row['alias'],
						'description' => $row['description'],
						'parent' => $row['parent_id'] ? $this->getApiId($row['parent_id'], 'ProductCategory') : 0,
					]);
				} elseif ($this->config->get('master')) {
					$this->connector->request('PUT', 'products/categories/' . $row['woocommerce_id'], [
						'name' => $row['category'],
						'slug' => $row['alias'],
						'description' => $row['description'],
						'parent' => $row['parent_id'] ? $this->getApiId($row['parent_id'], 'ProductCategory') : 0,
					]);
				}
			} catch (\Throwable $th) {
				$this->controller->log('Export category', $row, $th);
				\App\Log::error('Error during export category: ' . PHP_EOL . $th->__toString(), self::LOG_CATEGORY);
			}
		}
	}

	/**
	 * Save category in YF.
	 *
	 * @param int   $id
	 * @param array $category
	 * @param int   $yfId
	 *
	 * @return int
	 */
	public function saveCategory(int $id, array $category = [], int $yfId = 0): int
	{
		if (empty($category)) {
			$category = $this->cache[$id] ?? $this->getCategory($id);
		}
		if ($yfId) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($yfId, 'ProductCategory');
		} else {
			$recordModel = \Vtiger_Record_Model::getCleanInstance('ProductCategory');
		}
		$recordModel->set('woocommerce_server_id', $this->config->get('id'));
		$recordModel->set('woocommerce_id', $id);
		$recordModel->set('category', trim($category['name']));
		$recordModel->set('alias', trim($category['slug']));
		$recordModel->set('description', $category['description']);
		$recordModel->set('active', 1);
		$parentId = 0;
		if ($category['parent'] > 1) {
			$parentId = $this->getYfId($category['parent'], 'ProductCategory') ?: $this->saveCategory($category['parent']);
		}
		$recordModel->set('parent_id', $parentId);
		$recordModel->save();
		if (!$yfId) {
			\App\Cache::staticSave('Integrations/WooCommerce/CRM_ID/ProductCategory', $id, $recordModel->getId());
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('Import category', [
				'API' => $category,
				'YF' => $recordModel->getData(),
			]);
		}
		return $recordModel->getId();
	}

	/**
	 * Get category by id form WooCommerce.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function getCategory(int $id): array
	{
		return $this->cache[$id] = \App\Json::decode($this->connector->request('GET', 'products/categories/' . $id));
	}

	/**
	 * Get all tags from API.
	 *
	 * @return void
	 */
	private function getAllFromApi(): void
	{
		try {
			$page = 1;
			$load = true;
			while ($load) {
				if ($rows = $this->getFromApi('products/categories?&page=' . $page . '&per_page=' . self::RECORDS_LIMIT_PER_PAGE)) {
					foreach ($rows as $row) {
						$this->cache[$row['id']] = $row;
					}
					++$page;
				}
				if (self::RECORDS_LIMIT_PER_PAGE !== \count($rows)) {
					$load = false;
				}
			}
		} catch (\Throwable $ex) {
			$this->controller->log('Get categories', null, $ex);
			\App\Log::error('Error during get categories: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
		}
	}
}
