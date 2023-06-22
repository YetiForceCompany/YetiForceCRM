<?php

/**
 * Comarch accounts synchronization file.
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

namespace App\Integrations\Comarch\Xl\Synchronizer;

/**
 * Comarch accounts synchronization class.
 */
class Accounts extends \App\Integrations\Comarch\Synchronizer
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
			$direction = (int) $this->config->get('direction_accounts');
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
	 * Import accounts from Comarch.
	 *
	 * @return void
	 */
	public function import(): void
	{
		//Not implemented and tested method
		// $this->lastScan = $this->config->getLastScan('import' . $this->name);
		// if (
		// 	!$this->lastScan['start_date']
		// 	|| (0 === $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])
		// ) {
		// 	$this->config->setScan('import' . $this->name);
		// 	$this->lastScan = $this->config->getLastScan('import' . $this->name);
		// }
		// if ($this->config->get('log_all')) {
		// 	$this->controller->log('Start import ' . $this->name, [
		// 		'lastScan' => $this->lastScan,
		// 	]);
		// }
		// $i = 0;
		// try {
		// 	$page = $this->lastScan['page'] ?? 1;
		// 	$load = true;
		// 	$finish = false;
		// 	$limit = $this->config->get('accounts_limit');
		// 	while ($load) {
		// 		if ($rows = $this->getFromApi('products?&page=' . $page . '&' . $this->getSearchCriteria($limit))) {
		// 			foreach ($rows as $id => $row) {
		// 				$this->importItem($row);
		// 				$this->config->setScan('import' . $this->name, 'id', $id);
		// 				++$i;
		// 			}
		// 			++$page;
		// 			if (\is_callable($this->controller->bathCallback)) {
		// 				$load = \call_user_func($this->controller->bathCallback, 'import' . $this->name);
		// 			}
		// 			if ($this->config->get('accounts_limit') !== \count($rows)) {
		// 				$finish = true;
		// 			}
		// 		} else {
		// 			$finish = true;
		// 		}
		// 		if ($finish || !$load) {
		// 			$load = false;
		// 			if ($finish) {
		// 				$this->config->setEndScan('import' . $this->name, $this->lastScan['start_date']);
		// 			} else {
		// 				$this->config->setScan('import' . $this->name, 'page', $page);
		// 			}
		// 		}
		// 	}
		// } catch (\Throwable $ex) {
		// 	$this->controller->log('Import ' . $this->name, null, $ex);
		// 	\App\Log::error("Error during import {$this->name}: \n{$ex->__toString()}", self::LOG_CATEGORY);
		// }
		// if ($this->config->get('log_all')) {
		// 	$this->controller->log('End import ' . $this->name, ['imported' => $i]);
		// }
	}

	/**
	 * Import account from Comarch to YetiFoce.
	 *
	 * @param array $row
	 *
	 * @return void
	 */
	public function importItem(array $row): void
	{
		// $mapModel = $this->getMapModel();
		// $mapModel->setDataApi($row);
		// if ($dataYf = $mapModel->getDataYf()) {
		// 	try {
		// 		$yfId = $this->getYfId($row['id']);
		// 		if (empty($yfId) || empty($this->exported[$yfId])) {
		// 			$mapModel->loadRecordModel($yfId);
		// 			$mapModel->loadAdditionalData();
		// 			$mapModel->saveInYf();
		// 			$dataYf['id'] = $this->imported[$row['id']] = $mapModel->getRecordModel()->getId();
		// 		}
		// 		$this->updateMapIdCache($mapModel->getModule(), $row['id'], $yfId ?: $mapModel->getRecordModel()->getId());
		// 	} catch (\Throwable $ex) {
		// 		$this->controller->log(__FUNCTION__, ['YF' => $dataYf, 'API' => $row], $ex);
		// 		\App\Log::error('Error during ' . __FUNCTION__ . ': ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
		// 	}
		// } else {
		// 	\App\Log::error('Empty map details in ' . __FUNCTION__, self::LOG_CATEGORY);
		// }
		// if ($this->config->get('log_all')) {
		// 	$this->controller->log(__FUNCTION__ . ' | ' . (\array_key_exists($row['id'], $this->imported) ? 'imported' : 'skipped'), [
		// 		'API' => $row,
		// 		'YF' => $dataYf ?? [],
		// 	]);
		// }
	}

	/**
	 * Export accounts to Comarch.
	 *
	 * @return void
	 */
	public function export(): void
	{
		$this->lastScan = $this->config->getLastScan('export' . $this->name);
		if (
			!$this->lastScan['start_date']
			|| (0 === $this->lastScan['id'] && $this->lastScan['start_date'] === $this->lastScan['end_date'])
		) {
			$this->config->setScan('export' . $this->name);
			$this->lastScan = $this->config->getLastScan('export' . $this->name);
		}
		if ($this->config->get('log_all')) {
			$this->controller->log('Start export ' . $this->name, [
				'lastScan' => $this->lastScan,
			]);
		}
		$i = 0;
		try {
			$page = $this->lastScan['page'] ?? 0;
			$load = true;
			$finish = false;
			$query = $this->getExportQuery();
			$limit = $this->config->get('accounts_limit');
			while ($load) {
				$query->offset($page);
				if ($rows = $query->all()) {
					foreach ($rows as $row) {
						$this->exportItem($row['id']);
						$this->config->setScan('export' . $this->name, 'id', $row['id']);
						++$i;
					}
					++$page;
					if (\is_callable($this->controller->bathCallback)) {
						$load = \call_user_func($this->controller->bathCallback, 'export' . $this->name);
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
						$this->config->setEndScan('export' . $this->name, $this->lastScan['start_date']);
					} else {
						$this->config->setScan('export' . $this->name, 'page', $page);
					}
				}
			}
		} catch (\Throwable $ex) {
			$this->controller->log('Export ' . $this->name, ['API' => $rows ?? ''], $ex);
			\App\Log::error("Error during export {$this->name}: \n{$ex->__toString()}", self::LOG_CATEGORY);
		}
		if ($this->config->get('log_all')) {
			$this->controller->log('End export ' . $this->name, ['exported' => $i]);
		}
	}

	/**
	 * Get export query.
	 *
	 * @return \App\Db\Query
	 */
	private function getExportQuery(): \App\Db\Query
	{
		$queryGenerator = $this->getFromYf($this->getMapModel()->getModule(), true);
		$queryGenerator->setLimit($this->config->get('accounts_limit'));
		return $queryGenerator->createQuery();
	}

	/**
	 * Export account to Comarch from YetiFoce.
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public function exportItem(int $id): void
	{
		$mapModel = $this->getMapModel();
		$mapModel->setDataApi([]);
		$mapModel->setDataYfById($id);
		$mapModel->loadModeApi();
		$row = $mapModel->getDataYf('fieldMap', false);
		$dataApi = $mapModel->getDataApi();
		if ($mapModel->skip) {
			if ($this->config->get('log_all')) {
				$this->controller->log(__FUNCTION__ . ' | skipped , inconsistent data', ['YF' => $row, 'API' => $dataApi ?? []]);
			}
		} elseif (empty($dataApi)) {
			\App\Log::error(__FUNCTION__ . ' | Empty map details', self::LOG_CATEGORY);
			$this->controller->log(__FUNCTION__ . ' | Empty map details', ['YF' => $row, 'API' => $dataApi ?? []], null, true);
		} else {
			// print_r($dataApi);
			try {
				if ('create' === $mapModel->getModeApi() || empty($this->imported[$row[$mapModel::FIELD_NAME_ID]])) {
					$mapModel->saveInApi();
					$dataApi = $mapModel->getDataApi(false);
				} else {
					$this->updateMapIdCache(
						$mapModel->getRecordModel()->getModuleName(),
						$mapModel->getRecordModel()->get($mapModel::FIELD_NAME_ID),
						$row['id']
					);
				}
			} catch (\Throwable $ex) {
				print_r($ex->__toString());
				print_r(['YF' => $row, 'API' => $dataApi]);
				$this->controller->log(__FUNCTION__, ['YF' => $row, 'API' => $dataApi], $ex);
				\App\Log::error('Error during ' . __FUNCTION__ . ': ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
			}
		}

		exit;
		if ($this->config->get('log_all')) {
			$this->controller->log(__FUNCTION__ . ' | ' . (\array_key_exists($row['id'], $this->exported) ? 'exported' : 'skipped'), [
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
				$this->importItem($row);
				$id = $this->imported[$row['id']] ?? 0;
			} else {
				$this->controller->log("Import {$this->name} by id [Empty details]", ['apiId' => $apiId]);
				\App\Log::error("Import during export {$this->name}: Empty details", self::LOG_CATEGORY);
			}
		} catch (\Throwable $ex) {
			$this->controller->log("Import {$this->name} by id", ['apiId' => $apiId, 'API' => $row], $ex);
			\App\Log::error("Error during import by id {$this->name}: \n{$ex->__toString()}", self::LOG_CATEGORY);
		}
		return $id;
	}

	/** {@inheritdoc} */
	public function getApiId(int $yfId, ?string $moduleName = null): int
	{
		// $moduleName = $moduleName ?? $this->getMapModel()->getModule();
		// $cacheKey = 'Integrations/Comarch/API_ID/' . $moduleName;
		// if (\App\Cache::staticHas($cacheKey, $yfId)) {
		// 	return \App\Cache::staticGet($cacheKey, $yfId);
		// }
		// $apiId = 0;
		// try {
		// 	$recordModel = \Vtiger_Record_Model::getInstanceById($yfId, $moduleName);
		// 	$apiId = $recordModel->get('comarch_id') ?: 0;
		// } catch (\Throwable $th) {
		// 	$this->controller->log('GetApiId', ['comarch_id' => $yfId, 'moduleName' => $moduleName], $th);
		// 	\App\Log::error('Error GetApiId: ' . PHP_EOL . $th->__toString(), self::LOG_CATEGORY);
		// }
		// $this->updateMapIdCache($moduleName, $apiId, $yfId);
		return $apiId;
	}
}
