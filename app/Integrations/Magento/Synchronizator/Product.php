<?php

/**
 * Synchronize products.
 *
 * @package Integration
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */

namespace App\Integrations\Magento\Synchronizator;

/**
 * Product class.
 */
class Product extends Integrators\Product
{
	/**
	 * {@inheritdoc}
	 */
	public function process(): void
	{
		$this->config = \App\Integrations\Magento\Config::getInstance();
		$this->lastScan = $this->config::getLastScan('product');
		if (!$this->lastScan['start_date'] || (0 === (int) $this->lastScan['id'] && 0 === (int) $this->lastScan['idcrm'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])) {
			$this->config::setScan('product');
			$this->lastScan = $this->config::getLastScan('product');
		}
		$this->getMapping('product');
		$resultCrm = $this->checkProductsCrm();
		$result = $this->checkProducts();
		$resultMap = $this->checkProductsMap();
		if ($resultCrm && $result && $resultMap) {
			$this->config::setEndScan('product', $this->lastScan['start_date']);
		}
	}

	/**
	 * Method to save, update or delete products from YetiForce.
	 */
	public function checkProductsCrm(): bool
	{
		$result = false;
		$this->getProductSkuMap();
		$productsCrm = $this->getProductsCrm();
		$products = $this->getProducts($this->getFormattedRecordsIds(array_keys($productsCrm)));
		if (!empty($productsCrm)) {
			foreach ($productsCrm as $id => $productCrm) {
				$productCrm['productid'] = $id;
				if (isset($this->map[$id], $products[$this->map[$id]])) {
					$productData = $this->getProductFullData($this->mapSku[$this->map[$id]]);
					if ($this->hasChanges($productCrm, $productData)) {
						if (self::MAGENTO === $this->whichToUpdate($productCrm, $productData)) {
							$this->updateProduct($this->map[$id], $productCrm);
						} else {
							$this->updateProductCrm($id, $productData);
						}
					}
				} elseif (isset($this->map[$id]) && !isset($products[$this->map[$id]])) {
					$this->deleteProductCrm($id);
				} else {
					$this->saveProduct($productCrm);
				}
				$this->config::setScan('product', 'idcrm', $id);
			}
		} else {
			$result = true;
		}
		return $result;
	}

	/**
	 * Method to save, update or delete products from Magento.
	 *
	 * @return bool
	 */
	public function checkProducts(): bool
	{
		$allChecked = false;
		try {
			$products = $this->getProducts();
			$productsCrm = $this->getProductsCrm($this->getFormattedRecordsIds(array_keys($products), self::YETIFORCE));
			if (!empty($products)) {
				foreach ($products as $id => $product) {
					$productData = $this->getProductFullData($product['sku']);
					if (isset($this->mapCrm[$id], $productsCrm[$this->mapCrm[$id]])) {
						if ($this->hasChanges($productsCrm[$this->mapCrm[$id]], $productData)) {
							if (self::MAGENTO === $this->whichToUpdate($productsCrm[$this->mapCrm[$id]], $productData)) {
								$this->updateProduct($id, $productsCrm[$this->mapCrm[$id]]);
							} else {
								$this->updateProductCrm($this->mapCrm[$id], $productData);
							}
						}
					} elseif (isset($this->mapCrm[$id]) && !isset($productsCrm[$this->mapCrm[$id]])) {
						$this->deleteProduct($id);
					} else {
						$this->saveProductCrm($productData);
					}
					$this->config::setScan('product', 'id', $id);
				}
			} else {
				$allChecked = true;
			}
		} catch (\Throwable $ex) {
			\App\Log::error('Error during saving magento products to yetiforce: ' . $ex->getMessage(), 'Integrations/Magento');
			$allChecked = false;
		}
		return $allChecked;
	}

	/**
	 * Method to delete removed products from YetiForce and Magento.
	 *
	 * @return bool
	 */
	public function checkProductsMap(): bool
	{
		$allChecked = false;
		try {
			$this->getMapping('product', $this->lastScan['idmap'], \App\Config::component('Magento', 'productLimit'));
			$mapKeys = array_keys($this->map);
			if (!empty($mapKeys)) {
				$productsCrm = $this->getProductsCrm($mapKeys);
				$products = $this->getProducts($this->getFormattedRecordsIds($mapKeys));
				if ($diffedRecords = \array_diff_key($this->map, $productsCrm)) {
					foreach ($diffedRecords as $idCrm => $id) {
						$this->deleteProduct($id);
					}
				}
				if ($diffedRecords = \array_diff_key($this->mapCrm, $products)) {
					foreach ($diffedRecords as $id => $idCrm) {
						$this->deleteProductCrm($idCrm);
					}
				}
				$this->config::setScan('product', 'idmap', !empty($this->mapCrm) ? max(array_keys($this->mapCrm)) : 0);
			} else {
				$allChecked = true;
			}
		} catch (\Throwable $ex) {
			\App\Log::error('Error during checking products map: ' . $ex->getMessage(), 'Integrations/Magento');
		}
		return $allChecked;
	}

	/**
	 * Method to get products form YetiForce.
	 *
	 * @param array $ids
	 *
	 * @throws \ReflectionException
	 *
	 * @return array
	 */
	public function getProductsCrm(array $ids = []): array
	{
		$queryGenerator = new \App\QueryGenerator('Products');
		$query = $queryGenerator->createQuery();
		if (!empty($ids)) {
			$query->andWhere(['IN', 'productid', $ids]);
		} else {
			$query->andWhere(['>', 'productid', $this->lastScan['idcrm']]);
			$query->andWhere(['<=', 'modifiedtime', $this->lastScan['start_date']]);
			if (!empty($this->lastScan['end_date'])) {
				$query->andWhere(['>=', 'modifiedtime', $this->lastScan['end_date']]);
			}
			$query->limit(\App\Config::component('Magento', 'productLimit'));
		}
		return $query->createCommand()->queryAllByGroup(1);
	}

	/**
	 * Method to save product to YetiForce.
	 *
	 * @param array $data
	 *
	 * @throws \Exception
	 */
	public function saveProductCrm(array $data): void
	{
		$productFields = new \App\Integrations\Magento\Synchronizator\Maps\Product();
		$productFields->setData($data);
		$dataCrm = $productFields->getDataCrm();
		if (!empty($dataCrm)) {
			try {
				$recordModel = \Vtiger_Record_Model::getCleanInstance('Products');
				$recordModel->setData($dataCrm);
				$recordModel->save();
				$this->saveMapping($data['id'], $recordModel->getId(), 'product');
			} catch (\Throwable $ex) {
				\App\Log::error('Error during saving yetiforce product: ' . $ex->getMessage(), 'Integrations/Magento');
			}
		}
	}

	/**
	 * Method to update product in YetiForce.
	 *
	 * @param int   $id
	 * @param array $data
	 *
	 * @throws \Exception
	 */
	public function updateProductCrm(int $id, array $data): void
	{
		try {
			$productFields = new \App\Integrations\Magento\Synchronizator\Maps\Product();
			$productFields->setData($data);
			$recordModel = \Vtiger_Record_Model::getInstanceById($id, 'Products');
			foreach ($productFields->getDataCrm(true) as $key => $value) {
				$recordModel->set($key, $value);
			}
			$recordModel->save();
		} catch (\Throwable $ex) {
			\App\Log::error('Error during updating yetiforce product: ' . $ex->getMessage(), 'Integrations/Magento');
		}
	}

	/**
	 * Method to delete product in YetiForce.
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function deleteProductCrm(int $id): bool
	{
		try {
			$recordModel = \Vtiger_Record_Model::getInstanceById($id, 'Products');
			$recordModel->delete();
			$this->deleteMapping($this->map[$id], $id);
			$result = true;
		} catch (\Throwable $ex) {
			\App\Log::error('Error during deleting yetiforce product: ' . $ex->getMessage(), 'Integrations/Magento');
			$result = false;
		}
		return $result;
	}
}
