<?php

/**
 * WooCommerce abstract base method synchronization file.
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

namespace App\Integrations\WooCommerce\Synchronizer\Maps;

/**
 * WooCommerce abstract base method synchronization class.
 */
abstract class Base
{
	/** @var string Map module name. */
	protected $moduleName;
	/** @var array Mapped fields. */
	protected $fieldMap = [];
	/** @var array Data from WooCommerce. */
	protected $dataApi = [];
	/** @var array Default data from WooCommerce. */
	protected $defaultDataApi = [];
	/** @var array Data from YetiForce. */
	protected $dataYf = [];
	/** @var array Default data from YetiForce. */
	protected $defaultDataYf = [];
	/** @var \App\Integrations\WooCommerce\Synchronizer\Base Synchronizer instance */
	protected $synchronizer;
	/** @var \Vtiger_Module_Model Module model instance */
	protected $moduleModel;
	/** @var \Vtiger_Record_Model Record model instance */
	protected $recordModel;

	/** @var string[] Mapped address fields. */
	protected $addressMapFields = [
		'addresslevel1' => ['name' => 'country', 'fn' => 'convertCountry'],
		'addresslevel2' => 'state',
		'addresslevel5' => 'city',
		'addresslevel7' => 'postcode',
		'addresslevel8' => 'address_1',
		'buildingnumber' => 'address_2',
		'first_name_' => 'first_name',
		'last_name_' => 'last_name',
		'phone_' => ['name' => 'phone', 'fn' => 'convertPhone'],
		'email_' => 'email',
		'company_name_' => 'company',
	];

	/**
	 * Constructor.
	 *
	 * @param \App\Integrations\WooCommerce\Synchronizer\Base $synchronizer
	 */
	public function __construct(\App\Integrations\WooCommerce\Synchronizer\Base $synchronizer)
	{
		$this->synchronizer = $synchronizer;
		$this->moduleModel = \Vtiger_Module_Model::getInstance($this->moduleName);
	}

	/**
	 * Set data from/for API.
	 *
	 * @param array $data
	 */
	public function setDataApi(array $data): void
	{
		if ($data) {
			$this->dataApi = $data;
		} else {
			$this->dataApi = $this->defaultDataApi;
		}
	}

	/**
	 * Set data from/for YetiForce. YetiForce data is read-only.
	 *
	 * @param array $data
	 * @param bool  $updateRecordModel
	 *
	 * @return void
	 */
	public function setDataYf(array $data, bool $updateRecordModel = false): void
	{
		$this->dataYf = $data;
		if ($updateRecordModel) {
			$this->recordModel = \Vtiger_Module_Model::getInstance($this->moduleName)->getRecordFromArray($data);
		}
	}

	/**
	 * Set data from/for YetiForce by record ID. Read/Write YetiForce data.
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public function setDataYfById(int $id): void
	{
		$this->recordModel = \Vtiger_Record_Model::getInstanceById($id, $this->moduleName);
		$this->dataYf = $this->recordModel->getData();
	}

	/**
	 * Load record model.
	 *
	 * @param int $id
	 */
	public function loadRecordModel(int $id): void
	{
		if ($id) {
			$this->recordModel = \Vtiger_Record_Model::getInstanceById($id, $this->moduleName);
		} else {
			$this->recordModel = \Vtiger_Record_Model::getCleanInstance($this->moduleName);
		}
	}

	/**
	 * Get record model.
	 *
	 * @return \Vtiger_Record_Model
	 */
	public function getRecordModel(): \Vtiger_Record_Model
	{
		return $this->recordModel;
	}

	/**
	 * Get module name.
	 *
	 * @return string
	 */
	public function getModule(): string
	{
		return $this->moduleName;
	}

	/**
	 * Return fields list.
	 *
	 * @return array
	 */
	public function getFields(): array
	{
		return $this->fieldMap;
	}

	/**
	 * Return parsed data in YetiForce format.
	 *
	 * @param string $type
	 * @param bool   $mapped
	 *
	 * @return array
	 */
	public function getDataYf(string $type = 'fieldMap', bool $mapped = true): array
	{
		if ($mapped) {
			$this->dataYf = $this->defaultDataYf[$type] ?? [];
			$this->parseMetaDataFromApi();
			foreach ($this->{$type} as $fieldCrm => $field) {
				if (\is_array($field)) {
					if (!empty($field['direction']) && 'api' === $field['direction']) {
						continue;
					}
					$field['fieldCrm'] = $fieldCrm;

					if (\is_array($field['name'])) {
						$this->loadDataYfMultidimensional($fieldCrm, $field);
					} elseif (\array_key_exists($field['name'], $this->dataApi)) {
						$this->loadDataYfMap($fieldCrm, $field);
					} elseif (!\array_key_exists('optional', $field) || empty($field['optional'])) {
						$error = "[API>YF][1] No column {$field['name']} ($fieldCrm)";
						\App\Log::warning($error, $this->synchronizer::LOG_CATEGORY);
						$this->synchronizer->controller->log($error, ['fieldConfig' => $field, 'data' => $this->dataApi], null, true);
					}
				} else {
					$this->dataYf[$fieldCrm] = $this->dataApi[$field] ?? null;
					if (!\array_key_exists($field, $this->dataApi)) {
						$error = "[API>YF][2] No column $field ($fieldCrm)";
						\App\Log::warning($error, $this->synchronizer::LOG_CATEGORY);
						$this->synchronizer->controller->log($error, $this->dataApi, null, true);
					}
				}
			}
		}
		return $this->dataYf;
	}

	/**
	 * Create/update product in YF.
	 *
	 * @return void
	 */
	public function saveInYf(): void
	{
		foreach ($this->dataYf as $key => $value) {
			$this->recordModel->set($key, $value);
		}
		if ($this->recordModel->isEmpty('assigned_user_id')) {
			$this->recordModel->set('assigned_user_id', $this->synchronizer->config->get('assigned_user_id'));
		}
		if (
			$this->recordModel->isEmpty('woocommerce_id')
			&& $this->recordModel->getModule()->getFieldByName('woocommerce_id')
			&& !empty($this->dataApi['id'])
		) {
			$this->recordModel->set('woocommerce_id', $this->dataApi['id']);
		}
		$this->recordModel->set('woocommerce_server_id', $this->synchronizer->config->get('id'));
		$isNew = empty($this->recordModel->getId());
		$this->recordModel->save();
		$this->recordModel->ext['isNew'] = $isNew;
		if ($isNew && $this->recordModel->get('woocommerce_id')) {
			$this->synchronizer->updateMapIdCache(
				$this->recordModel->getModuleName(),
				$this->recordModel->get('woocommerce_id'),
				$this->recordModel->getId()
			);
		}
	}

	/**
	 * Return parsed data in YetiForce format.
	 *
	 * @param bool $mapped
	 *
	 * @return array
	 */
	public function getDataApi(bool $mapped = true): array
	{
		if ($mapped) {
			if (!empty($this->dataYf['woocommerce_id'])) {
				$this->dataApi['id'] = $this->dataYf['woocommerce_id'];
			}
			foreach ($this->fieldMap as $fieldCrm => $field) {
				if (\is_array($field)) {
					if (!empty($field['direction']) && 'yf' === $field['direction']) {
						continue;
					}
					if (\array_key_exists($fieldCrm, $this->dataYf)) {
						$field['fieldCrm'] = $fieldCrm;
						if (isset($field['map'])) {
							$mapValue = array_search($this->dataYf[$fieldCrm], $field['map']);
							if (false !== $mapValue) {
								$this->setApiData($mapValue, $field);
							} elseif (empty($field['mayNotExist'])) {
								$error = "[YF>API] No value `{$this->dataYf[$fieldCrm]}` in map {$fieldCrm}";
								\App\Log::warning($error, $this->synchronizer::LOG_CATEGORY);
								$this->synchronizer->controller->log($error, ['fieldConfig' => $field, 'data' => $this->dataYf], null, true);
							}
						} elseif (isset($field['fn'])) {
							$this->setApiData($this->{$field['fn']}($this->dataYf[$fieldCrm], $field, false), $field);
						} else {
							$this->setApiData($this->dataYf[$fieldCrm], $field);
						}
					} elseif (!\array_key_exists('optional', $field) || empty($field['optional'])) {
						$error = '[YF>API] No field ' . $fieldCrm;
						\App\Log::warning($error, $this->synchronizer::LOG_CATEGORY);
						$this->synchronizer->controller->log($error, ['fieldConfig' => $field, 'data' => $this->dataYf], null, true);
					}
				} else {
					$this->dataApi[$field] = $this->dataYf[$fieldCrm] ?? null;
					if (!\array_key_exists($fieldCrm, $this->dataYf)) {
						$error = '[YF>API] No field ' . $fieldCrm;
						\App\Log::warning($error, $this->synchronizer::LOG_CATEGORY);
						$this->synchronizer->controller->log($error, ['fieldConfig' => $field, 'data' => $this->dataYf], null, true);
					}
				}
			}
			$this->parseMetaDataToApi();
		}
		return $this->dataApi;
	}

	/**
	 * Set the data to in the appropriate key structure.
	 *
	 * @param mixed $value
	 * @param array $field
	 *
	 * @return void
	 */
	public function setApiData($value, array $field): void
	{
		if (\is_array($field['name'])) {
			foreach (array_reverse($field['name']) as $name) {
				$value = [$name => $value];
			}
			$this->dataApi = \App\Utils::merge($this->dataApi, $value);
		} else {
			$this->dataApi[$field['name']] = $value;
		}
	}

	/**
	 * Create/update product by API.
	 *
	 * @return void
	 */
	public function saveInApi(): void
	{
		throw new \App\Exceptions\AppException('Method not implemented');
	}

	/**
	 * Save record in YF from relation action.
	 *
	 * @param array $field
	 *
	 * @return int
	 */
	public function saveFromRelation(array $field): int
	{
		$id = 0;
		if ($dataYf = $this->getDataYf()) {
			try {
				$id = $this->findRecordInYf();
				if (empty($field['onlyCreate']) || empty($id)) {
					$this->loadRecordModel($id);
					$this->loadAdditionalData();
					$this->saveInYf();
					$id = $this->getRecordModel()->getId();
				}
			} catch (\Throwable $ex) {
				$error = "[API>YF] Import {$this->moduleName}";
				\App\Log::warning($error . "\n" . $ex->getMessage(), $this->synchronizer::LOG_CATEGORY);
				$this->synchronizer->controller->log($error, ['YF' => $dataYf, 'API' => $this->dataApi], $ex);
			}
		}
		return $id;
	}

	/**
	 * Load additional data.
	 *
	 * @return void
	 */
	public function loadAdditionalData(): void
	{
	}

	/**
	 * Parse data to YetiForce format from multidimensional array.
	 *
	 * @param string $fieldCrm
	 * @param array  $field
	 *
	 * @return void
	 */
	protected function loadDataYfMultidimensional(string $fieldCrm, array $field): void
	{
		$value = $this->dataApi;
		$field['fieldCrm'] = $fieldCrm;
		foreach ($field['name'] as $name) {
			if (\array_key_exists($name, $value)) {
				$value = $value[$name];
			} else {
				$error = "[API>YF][3] No column $name ($fieldCrm)";
				if (!\array_key_exists('optional', $field) || empty($field['optional'])) {
					\App\Log::warning($error, $this->synchronizer::LOG_CATEGORY);
					$this->synchronizer->controller->log($error, ['fieldConfig' => $field, 'data' => $this->dataApi], null, true);
				}
			}
		}
		if (empty($error)) {
			$this->loadDataYfMap($fieldCrm, $field, $value);
		}
	}

	/**
	 * Parse data to YetiForce format from map.
	 *
	 * @param string     $fieldCrm
	 * @param array      $field
	 * @param mixed|null $value
	 *
	 * @return void
	 */
	protected function loadDataYfMap(string $fieldCrm, array $field, $value = null): void
	{
		$value ??= $this->dataApi[$field['name']];
		if (isset($field['map'])) {
			if (\array_key_exists($value, $field['map'])) {
				$this->dataYf[$fieldCrm] = $field['map'][$value];
			} elseif (empty($field['mayNotExist'])) {
				$value = print_r($value, true);
				$error = "[API>YF] No value `{$value}` in map {$field['name']}";
				\App\Log::warning($error, $this->synchronizer::LOG_CATEGORY);
				$this->synchronizer->controller->log($error, ['fieldConfig' => $field, 'data' => $this->dataApi], null, true);
			}
		} elseif (isset($field['fn'])) {
			$this->dataYf[$fieldCrm] = $this->{$field['fn']}($value, $field, true);
		} else {
			$this->dataYf[$fieldCrm] = $value;
		}
	}

	/**
	 * Convert bool to system format.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return int|bool int (YF) or bool (API)
	 */
	protected function convertBool($value, array $field, bool $fromApi)
	{
		if ($fromApi) {
			return $value ? 1 : 0;
		}
		return 1 == $value;
	}

	/**
	 * Convert date time to system format.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return string Date time Y-m-d H:i:s (YF) or Y-m-d\TH:i:s (API)
	 */
	protected function convertDateTime($value, array $field, bool $fromApi): string
	{
		if ($fromApi) {
			return \DateTimeField::convertTimeZone($value, 'UTC', \App\Fields\DateTime::getTimeZone())
				->format('Y-m-d H:i:s');
		}
		return \DateTimeField::convertTimeZone($value, \App\Fields\DateTime::getTimeZone(), 'UTC')
			->format('Y-m-d\TH:i:s');
	}

	/**
	 * Convert date to system format.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return string
	 */
	protected function convertDate($value, array $field, bool $fromApi)
	{
		return date('Y-m-d', strtotime($value));
	}

	/**
	 * Convert price to system format.
	 *
	 * @param array $field
	 * @param mixed $value
	 * @param bool  $fromApi
	 *
	 * @return string|float JSON (YF) or string (API)
	 */
	protected function convertPrice($value, array $field, bool $fromApi)
	{
		$currency = $this->synchronizer->config->get('currency_id');
		if ($fromApi) {
			return \App\Json::encode([
				'currencies' => [
					$currency => ['price' => $value ?: 0]
				],
				'currencyId' => $currency
			]);
		}
		$value = \App\Json::decode($value);
		return (string) (empty($value['currencies'][$currency]) ? 0 : $value['currencies'][$currency]['price']);
	}

	/**
	 * Convert price to system format.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return mixed
	 */
	protected function convert($value, array $field, bool $fromApi)
	{
		switch ($field[$fromApi ? 'crmType' : 'apiType'] ?? 'string') {
			default:
			case 'string':
				$value = (string) $value;
				break;
			case 'float':
				$value = (float) $value;
				break;
		}
		return $value;
	}

	/**
	 * Find relationship in YF by API ID.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return int
	 */
	protected function findByRelationship($value, array $field, bool $fromApi): int
	{
		$moduleName = $field['moduleName'] ?? $this->moduleName;
		if (empty($value)) {
			return 0;
		}
		if ($fromApi) {
			return $this->synchronizer->getYfId($value, $moduleName);
		}
		return $this->synchronizer->getApiId($value, $moduleName);
	}

	/**
	 * Add relationship in YF by API ID.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return string|array string (YF) or string (API)
	 */
	protected function addRelationship($value, array $field, bool $fromApi)
	{
		$moduleName = rtrim($field['moduleName'], 's');
		$key = mb_strtolower($moduleName);
		if (null === $this->{$key}) {
			$this->{$key} = $this->synchronizer->getMapModel($moduleName);
		}
		$this->{$key}->setDataApi($this->dataApi);
		return $this->{$key}->saveFromRelation($field);
	}

	/**
	 * Convert phone number to system YF format.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return string
	 */
	protected function convertPhone($value, array $field, bool $fromApi)
	{
		if (empty($value) || !$fromApi) {
			return $value;
		}
		$fieldCrm = $field['fieldCrm'];
		$parsedData = [$fieldCrm => $value];
		$parsedData = \App\Fields\Phone::parsePhone($fieldCrm, $parsedData);
		if (empty($parsedData[$fieldCrm])) {
			foreach ($parsedData as $key => $value) {
				$this->dataYf[$key] = $value;
			}
			return '';
		}
		return $parsedData[$fieldCrm];
	}

	/**
	 * Convert country to system format.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return string|null Country name (YF) or Country code (API)
	 */
	protected function convertCountry($value, array $field, bool $fromApi)
	{
		if (empty($value)) {
			return $value;
		}
		return $fromApi ? \App\Fields\Country::getCountryName($value) : \App\Fields\Country::getCountryCode($value);
	}

	/**
	 * Convert address fields.
	 *
	 * @param string $source
	 * @param string $target
	 * @param bool   $checkField
	 *
	 * @return void
	 */
	protected function convertAddress(string $source, string $target, bool $checkField = true): void
	{
		foreach ($this->addressMapFields as $yf => $api) {
			if ($checkField && !$this->moduleModel->getFieldByName($yf . $target)) {
				\App\Log::info(
					"The {$yf}{$target} field does not exist in the {$this->moduleName} module",
					$this->synchronizer::LOG_CATEGORY
				);
				continue;
			}
			if (\is_array($api)) {
				$api['name'] = [$source, $api['name']];
				$this->loadDataYfMultidimensional($yf . $target, $api);
			} elseif (\array_key_exists($api, $this->dataApi[$source])) {
				$this->dataYf[$yf . $target] = $this->dataApi[$source][$api];
			}
		}
	}

	/**
	 * Convert currency.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return int|string int (YF) or string (API)
	 */
	protected function convertCurrency($value, array $field, bool $fromApi)
	{
		if ($fromApi) {
			$currency = \App\Fields\Currency::getIdByCode($value);
			if (empty($currency)) {
				$currency = \App\Fields\Currency::addCurrency($value);
			}
		} else {
			$currency = \App\Fields\Currency::getById($value)['currency_code'];
		}
		return $currency;
	}

	/**
	 * Find record in YetiFoce.
	 *
	 * @return int
	 */
	protected function findRecordInYf(): int
	{
		return $this->synchronizer->getYfId($this->dataApi['id'], $this->moduleName);
	}

	/**
	 * Parsing `meta_data` from API.
	 *
	 * @return void
	 */
	protected function parseMetaDataFromApi(): void
	{
		if ($this->dataApi['meta_data']) {
			$this->dataApi['metaData'] = [];
			foreach ($this->dataApi['meta_data'] as $value) {
				$this->dataApi['metaData'][$value['key']] = $value['value'];
			}
		}
	}

	/**
	 * Parsing `meta_data` to API.
	 *
	 * @return void
	 */
	protected function parseMetaDataToApi(): void
	{
		if ($this->dataApi['metaData']) {
			$this->dataApi['meta_data'] = [];
			foreach ($this->dataApi['metaData'] as $key => $value) {
				$this->dataApi['meta_data'][] = ['key' => $key, 'value' => $value];
			}
			unset($this->dataApi['metaData']);
		}
	}
}
