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
class Product extends Maps\Magento
{
	/**
	 * {@inheritdoc}
	 */
	public function process(): void
	{
		$this->config = \App\Integrations\Magento\Config::getInstance();
		$this->lastScan = $this->config::getLastScan('product');
		if (!$this->lastScan['start_date'] || (0 === (int) $this->lastScan['id'] && 0 === (int) $this->lastScan['idcrm'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])) {
			$this->config::setStartScan('product');
			$this->lastScan = $this->config::getLastScan('product');
		}
		$this->getMapping('product');
		$resultYF = $this->checkProductsYF();
		$resultMagento = $this->checkProductsMagento();
		$resultMap = $this->checkProductsMap();
		if ($resultYF && $resultMagento && $resultMap) {
			$this->config::setEndScan('product', $this->lastScan['start_date']);
		}
	}

	/**
	 * Method to save, update or delete products from YetiForce.
	 */
	public function checkProductsYF(): bool
	{
		$result = false;
		$productsYF = $this->getProductsYF();
		$productsMagento = $this->getProductsMagento($this->getFormatedRecordsIds(array_keys($productsYF)));
		if (!empty($productsYF)) {
			foreach ($productsYF as $id => $productYF) {
				$productYF['productid'] = $id;
				if (isset($this->mapMagento[$id], $productsMagento[$this->mapMagento[$id]])) {
					if ($this->hasChanges($productYF, $productsMagento[$this->mapMagento[$id]])) {
						if ('magento' === $this->whichToUpdate($productYF, $productsMagento[$this->mapMagento[$id]])) {
							$this->updateProductMagento($this->mapMagento[$id], $productYF);
						} else {
							$this->updateProductYF($id, $productsMagento[$this->mapMagento[$id]]);
						}
					}
				} elseif (isset($this->mapMagento[$id]) && !isset($productsMagento[$this->mapMagento[$id]])) {
					$this->deleteProductYF($id);
				} else {
					$this->saveProductMagento($productYF);
				}
				$this->config::setLastScanId('product', 'idcrm', $id);
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
	public function checkProductsMagento(): bool
	{
		$allChecked = false;
		try {
			$productsMagento = $this->getProductsMagento();
			$productsYF = $this->getProductsYF($this->getFormatedRecordsIds(array_keys($productsMagento), 'yetiforce'));
			if (!empty($productsMagento)) {
				foreach ($productsMagento as $id => $product) {
					if (isset($this->mapYF[$id], $productsYF[$this->mapYF[$id]])) {
						if ($this->hasChanges($productsYF[$this->mapYF[$id]], $product)) {
							if ('magento' === $this->whichToUpdate($productsYF[$this->mapYF[$id]], $product)) {
								$this->updateProductMagento($id, $productsYF[$this->mapYF[$id]]);
							} else {
								$this->updateProductYF($this->mapYF[$id], $product);
							}
						}
					} elseif (isset($this->mapYF[$id]) && !isset($productsYF[$this->mapYF[$id]])) {
						$this->deleteProductMagento($id);
					} else {
						$this->saveProductYF($product);
					}
					$this->config::setLastScanId('product', 'id', $id);
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
			$mapKeys = array_keys($this->mapMagento);
			if (!empty($mapKeys)) {
				$productsYF = $this->getProductsYF($mapKeys);
				$productsMagento = $this->getProductsMagento($this->getFormatedRecordsIds($mapKeys));
				if ($diffedRecords = \array_diff_key($this->mapMagento, $productsYF)) {
					foreach ($diffedRecords as $idYF => $idMagento) {
						$this->deleteProductMagento($idMagento);
					}
				}
				if ($diffedRecords = \array_diff_key($this->mapYF, $productsMagento)) {
					foreach ($diffedRecords as $idMagento => $idYF) {
						$this->deleteProductYF($idYF);
					}
				}
				$this->config::setLastScanId('product', 'idmap', max(array_keys($this->mapYF)));
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
	public function getProductsYF(array $ids = []): array
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
	public function saveProductYF(array $data): void
	{
		$fields = $this->getData($data);
		if (!empty($fields)) {
			$recordModel = \Vtiger_Record_Model::getCleanInstance('Products');
			$recordModel->setData($fields);
			$recordModel->save();
			$this->saveMapping($data['id'], $recordModel->getId(), 'product');
		}
	}

	/**
	 * Method to update product in YetiForce.
	 *
	 * @param int   $id
	 * @param array $productData
	 *
	 * @throws \Exception
	 */
	public function updateProductYF(int $id, array $productData): void
	{
		$fields = $this->getData($productData);
		$recordModel = \Vtiger_Record_Model::getInstanceById($id, 'Products');
		foreach ($fields as $key => $value) {
			$recordModel->set($key, $value);
		}
		$recordModel->save();
	}

	/**
	 * Method to delete product in YetiForce.
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function deleteProductYF(int $id): bool
	{
		try {
			$recordModel = \Vtiger_Record_Model::getInstanceById($id, 'Products');
			$recordModel->delete();
			$this->deleteMapping($this->mapMagento[$id], $id);
			$result = true;
		} catch (\Throwable $ex) {
			\App\Log::error('Error during deleting yetiforce product: ' . $ex->getMessage(), 'Integrations/Magento');
			$result = false;
		}
		return $result;
	}
}
