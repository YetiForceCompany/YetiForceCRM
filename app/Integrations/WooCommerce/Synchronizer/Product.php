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
			|| (0 === (int) $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])
		) {
			$this->config->setScan('importProduct');
			$this->lastScan = $this->config->getLastScan('importProduct');
		}
		if ($this->config->get('logAll')) {
			$this->log('Start import product', [
				'lastScan' => $this->lastScan,
			]);
		}
		$i = 0;
		try {
			$page = 1;
			$load = true;
			$limit = $this->config->get('products_limit');
			while ($load) {
				if ($rows = $this->getFromApi('products?&page=' . $page . '&' . $this->getSearchCriteria($limit))) {
					foreach ($rows as $id => $row) {
						$this->importProduct($row);
						$this->config->setScan('importProduct', 'id', $id);
						++$i;
					}
					++$page;
					if ($this->config->get('products_limit') !== \count($rows)) {
						$load = false;
						$this->config->setEndScan('importProduct', $this->lastScan['start_date']);
					}
				} else {
					$load = false;
					$this->config->setEndScan('importProduct', $this->lastScan['start_date']);
				}
			}
		} catch (\Throwable $ex) {
			$this->log('Import products', null, $ex);
			\App\Log::error('Error during import products: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
		}
		if ($this->config->get('logAll')) {
			$this->log('End import product', ['imported' => $i]);
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
		$mapModel->setDataApi($row);
		if ($dataYf = $mapModel->getDataYf()) {
			try {
				$yfId = $this->getYfId($row['id']);
				if (empty($yfId) || ($this->config->get('master') && empty($this->exported[$yfId]))) {
					$mapModel->loadRecordModel($yfId);
					$mapModel->saveInYf();
					$this->imported[$row['id']] = $mapModel->getRecordModel()->getId();
				}
				$this->updateMapIdCache($mapModel->getModule(), $row['id'], $mapModel->getRecordModel()->getId());
			} catch (\Throwable $ex) {
				$this->log('Import product', ['YF' => $dataYf, 'API' => $row], $ex);
				\App\Log::error('Error during import product: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
			}
		} else {
			\App\Log::error('Empty map product details', self::LOG_CATEGORY);
		}
		if ($this->config->get('logAll')) {
			$this->log('Import product', [
				'API' => $row,
				'YF' => $dataYf ?? [],
				'imported' => \array_key_exists($row['id'], $this->imported) ? 1 : 0,
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
			|| (0 === (int) $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])
		) {
			$this->config->setScan('exportProduct');
			$this->lastScan = $this->config->getLastScan('exportProduct');
		}
		if ($this->config->get('logAll')) {
			$this->log('Start export product', [
				'lastScan' => $this->lastScan,
			]);
		}
		$i = 0;
		try {
			$page = 0;
			$load = true;
			$query = $this->getExportQuery();
			$limit = $this->config->get('products_limit');
			while ($load) {
				$query->offset($page);
				if ($rows = $query->all()) {
					foreach ($rows as $id => $row) {
						$this->exportProduct($row);
						$this->config->setScan('exportProduct', 'id', $id);
						++$i;
					}
					++$page;
					if ($limit !== \count($rows)) {
						$load = false;
						$this->config->setEndScan('exportProduct', $this->lastScan['start_date']);
					}
				} else {
					$load = false;
					$this->config->setEndScan('exportProduct', $this->lastScan['start_date']);
				}
			}
		} catch (\Throwable $ex) {
			$this->log('Export products', null, $ex);
			\App\Log::error('Error during export products: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
		}
		if ($this->config->get('logAll')) {
			$this->log('End export product', ['exported' => $i]);
		}
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
		$queryGenerator->setFields(array_merge(
			['id', 'modifiedtime'],
			array_keys($mapModel->getFields()),
			$mapModel->getAttributesMapFields()
		));
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

	/**
	 * Export product.
	 *
	 * @param array $row
	 *
	 * @return void
	 */
	public function exportProduct(array $row): void
	{
		$mapModel = $this->getMapModel();
		$mapModel->setDataYf($row, true);
		$mapModel->setDataApi([]);
		if ($dataApi = $mapModel->getDataApi()) {
			try {
				if (
					empty($row['woocommerce_id'])
					|| (!$this->config->get('master') && empty($this->imported[$row['woocommerce_id']]))
				) {
					$mapModel->saveInApi();
					$this->exported[$row['id']] = $mapModel->getRecordModel()->get('woocommerce_id');
				}
				$this->updateMapIdCache(
					$mapModel->getRecordModel()->getModuleName(),
					$mapModel->getRecordModel()->get('woocommerce_id'),
					$mapModel->getRecordModel()->getId()
				);
			} catch (\Throwable $ex) {
				$this->log('Export product', ['YF' => $row, 'API' => $dataApi], $ex);
				\App\Log::error('Error during export product: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
			}
		} else {
			\App\Log::error('Empty map product details', self::LOG_CATEGORY);
		}
		if ($this->config->get('logAll')) {
			$this->log('Export product', [
				'YF' => $row,
				'API' => $dataApi ?? [],
				'exported' => \array_key_exists($row['id'], $this->exported) ? 1 : 0,
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
				$this->log('Import product by id [Empty details]', ['apiId' => $apiId]);
				\App\Log::error('Import during export product: Empty details', self::LOG_CATEGORY);
			}
		} catch (\Throwable $ex) {
			$this->log('Import product by id', ['apiId' => $apiId, 'API' => $row], $ex);
			\App\Log::error('Error during import by ean product: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
		}
		return $id;
	}
}
