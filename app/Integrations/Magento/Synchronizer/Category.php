<?php

/**
 * Synchronize products categories file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription. File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizer;

/**
 * Synchronize products categories class.
 */
class Category extends Record
{
	/**
	 * Category cache.
	 *
	 * @var array
	 */
	protected $cache = [];

	/** {@inheritdoc} */
	public function process()
	{
		$this->lastScan = $this->config->getLastScan('category');
		if (!$this->lastScan['start_date'] || (0 === (int) $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])) {
			$this->config->setScan('category');
			$this->lastScan = $this->config->getLastScan('category');
		}
		if ($this->import()) {
			$this->config->setEndScan('category', $this->lastScan['start_date']);
		}
	}

	/**
	 * Import categories from Magento.
	 *
	 * @return bool
	 */
	public function import(): bool
	{
		$allChecked = false;
		try {
			if ($categories = $this->getCategoriesFromApi()) {
				foreach ($categories as $category) {
					if (empty($category)) {
						\App\Log::error('Empty category details', 'Integrations/Magento');
						continue;
					}
					if (0 === (int) $category['parent_id']) {
						continue;
					}
					try {
						if (empty($this->getCrmId($category['id']))) {
							$this->createCategory($category['id'], $category);
						}
					} catch (\Throwable $ex) {
						$this->log('Saving category', $ex);
						\App\Log::error('Error during saving category: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
					}
					$this->config->setScan('category', 'id', $category['id']);
				}
			} else {
				$allChecked = true;
			}
		} catch (\Throwable $ex) {
			$this->log('Import categories', $ex);
			\App\Log::error('Error during import category: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
		}
		return $allChecked;
	}

	/**
	 * Method to get categories form Magento.
	 *
	 * @return array
	 */
	public function getCategoriesFromApi(): array
	{
		$items = [];
		$data = \App\Json::decode($this->connector->request('GET', $this->config->get('store_code') . '/V1/categories/list?' . $this->getSearchCriteria($this->config->get('categories_limit'))));
		if (!empty($data['items'])) {
			$items = $data['items'];
			foreach ($items as $item) {
				$this->cache[$item['id']] = $item;
			}
		}
		return $items;
	}

	/**
	 * Get crm id by magento id.
	 *
	 * @param int         $magentoId
	 * @param string|null $moduleName
	 *
	 * @return int
	 */
	public function getCrmId(int $magentoId): int
	{
		if (\App\Cache::staticHas('CrmIdByMagentoIdProductCategory', $magentoId)) {
			return \App\Cache::staticGet('CrmIdByMagentoIdProductCategory', $magentoId);
		}
		$queryGenerator = new \App\QueryGenerator('ProductCategory');
		$queryGenerator->setStateCondition('All');
		$queryGenerator->setFields(['id'])->permissions = false;
		$queryGenerator->addCondition('magento_id', $magentoId, 'e');
		$queryGenerator->addCondition('magento_server_id', $this->config->get('id'), 'e');
		$crmId = $queryGenerator->createQuery()->scalar() ?: 0;
		\App\Cache::staticSave('CrmIdByMagentoIdProductCategory', $magentoId, $crmId);
		return $crmId;
	}

	/**
	 * Create category in CRM.
	 *
	 * @param int   $id
	 * @param array $category
	 *
	 * @return void
	 */
	public function createCategory(int $id, array $category = [])
	{
		if (empty($category)) {
			$category = $this->cache[$id] ?? $this->getCategory($id);
		}
		$recordModel = \Vtiger_Record_Model::getCleanInstance('ProductCategory');
		$parentId = 0;
		if ($category['parent_id'] > 1) {
			$parentId = $this->getCrmId($category['parent_id']) ?: $this->createCategory($category['parent_id']);
		}
		$recordModel->setData([
			'category' => trim($category['name']),
			'parent_id' => $parentId,
			'active' => $category['is_active'],
			'magento_server_id' => $this->config->get('id'),
			'magento_id' => $id,
		]);
		$recordModel->save();
		\App\Cache::staticSave('CrmIdByMagentoIdProductCategory', $id, $recordModel->getId());
		return $recordModel->getId();
	}

	/**
	 * Method to get category by id form Magento.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function getCategory(int $id): array
	{
		return $this->cache[$id] = \App\Json::decode($this->connector->request('GET', $this->config->get('store_code') . '/V1/categories/' . $id));
	}

	/** {@inheritdoc} */
	public function getSearchCriteria(int $pageSize = 10): string
	{
		$searchCriteria = [];
		$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][field]=entity_id';
		$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][value]=' . $this->lastScan['id'];
		$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][condition_type]=gt';
		$searchCriteria[] = 'searchCriteria[filter_groups][1][filters][0][field]=created_at';
		$searchCriteria[] = 'searchCriteria[filter_groups][1][filters][0][value]=' . $this->getFormattedTime($this->lastScan['start_date']);
		$searchCriteria[] = 'searchCriteria[filter_groups][1][filters][0][condition_type]=lteq';
		if (!empty($this->lastScan['end_date'])) {
			$searchCriteria[] = 'searchCriteria[filter_groups][2][filters][0][field]=created_at';
			$searchCriteria[] = 'searchCriteria[filter_groups][2][filters][0][value]=' . $this->getFormattedTime($this->lastScan['end_date']);
			$searchCriteria[] = 'searchCriteria[filter_groups][2][filters][0][condition_type]=gteq';
		}
		$searchCriteria[] = 'searchCriteria[pageSize]=' . $pageSize;
		$searchCriteria = implode('&', $searchCriteria);
		return $searchCriteria ?? 'searchCriteria';
	}
}
