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

namespace App\Integrations\Magento\Synchronizator\Integrators;

/**
 * Magento Product map class.
 */
abstract class Product extends \App\Integrations\Magento\Synchronizator\Record
{
	/**
	 * Mapped magento id to sku.
	 *
	 * @var array
	 */
	public $mapIdToSku = [];
	/**
	 * Mapped magento sku to id.
	 *
	 * @var array
	 */
	public $mapSkuToId = [];

	/**
	 * Method to update product in Magento.
	 *
	 * @param int   $productId
	 * @param array $product
	 *
	 * @return bool
	 */
	public function updateProduct(int $productId, array $product): bool
	{
		$result = false;
		if (!empty($this->mapIdToSku[$productId])) {
			try {
				$productFields = new \App\Integrations\Magento\Synchronizator\Maps\Product();
				$productFields->setDataCrm($product);
				$this->connector->request('PUT', 'rest/' . \App\Config::component('Magento', 'storeCode') . '/V1/products/' . urlencode($this->mapIdToSku[$productId]), $productFields->getData(true));
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
		$productFields = new \App\Integrations\Magento\Synchronizator\Maps\Product();
		$productFields->setDataCrm($product);
		try {
			$productRequest = \App\Json::decode($this->connector->request('POST', '/rest/' . \App\Config::component('Magento', 'storeCode') . '/V1/products/', $productFields->getData()));
			if (!empty($productRequest)) {
				$this->saveImages($productRequest['sku'], \App\Json::decode($product['imagename']));
				$this->saveMapping($productRequest['id'], $product['productid'], 'product');
			} else {
				\App\Log::error('Error during saving magento product: empty product request', 'Integrations/Magento');
			}
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
		try {
			if (!empty($this->mapIdToSku)) {
				if (isset($this->mapIdToSku[$productId])) {
					$this->connector->request('DELETE', '/rest/all/V1/products/' . urlencode($this->mapIdToSku[$productId]), []);
				}
				$this->deleteMapping($productId, $this->mapCrm['product'][$productId], 'product');
			}
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
		if (empty($this->mapIdToSku)) {
			$data = \App\Json::decode($this->connector->request('GET', 'rest/' . \App\Config::component('Magento', 'storeCode') . '/V1/products?fields=items[id,sku]&searchCriteria'));
			if (!empty($data['items'])) {
				foreach ($data['items'] as $item) {
					$this->mapIdToSku[$item['id']] = $item['sku'];
				}
				$this->mapSkuToId = \array_flip($this->mapIdToSku);
			}
		}
	}

	/**
	 * Method to get products form Magento.
	 *
	 * @param string|array $ids
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \ReflectionException
	 *
	 * @return array
	 */
	public function getProducts($ids = ''): array
	{
		$items = [];
		$data = \App\Json::decode($this->connector->request('GET', 'rest/' . \App\Config::component('Magento', 'storeCode') . '/V1/products?' . $this->getSearchCriteria($ids, \App\Config::component('Magento', 'productLimit'))));
		if (!empty($data['items'])) {
			foreach ($data['items'] as $item) {
				$items[$item['id']] = $item;
			}
		}
		return $items;
	}

	/**
	 * Get full product data.
	 *
	 * @param string $sku
	 *
	 * @return array|mixed
	 */
	public function getProductFullData(string $sku)
	{
		$data = [];
		try {
			$data = \App\Json::decode($this->connector->request('GET', 'rest/' . \App\Config::component('Magento', 'storeCode') . '/V1/products/' . urlencode($sku)));
		} catch (\Throwable $ex) {
			\App\Log::error('Error during getting magento product data: ' . $ex->getMessage(), 'Integrations/Magento');
		}
		return $data;
	}

	/**
	 * Method to get search criteria Magento products.
	 *
	 * @param array $ids
	 * @param int   $pageSize
	 *
	 * @throws \ReflectionException
	 *
	 * @return string
	 */
	public function getSearchCriteria($ids, int $pageSize = 10): string
	{
		return parent::getSearchCriteria($ids, $pageSize) . '&fields=items[id,sku]';
	}

	/**
	 * Update product images.
	 *
	 * @param $sku
	 * @param $imagesData
	 */
	public function updateImages($sku, $imagesData)
	{
		if (!empty($imagesData['add'])) {
			$this->saveImages($sku, $imagesData['add']);
		}
		if (!empty($imagesData['remove'])) {
			$this->removeImages($sku, $imagesData['remove']);
		}
	}

	/**
	 * Save product images.
	 *
	 * @param $sku
	 * @param $images
	 */
	public function saveImages($sku, $images)
	{
		if (!empty($images)) {
			foreach ($images as $image) {
				$imageBaseData = \App\Fields\File::getImageBaseData($image['path']);
				$imageBaseData = explode(',', $imageBaseData);
				$imageType = str_replace([';base64', 'data:'], '', $imageBaseData[0]);
				$data = [
					'entry' => [
						'media_type' => 'image',
						'label' => 'Image',
						'disabled' => false,
						'types' => [
							'image',
							'small_image',
							'thumbnail'
						],
						'content' => [
							'base64_encoded_data' => $imageBaseData[1],
							'type' => $imageType,
							'name' => $image['name']
						],
					]
				];
				try {
					\App\Json::decode($this->connector->request('POST', 'rest/V1/products/' . urlencode($sku) . '/media',
						$data
					));
				} catch (\Throwable $ex) {
					\App\Log::error('Error during saving magento product images: ' . $ex->getMessage(), 'Integrations/Magento');
				}
			}
		}
	}

	/**
	 * Remove product images.
	 *
	 * @param $sku
	 * @param $images
	 */
	public function removeImages($sku, $images)
	{
		if (!empty($images)) {
			foreach ($images as $image) {
				try {
					\App\Json::decode($this->connector->request('DELETE', 'rest/V1/products/' . urlencode($sku) . "/media/{$image['id']}"));
				} catch (\Throwable $ex) {
					\App\Log::error('Error during removing magento product image: ' . $ex->getMessage(), 'Integrations/Magento');
				}
			}
		}
	}
}
