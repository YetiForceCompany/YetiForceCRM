<?php

/**
 * WooCommerce orders synchronization file.
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
 * WooCommerce orders synchronization class.
 */
class Orders extends Base
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
			$direction = (int) $this->config->get('direction_orders');
			if (self::DIRECTION_TWO_WAY === $direction || self::DIRECTION_API_TO_YF === $direction) {
				$this->import();
			}
			if (self::DIRECTION_TWO_WAY === $direction || self::DIRECTION_YF_TO_API === $direction) {
				$this->export();
			}
		}
	}

	/**
	 * Import orders from WooCommerce.
	 *
	 * @return void
	 */
	public function import(): void
	{
		$this->lastScan = $this->config->getLastScan('importOrders');
		if (
			!$this->lastScan['start_date']
			|| (0 === $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])
		) {
			$this->config->setScan('importOrders');
			$this->lastScan = $this->config->getLastScan('importOrders');
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('Start import order', [
				'lastScan' => $this->lastScan,
			]);
		}
		$i = 0;
		try {
			$page = $this->lastScan['page'] ?? 1;
			$load = true;
			$finish = false;
			$limit = $this->config->get('orders_limit');
			while ($load) {
				if ($rows = $this->getFromApi('orders?&page=' . $page . '&' . $this->getSearchCriteria($limit))) {
					foreach ($rows as $id => $row) {
						$this->importOrder($row);
						$this->config->setScan('importOrders', 'id', $id);
						++$i;
					}
					++$page;
					if (\is_callable($this->controller->bathCallback)) {
						$load = \call_user_func($this->controller->bathCallback, 'importOrders');
					}
					if ($this->config->get('orders_limit') !== \count($rows)) {
						$finish = true;
					}
				} else {
					$finish = true;
				}
				if ($finish || !$load) {
					$load = false;
					if ($finish) {
						$this->config->setEndScan('importOrders', $this->lastScan['start_date']);
					} else {
						$this->config->setScan('importOrders', 'page', $page);
					}
				}
			}
		} catch (\Throwable $ex) {
			$this->controller->log('Import orders', null, $ex);
			\App\Log::error('Error during import orders: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('End import orders', ['imported' => $i]);
		}
	}

	/**
	 * Import order.
	 *
	 * @param array $row
	 *
	 * @return void
	 */
	public function importOrder(array $row): void
	{
		$mapModel = $this->getMapModel();
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
			} catch (\Throwable $ex) {
				$this->controller->log('Import order', ['YF' => $dataYf, 'API' => $row], $ex);
				\App\Log::error('Error during import order: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
			}
		} else {
			\App\Log::error('Empty map order details', self::LOG_CATEGORY);
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('Import order | ' . (\array_key_exists($row['id'], $this->imported) ? 'imported' : 'skipped'), [
				'API' => $row,
				'YF' => $dataYf ?? [],
			]);
		}
	}

	/**
	 * Export orders to WooCommerce.
	 *
	 * @return void
	 */
	public function export(): void
	{
		$this->lastScan = $this->config->getLastScan('exportOrders');
		if (
			!$this->lastScan['start_date']
			|| (0 === $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])
		) {
			$this->config->setScan('exportOrders');
			$this->lastScan = $this->config->getLastScan('exportOrders');
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('Start export order', [
				'lastScan' => $this->lastScan,
			]);
		}
		$i = 0;
		try {
			$page = $this->lastScan['page'] ?? 0;
			$load = true;
			$finish = false;
			$query = $this->getExportQuery();
			$limit = $this->config->get('orders_limit');
			while ($load) {
				$query->offset($page);
				if ($rows = $query->all()) {
					foreach ($rows as $id => $row) {
						$this->exportOrder($row);
						$this->config->setScan('exportOrders', 'id', $id);
						++$i;
					}
					++$page;
					if (\is_callable($this->controller->bathCallback)) {
						$load = \call_user_func($this->controller->bathCallback, 'exportOrders');
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
						$this->config->setEndScan('exportOrders', $this->lastScan['start_date']);
					} else {
						$this->config->setScan('exportOrders', 'page', $page);
					}
				}
			}
		} catch (\Throwable $ex) {
			$this->controller->log('Export orders', null, $ex);
			\App\Log::error('Error during export orders: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('End export order', ['exported' => $i]);
		}
	}

	/**
	 * Export order.
	 *
	 * @param array $row
	 *
	 * @return void
	 */
	public function exportOrder(array $row): void
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
					$dataApi['id'] = $this->exported[$row['id']] = $mapModel->getRecordModel()->get('woocommerce_id');
				}
			} catch (\Throwable $ex) {
				$this->controller->log('Export order', ['YF' => $row, 'API' => $dataApi], $ex);
				\App\Log::error('Error during export order: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
			}
		} else {
			\App\Log::error('Empty map order details', self::LOG_CATEGORY);
		}
		if ($this->config->get('logAll')) {
			$this->controller->log('Export order | ' . (\array_key_exists($row['id'], $this->exported) ? 'exported' : 'skipped'), [
				'YF' => $row,
				'API' => $dataApi ?? [],
			]);
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
		$queryGenerator->setFields(['id', 'modifiedtime', 'woocommerce_id', 'ssingleorders_status']);
		$queryGenerator->setLimit($this->config->get('products_limit'));
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
