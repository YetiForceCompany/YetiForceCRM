<?php

/**
 * Magento product map.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator\Maps;

/**
 * Magento Product map class.
 */
abstract class Magento extends \App\Integrations\Magento\Synchronizator\Record
{
	/**
	 * {@inheritdoc}
	 */
	public $mappedFields = ['productname' => 'name', 'unit_price' => 'price'];
	/**
	 * Mapped magento sku.
	 *
	 * @var array
	 */
	public $mapSku = [];

	/**
	 * Method to update product in Magento.
	 *
	 * @param int   $productId
	 * @param array $product
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	public function updateProduct(int $productId, array $product): bool
	{
		$this->getProductSkuMap();
		$result = false;
		if (!empty($this->mapSku[$productId])) {
			try {
				$data = [
					'product' => [
						'sku' => !empty($product['ean']) ? $product['ean'] : $product['productname'],
					]
				];
				if (!empty($this->mappedFields)) {
					foreach ($this->mappedFields as $fieldNameCrm => $fieldName) {
						$data['product'][$fieldName] = $product[$fieldNameCrm];
					}
				}
				$this->connector->request('PUT', 'rest/all/V1/products/' . $this->mapSku[$productId], $data);
				$result = true;
			} catch (\Throwable $ex) {
				\App\Log::error('Error during updating magento product: ' . $ex->getMessage(), 'Integrations/Magento');
			}
		}
		return $result;
	}

	/**
	 * Method to save product to Magento.
	 *
	 * @param array $product
	 *
	 * @return bool
	 */
	public function saveProduct(array $product): bool
	{
		$data = [
			'product' => [
				'type_id' => 'simple',
				'sku' => !empty($product['ean']) ? $product['ean'] : $product['productname'],
				'attribute_set_id' => 4,
			]
		];
		if (!empty($this->mappedFields)) {
			foreach ($this->mappedFields as $fieldNameCrm => $fieldName) {
				$data['product'][$fieldName] = $product[$fieldNameCrm];
			}
		}
		try {
			$productRequest = \App\Json::decode($this->connector->request('POST', '/rest/all/V1/products/',
				$data
			));
			$this->saveMapping($productRequest['id'], $product['productid'], 'product');
			$result = true;
		} catch (\Throwable $ex) {
			\App\Log::error('Error during saving magento product: ' . $ex->getMessage(), 'Integrations/Magento');
			$result = false;
		}
		return $result;
	}

	/**
	 * Method to delete product in Magento.
	 *
	 * @param int $productId
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool
	 */
	public function deleteProduct(int $productId): bool
	{
		$this->getProductSkuMap();
		try {
			$this->connector->request('DELETE', '/rest/all/V1/products/' . $this->mapSku[$productId], []);
			$this->deleteMapping($productId, $this->mapCrm[$productId]);
			$result = true;
		} catch (\Throwable $ex) {
			\App\Log::error('Error during deleting magento product: ' . $ex->getMessage(), 'Integrations/Magento');
			$result = false;
		}
		return $result;
	}

	/**
	 * Method to get sku mapped by product id.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function getProductSkuMap(): void
	{
		if (empty($this->mapSku)) {
			$data = \App\Json::decode($this->connector->request('GET', 'rest/all/V1/products?fields=items[id,sku]&searchCriteria'));
			if (!empty($data['items'])) {
				foreach ($data['items'] as $item) {
					$this->mapSku[$item['id']] = $item['sku'];
				}
			}
		}
	}

	/**
	 * Method to get products form Magento.
	 *
	 * @param array $ids
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \ReflectionException
	 *
	 * @return array
	 */
	public function getProducts(array $ids = []): array
	{
		$items = [];
		$data = \App\Json::decode($this->connector->request('GET', 'rest/all/V1/products?' . $this->getSearchCriteria($ids)));
		if (!empty($data['items'])) {
			foreach ($data['items'] as $item) {
				$items[$item['id']] = $item;
			}
		}
		return $items;
	}

	/**
	 * Method to get search criteria Magento products.
	 *
	 * @param array $ids
	 *
	 * @throws \ReflectionException
	 *
	 * @return string
	 */
	public function getSearchCriteria(array $ids): string
	{
		$pageSize = \App\Config::component('Magento', 'productLimit');
		$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][field]=entity_id';
		if (!empty($ids)) {
			$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][value]=' . implode(',', $ids);
			$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][condition_type]=in';
		} else {
			$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][value]=' . $this->lastScan['id'];
			$searchCriteria[] = 'searchCriteria[filter_groups][0][filters][0][condition_type]=gt';
			$searchCriteria[] = 'searchCriteria[filter_groups][1][filters][0][field]=updated_at';
			$searchCriteria[] = 'searchCriteria[filter_groups][1][filters][0][value]=' . $this->getFormattedTime($this->lastScan['start_date']);
			$searchCriteria[] = 'searchCriteria[filter_groups][1][filters][0][condition_type]=lteq';
			if (!empty($this->lastScan['end_date'])) {
				$searchCriteria[] = 'searchCriteria[filter_groups][2][filters][0][field]=updated_at';
				$searchCriteria[] = 'searchCriteria[filter_groups][2][filters][0][value]=' . $this->getFormattedTime($this->lastScan['end_date']);
				$searchCriteria[] = 'searchCriteria[filter_groups][2][filters][0][condition_type]=gteq';
			}
			$searchCriteria[] = 'searchCriteria[pageSize]=' . $pageSize;
		}
		return implode('&', $searchCriteria);
	}
}
