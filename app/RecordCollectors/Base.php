<?php
/**
 * Base record collector file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * Base record collector class.
 */
class Base
{
	/** @var string Module name. */
	public $moduleName;

	/** @var string Record collector name. */
	protected $name;

	/** @var string[] Allowed modules. */
	public $allowedModules;

	/** @var string Icon. */
	public $icon;

	/** @var string Label. */
	public $label;

	/** @var string Additional description, visible in the modal window. */
	public $description;

	/** @var string Search results display type. */
	public $displayType;

	/** @var array Configuration field list. */
	public $settingsFields = [];

	/** @var string Url to Documentation API */
	public $docUrl;

	/** var array List of fields for the modal search window. */
	protected $fields = [];

	/** @var array Data from record collector source. */
	protected $data = [];

	/** @var array Response data. */
	protected $response = [];

	/** @var \App\Request Request instance. */
	protected $request;

	/** @var array Fields mapping for loading record data. */
	protected $modulesFieldsMap = [];

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$name = basename(str_replace('\\', '/', static::class));
		$this->name = $name;

		$class = '\\Config\\Components\\RecordCollectors\\' . $name;
		if (!\class_exists($class)) {
			return;
		}
		$config = (new \ReflectionClass($class))->getStaticProperties();
		if (isset($config['allowedModules'])) {
			$this->allowedModules = $config['allowedModules'];
			unset($config['allowedModules']);
		}
		foreach ($config as $key => $value) {
			$this->{$key} = $value;
		}
	}

	/**
	 * Get record collector name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Set request.
	 *
	 * @param \App\Request $request
	 *
	 * @return void
	 */
	public function setRequest(\App\Request $request): void
	{
		$this->request = $request;
	}

	/**
	 * Get fields for the modal search window.
	 *
	 * @return \Vtiger_Field_Model[]
	 */
	public function getFields(): array
	{
		$fieldsModel = [];
		foreach ($this->fields as $fieldName => $data) {
			if (isset($data['picklistValuesFunction'])) {
				$data['picklistValues'] = $this->{$data['picklistValuesFunction']}($data);
			} elseif (isset($data['picklistValues']) && false !== $data['picklistModule']) {
				$picklistModule = $data['picklistModule'] ?? $this->moduleName;
				foreach ($data['picklistValues'] as $picklistKey => $value) {
					$data['picklistValues'][$picklistKey] = \App\Language::translate($value, $picklistModule);
				}
			}
			$fieldModel = \Vtiger_Field_Model::init($this->moduleName, $data, $fieldName);
			if (isset($this->modulesFieldsMap[$this->moduleName][$fieldName]) && $this->request->has($this->modulesFieldsMap[$this->moduleName][$fieldName])) {
				try {
					$uitypeModel = $fieldModel->getUITypeModel();
					$value = $this->request->getByType($this->modulesFieldsMap[$this->moduleName][$fieldName], 'Text');
					$uitypeModel->validate($value, true);
					$fieldModel->set('fieldvalue', $uitypeModel->getDBValue($value));
				} catch (\Throwable $th) {
					\App\Log::error($th->__toString(), 'RecordCollectors');
				}
			}
			$fieldsModel[$fieldName] = $fieldModel;
		}
		return $fieldsModel;
	}

	/**
	 * Get fields for the module name.
	 *
	 * @param string $moduleName
	 *
	 * @return string[]
	 */
	public function getFieldsModule(string $moduleName): array
	{
		return $this->modulesFieldsMap[$moduleName];
	}

	/**
	 * Check whether it is active.
	 *
	 * @return bool
	 */
	public function isActive(): bool
	{
		return \in_array($this->moduleName, $this->allowedModules);
	}

	/**
	 * Search data function.
	 *
	 * @return array
	 */
	public function search(): array
	{
		throw new \Api\Core\Exception('no search function');
	}

	/**
	 * Get params of collector.
	 *
	 * @return array
	 */
	protected function getParams(): array
	{
		if ($params = (new \App\Db\Query())->select(['params'])->from('vtiger_links')->where(['linktype' => 'EDIT_VIEW_RECORD_COLLECTOR', 'linkurl' => static::class])->scalar()) {
			return \App\Json::decode($params, true);
		}
		return [];
	}

	/**
	 * Load data.
	 *
	 * @return void
	 */
	public function loadData(): void
	{
		if (empty($this->data)) {
			return;
		}
		if ($recordId = $this->request->getInteger('record')) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($recordId, $this->moduleName);
			$this->response['recordModel'] = $recordModel;
			$fieldsModel = $recordModel->getModule()->getFields();
		} else {
			$fieldsModel = \Vtiger_Module_Model::getInstance($this->moduleName)->getFields();
		}
		$additional = $fieldsData = $skip = [];
		$rows = isset($this->data[0]) ? $this->data : [$this->data];
		foreach ($rows as $key => &$row) {
			$dataCounter[$key] = 0;
			if (empty($row)) {
				continue;
			}
			foreach ($this->formFieldsToRecordMap[$this->moduleName] as $apiKey => $fieldName) {
				if (empty($fieldsModel[$fieldName]) || !$fieldsModel[$fieldName]->isActiveField()) {
					if (isset($row[$apiKey]) && '' !== $row[$apiKey] && null !== $row[$apiKey]) {
						$skip[$fieldName]['data'][$key] = $row[$apiKey];
						if (isset($fieldsModel[$fieldName]) && empty($skip[$fieldName]['label'])) {
							$skip[$fieldName]['label'] = \App\Language::translate($fieldsModel[$fieldName]->getFieldLabel(), $this->moduleName);
						} else {
							$skip[$fieldName]['label'] = $fieldName;
						}
					}
					unset($row[$apiKey]);
					continue;
				}
				$value = '';
				if (isset($row[$apiKey])) {
					$value = trim($row[$apiKey]);
					unset($row[$apiKey]);
				}
				if ('' === $value && isset($fieldsData[$fieldName]['data'][$key])) {
					continue;
				}
				if ($value) {
					++$dataCounter[$key];
				}
				$fieldModel = $fieldsModel[$fieldName];
				$fieldsData[$fieldName]['label'] = \App\Language::translate($fieldModel->getFieldLabel(), $this->moduleName);
				$fieldsData[$fieldName]['data'][$key] = [
					'raw' => $value,
					'edit' => $fieldModel->getEditViewDisplayValue($value),
					'display' => $fieldModel->getDisplayValue($value, false, false, false, 40),
				];
			}
			foreach ($row as $name => $value) {
				if ('' !== $value && null !== $value) {
					$additional[$name][$key] = $value;
				}
			}
		}
		$this->response['fields'] = $fieldsData;
		$this->response['skip'] = $skip;
		$this->response['keys'] = array_keys($rows);
		$this->response['additional'] = $additional;
		$this->response['dataCounter'] = $dataCounter;
	}

	/**
	 * Get fields labels for the module name.
	 *
	 * @param string $moduleName
	 *
	 * @return string[]
	 */
	public function getFieldsLabelsByModule(string $moduleName): array
	{
		$fieldsModels = \Vtiger_Module_Model::getInstance($moduleName)->getFields();
		$labels = [];
		foreach ($this->formFieldsToRecordMap[$moduleName] as $fieldName) {
			if (isset($fieldsModels[$fieldName]) && $fieldsModels[$fieldName]->isActiveField()) {
				$labels[$fieldName] = $fieldsModels[$fieldName]->getFullLabelTranslation();
			}
		}
		return $labels;
	}
}
