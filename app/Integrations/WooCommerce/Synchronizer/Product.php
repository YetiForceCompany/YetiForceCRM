<?php

/**
 * WooCommerce product synchronization file.
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
 * WooCommerce product synchronization class.
 */
class Product extends Base
{
	/** @var int[] Imported ids */
	private $imported = [];
	/** @var int[] Exported ids */
	private $exported = [];

	/** {@inheritdoc} */
	public function process(): void
	{
		$mapModel = $this->getMapModel();
		if (\App\Module::isModuleActive($mapModel->getModule())) {
			$direction = (int) $this->config->get('direction_products');
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
	 * Import products from WooCommerce.
	 *
	 * @return void
	 */
	public function import(): void
	{
		$this->lastScan = $this->config->getLastScan('importProduct');
		if (
			!$this->lastScan['start_date']
			|| (0 === $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])
		) {
			$this->config->setScan('importProduct');
			$this->lastScan = $this->config->getLastScan('importProduct');
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('Start import product', [
				'lastScan' => $this->lastScan,
			]);
		}
		$i = 0;
		try {
			$page = $this->lastScan['page'] ?? 1;
			$load = true;
			$finish = false;
			$limit = $this->config->get('products_limit');
			while ($load) {
				if ($rows = $this->getFromApi('products?&page=' . $page . '&' . $this->getSearchCriteria($limit))) {
					foreach ($rows as $id => $row) {
						$this->importProduct($row);
						$this->config->setScan('importProduct', 'id', $id);
						++$i;
					}
					++$page;
					if (\is_callable($this->controller->bathCallback)) {
						$load = \call_user_func($this->controller->bathCallback, 'importProduct');
					}
					if ($this->config->get('products_limit') !== \count($rows)) {
						$finish = true;
					}
				} else {
					$finish = true;
				}
				if ($finish || !$load) {
					$load = false;
					if ($finish) {
						$this->config->setEndScan('importProduct', $this->lastScan['start_date']);
					} else {
						$this->config->setScan('importProduct', 'page', $page);
					}
				}
			}
		} catch (\Throwable $ex) {
			$this->controller->log('Import products', null, $ex);
			\App\Log::error('Error during import products: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('End import product', ['imported' => $i]);
		}
	}

	/**
	 * Import product.
	 *
	 * @param array $row
	 *
	 * @return void
	 */
	public function importProduct(array $row): void
	{
		$mapModel = $this->getMapModel();
		$mapModel->isVariation = 'variation' === $row['type'];
		$mapModel->setDataApi($row);
		if ($dataYf = $mapModel->getDataYf()) {
			try {
				$yfId = $this->getYfId($row['id']);
				if (empty($yfId) || empty($this->exported[$yfId])) {
					$mapModel->loadRecordModel($yfId);
					$mapModel->loadAdditionalData();
					$mapModel->saveInYf();
					$dataYf['id'] = $this->imported[$row['id']] = $mapModel->getRecordModel()->getId();
				}
				$this->updateMapIdCache($mapModel->getModule(), $row['id'], $yfId ?: $mapModel->getRecordModel()->getId());
			} catch (\Throwable $ex) {
				$this->controller->log('Import product', ['YF' => $dataYf, 'API' => $row], $ex);
				\App\Log::error('Error during import product: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
			}
		} else {
			\App\Log::error('Empty map product details', self::LOG_CATEGORY);
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('Import product | ' . (\array_key_exists($row['id'], $this->imported) ? 'imported' : 'skipped'), [
				'API' => $row,
				'YF' => $dataYf ?? [],
			]);
		}
	}

	/**
	 * Export products to WooCommerce.
	 *
	 * @return void
	 */
	public function export(): void
	{
		$this->lastScan = $this->config->getLastScan('exportProduct');
		if (
			!$this->lastScan['start_date']
			|| (0 === $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])
		) {
			$this->config->setScan('exportProduct');
			$this->lastScan = $this->config->getLastScan('exportProduct');
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('Start export product', [
				'lastScan' => $this->lastScan,
			]);
		}
		$i = 0;
		try {
			$page = $this->lastScan['page'] ?? 0;
			$load = true;
			$finish = false;
			$query = $this->getExportQuery();
			$limit = $this->config->get('products_limit');
			while ($load) {
				$query->offset($page);
				if ($rows = $query->all()) {
					foreach ($rows as $row) {
						$this->exportProduct($row['id']);
						$this->config->setScan('exportProduct', 'id', $row['id']);
						++$i;
					}
					++$page;
					if (\is_callable($this->controller->bathCallback)) {
						$load = \call_user_func($this->controller->bathCallback, 'exportProduct');
					}
					if ($limit !== \count($rows)) {
						$finish = true;
					}
				} else {
					$finish = true;
				}
				if ($finish || !$load) {
					$load = false;
					if ($finish) {
						$this->config->setEndScan('exportProduct', $this->lastScan['start_date']);
					} else {
						$this->config->setScan('exportProduct', 'page', $page);
					}
				}
			}
		} catch (\Throwable $ex) {
			$this->controller->log('Export products', null, $ex);
			\App\Log::error('Error during export products: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('End export product', ['exported' => $i]);
		}
	}

	/**
	 * Export product.
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public function exportProduct(int $id): void
	{
		$mapModel = $this->getMapModel();
		$mapModel->setDataYfById($id);
		$mapModel->setDataApi([]);
		$row = $mapModel->getDataYf('fieldMap', false);
		$mapModel->isVariation = 'PLL_TYPE_VARIATION' === $row['product_type'];
		if ($dataApi = $mapModel->getDataApi()) {
			try {
				if (empty($row['woocommerce_id']) || empty($this->imported[$row['woocommerce_id']])) {
					$mapModel->saveInApi();
					$dataApi['id'] = $this->exported[$row['id']] = $mapModel->getRecordModel()->get('woocommerce_id');
				}
				$this->updateMapIdCache(
					$mapModel->getRecordModel()->getModuleName(),
					$mapModel->getRecordModel()->get('woocommerce_id'),
					$row['id']
				);
			} catch (\Throwable $ex) {
				$this->controller->log('Export product', ['YF' => $row, 'API' => $dataApi], $ex);
				\App\Log::error('Error during export product: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
			}
		} else {
			\App\Log::error('Empty map product details', self::LOG_CATEGORY);
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('Export product | ' . (\array_key_exists($row['id'], $this->exported) ? 'exported' : 'skipped'), [
				'YF' => $row,
				'API' => $dataApi ?? [],
			]);
		}
	}

	/**
	 * Import by API id.
	 *
	 * @param int $apiId
	 *
	 * @return int
	 */
	public function importById(int $apiId): int
	{
		$id = 0;
		try {
			$row = $this->getFromApi('products/' . $apiId);
			if ($row) {
				$this->importProduct($row);
				$id = $this->imported[$row['id']] ?? 0;
			} else {
				$this->controller->log('Import product by id [Empty details]', ['apiId' => $apiId]);
				\App\Log::error('Import during export product: Empty details', self::LOG_CATEGORY);
			}
		} catch (\Throwable $ex) {
			$this->controller->log('Import product by id', ['apiId' => $apiId, 'API' => $row], $ex);
			\App\Log::error('Error during import by ean product: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
		}
		return $id;
	}

	/**
	 * Get export query.
	 *
	 * @return \App\Db\Query
	 */
	private function getExportQuery(): \App\Db\Query
	{
		$mapModel = $this->getMapModel();
		$queryGenerator = $this->getFromYf($mapModel->getModule());
		$queryGenerator->setFields(['id']);
		$queryGenerator->setLimit($this->config->get('products_limit'));
		$queryGenerator->addCondition('product_type', $mapModel->productType, 'e');
		$query = $queryGenerator->createQuery();
		if (!empty($this->lastScan['start_date'])) {
			$query->andWhere(['<', 'vtiger_crmentity.modifiedtime', $this->lastScan['start_date']]);
		}
		if (!empty($this->lastScan['end_date'])) {
			$query->andWhere(['>', 'vtiger_crmentity.modifiedtime', $this->lastScan['end_date']]);
		}
		return $query;
	}
}
