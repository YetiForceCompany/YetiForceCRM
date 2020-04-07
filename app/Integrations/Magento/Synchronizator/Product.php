<?php

/**
 * Synchronize products.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator;

/**
 * Product class.
 */
class Product extends Record
{
	/**
	 * {@inheritdoc}
	 */
	public function process(): void
	{
		$this->lastScan = $this->config->getLastScan('product');
		if (!$this->lastScan['start_date'] || (0 === (int) $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])) {
			$this->config->setScan('product');
			$this->lastScan = $this->config->getLastScan('product');
		}
		if ($this->import()) {
			$this->config->setEndScan('product', $this->lastScan['start_date']);
		}
	}

	/**
	 * Import products from magento.
	 *
	 * @return bool
	 */
	public function import(): bool
	{
		$allChecked = false;
		try {
			if ($products = $this->getProductsFromApi()) {
				foreach ($products as $id => $product) {
					if (empty($product)) {
						\App\Log::error('Empty product details', 'Integrations/Magento');
						continue;
					}
					$className = $this->config->get('product_map_class') ?: '\App\Integrations\Magento\Synchronizator\Maps\Product';
					$mapModel = new $className($this);
					$mapModel->setData($product);
					if ($dataCrm = $mapModel->getDataCrm()) {
						try {
							if (!$this->findProduct($product['sku'])) {
								$this->createProductInCrm($dataCrm);
							}
						} catch (\Throwable $ex) {
							\App\Log::error('Error during saving product: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
						}
					} else {
						\App\Log::error('Empty map product details', 'Integrations/Magento');
					}
					$this->config->setScan('product', 'id', $id);
				}
			} else {
				$allChecked = true;
			}
		} catch (\Throwable $ex) {
			\App\Log::error('Error during import products: ' . PHP_EOL . $ex->__toString() . PHP_EOL, 'Integrations/Magento');
		}
		return $allChecked;
	}

	/**
	 * Method to get products form Magento.
	 *
	 * @return array
	 */
	public function getProductsFromApi(): array
	{
		$items = [];
		$data = \App\Json::decode($this->connector->request('GET', $this->config->get('store_code') . '/V1/products?' . $this->getSearchCriteria($this->config->get('productLimit'))));
		if (!empty($data['items'])) {
			foreach ($data['items'] as $item) {
				$items[$item['id']] = $item;
			}
		}
		return $items;
	}

	/**
	 * {@inheritdoc}
	 */
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

	/**
	 * Method to create product in CRM.
	 *
	 * @param array $dataCrm
	 *
	 * @return int
	 */
	public function createProductInCrm(array $dataCrm): int
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('Products');
		$fields = $recordModel->getModule()->getFields();
		$categories = $dataCrm['categories'];
		unset($dataCrm['categories']);
		foreach ($dataCrm as $key => $value) {
			if (isset($fields[$key])) {
				$recordModel->set($key, $value);
			}
		}
		$recordModel->save();
		$this->addCategories($recordModel, $categories);
		return $recordModel->getId() ?? 0;
	}

	/**
	 * Add categories to product.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param array                $categories
	 */
	public function addCategories(\Vtiger_Record_Model $recordModel, array $categories): void
	{
		$relationModel = \Vtiger_Relation_Model::getInstance($recordModel->getModule(), \Vtiger_Module_Model::getInstance('ProductCategory'));
		foreach ($categories as $categoryId) {
			$relationModel->addRelation($recordModel->getId(), $categoryId);
		}
	}
}
