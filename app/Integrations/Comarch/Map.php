<?php

/**
 * Comarch abstract base map file.
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

namespace App\Integrations\Comarch;

/**
 * Comarch abstract base map class.
 */
abstract class Map
{
	/** @var string The name of the field with the identifier in Comarch */
	const FIELD_NAME_ID = 'comarch_id';
	/** @var string The name of the key with the identifier from Comarch */
	const API_NAME_ID = 'id';
	/** @var string Skip mode when data is incomplete */
	public $skip = false;
	/** @var string Map module name. */
	protected $moduleName;
	/** @var array Mapped fields. */
	protected $fieldMap = [];
	/** @var array Data from Comarch. */
	protected $dataApi = [];
	/** @var array Default data from Comarch. */
	protected $defaultDataApi = [];
	/** @var array Data from YetiForce. */
	protected $dataYf = [];
	/** @var array Default data from YetiForce. */
	protected $defaultDataYf = [];
	/** @var \App\Integrations\Comarch\Synchronizer Synchronizer instance */
	protected $synchronizer;
	/** @var array Dependent synchronizations to be performed during the operation */
	protected $dependentSynchronizations = [];
	/** @var \Vtiger_Module_Model Module model instance */
	protected $moduleModel;
	/** @var \Vtiger_Record_Model Record model instance */
	protected $recordModel;
	/** @var string API map mode: create, update, get. */
	protected $modeApi;

	/** @var string[] Mapped address fields. */
	protected $addressMapFields = [
		'addresslevel1' => [
			'names' => ['get' => 'knt_Kraj', 'create' => 'Kraj', 'update' => 'Kraj'], 'fn' => 'convertCountry'
		],
		'addresslevel2' => ['names' => ['get' => 'knt_Wojewodztwo', 'create' => 'Wojewodztwo', 'update' => 'Wojewodztwo']],
		'addresslevel3' => ['names' => ['get' => 'knt_Powiat', 'create' => 'Powiat', 'update' => 'Powiat']],
		'addresslevel4' => ['names' => ['get' => 'knt_Gmina', 'create' => 'Gmina', 'update' => 'Gmina']],
		'addresslevel5' => ['names' => ['get' => 'knt_Miasto', 'create' => 'Miasto', 'update' => 'Miasto']],
		'addresslevel7' => ['names' => ['get' => 'knt_KodP', 'create' => 'KodP', 'update' => 'KodP']],
		'addresslevel8' => ['names' => ['get' => 'knt_Ulica', 'create' => 'Ulica', 'update' => 'Ulica']],
		'first_name_' => 'first_name',
		'last_name_' => 'last_name',
		'phone_' => ['name' => 'phone', 'fn' => 'convertPhone'],
		'email_' => 'email',
		'company_name_' => 'company',
	];

	/**
	 * Constructor.
	 *
	 * @param \App\Integrations\Comarch\Synchronizer $synchronizer
	 */
	public function __construct(Synchronizer $synchronizer)
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
			$this->modeApi = 'get';
		} else {
			$this->dataApi = $this->defaultDataApi;
			$this->modeApi = '';
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
	 * Checking what is the mode of operation to be performed in the API,
	 * along with searching for the same entry in the API.
	 *
	 * @return void
	 */
	public function loadModeApi(): void
	{
		if (empty($this->modeApi)) {
			$this->modeApi = empty($this->dataYf[$this::FIELD_NAME_ID]) ? 'create' : 'update';
			if (empty($this->dataApi['id']) && ($id = $this->findRecordInApi())) {
				$this->dataApi['id'] = $id;
				$this->modeApi = 'update';
			}
		}
	}

	/**
	 * Get API mode.
	 *
	 * @return string|null
	 */
	public function getModeApi(): ?string
	{
		return $this->modeApi;
	}

	/**
	 * Load record model.
	 *
	 * @param int|null $id
	 */
	public function loadRecordModel(?int $id = null): void
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
			foreach ($this->{$type} as $fieldCrm => $field) {
				if ($this->skip) {
					continue;
				}
				if (\is_array($field)) {
					if (!empty($field['direction']) && 'api' === $field['direction']) {
						continue;
					}
					$key = $field['name'] ?? ($field['names'][$this->modeApi] ?? '');
					$field['fieldCrm'] = $fieldCrm;
					if (empty($key)) {
						$this->synchronizer->controller->log(
							"[API>YF][1] No key ($fieldCrm)",
							['fieldConfig' => $field, 'data' => $this->dataApi],
							null,
							true
						);
					} elseif (\is_array($key)) {
						$field['name'] = $key;
						$this->loadDataYfMultidimensional($fieldCrm, $field);
					} elseif (\array_key_exists($key, $this->dataApi)) {
						$this->loadDataYfMap($fieldCrm, $field);
					} elseif (!\array_key_exists('optional', $field) || empty($field['optional'])) {
						$error = "[API>YF][1] No column {$key} ($fieldCrm)";
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
	 * Return parsed data in YetiForce format.
	 *
	 * @param bool $mapped
	 *
	 * @return array
	 */
	public function getDataApi(bool $mapped = true): array
	{
		if ($mapped) {
			if (!empty($this->dataYf[$this::FIELD_NAME_ID])) {
				$this->dataApi['id'] = $this->dataYf[$this::FIELD_NAME_ID];
			}
			foreach ($this->fieldMap as $fieldCrm => $field) {
				if ($this->skip) {
					continue;
				}
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
		$key = $field['name'] ?? ($field['names'][$this->modeApi] ?? '');
		if (empty($key)) {
			$this->synchronizer->controller->log(
				"[API>YF][1] No key ({$field['fieldCrm']})",
				['fieldConfig' => $field, 'data' => $this->dataApi],
				null,
				true
			);
		} elseif (\is_array($key)) {
			foreach (array_reverse($key) as $name) {
				$value = [$name => $value];
			}
			$this->dataApi = \App\Utils::merge($this->dataApi, $value);
		} else {
			$this->dataApi[$key] = $value;
		}
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
			$this->recordModel->isEmpty($this::FIELD_NAME_ID)
			&& ($id = $this->dataApi['id'] ?? $this->dataApi[$this::API_NAME_ID] ?? 0)
			&& $this->recordModel->getModule()->getFieldByName($this::FIELD_NAME_ID)
			&& !empty($id)
		) {
			$this->recordModel->set($this::FIELD_NAME_ID, $id);
		}
		$this->recordModel->set('comarch_server_id', $this->synchronizer->config->get('id'));
		$isNew = empty($this->recordModel->getId());
		$this->recordModel->save();
		$this->recordModel->ext['isNew'] = $isNew;
		if ($isNew && $this->recordModel->get($this::FIELD_NAME_ID)) {
			$this->synchronizer->updateMapIdCache(
				$this->recordModel->getModuleName(),
				$this->recordModel->get($this::FIELD_NAME_ID),
				$this->recordModel->getId()
			);
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
	 * Find record in YetiFoce.  It can only be based on data from CRM `$this->dataApi`.
	 *
	 * @return int|null
	 */
	public function findRecordInYf(): ?int
	{
		return $this->synchronizer->getYfId($this->dataApi['id'], $this->moduleName);
	}

	/**
	 * Find record in API. It can only be based on data from CRM `$this->dataYf`.
	 *
	 * @return int
	 */
	public function findRecordInApi(): int
	{
		return $this->synchronizer->getApiId($this->dataYf['id'], $this->moduleName);
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
		$key = $field['name'] ?? $field['names']['get'];
		$value ??= $this->dataApi[$key];
		if (isset($field['map'])) {
			if (\array_key_exists($value, $field['map'])) {
				$this->dataYf[$fieldCrm] = $field['map'][$value];
			} elseif (empty($field['mayNotExist'])) {
				$value = print_r($value, true);
				$error = "[API>YF] No value `{$value}` in map {$key}";
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
	 * Find by relationship in YF by API ID.
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
	 * Add relationship in YF by API ID.findByRelationship.
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
	 * Find relationship in YF by API ID.
	 *
	 * @param mixed $value
	 * @param array $field
	 * @param bool  $fromApi
	 *
	 * @return mixed
	 */
	protected function findBySynchronizer($value, array $field, bool $fromApi)
	{
		$synchronizer = $this->synchronizer->controller->getSync($field['synchronizer']);
		if ($fromApi) {
			$return = $synchronizer->getYfValue($value, $field);
		} else {
			$return = $synchronizer->getApiValue($value, $field);
		}
		if (null === $return && (!\array_key_exists('optional', $field) || empty($field['optional']))) {
			$this->skip = true;
			$this->synchronizer->controller->log(
				($fromApi ? '[API>YF]' : '[YF>API]') .
				"Skip value: {$value} (Field: {$field['fieldCrm']} ,Sync: {$field['synchronizer']})",
				['fieldConfig' => $field, 'data' => $this->dataApi],
				null,
				true
			);
		}
		return $return;
	}

	/**
	 * Run dependent synchronizer.
	 *
	 * @param bool $fromApi
	 *
	 * @return void
	 */
	protected function runDependentSynchronizer(bool $fromApi): void
	{
		if (empty($this->dependentSynchronizations)) {
			return;
		}
		foreach ($this->dependentSynchronizations as $synchronizer) {
			$synchronizer = $this->synchronizer->controller->getSync($synchronizer);
			if ($fromApi) {
				if (method_exists($synchronizer, 'importFromDependent')) {
					$synchronizer->importFromDependent($this);
				}
			} else {
				if (method_exists($synchronizer, 'exportFromDependent')) {
					$synchronizer->exportFromDependent($this);
				}
			}
		}
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
}
