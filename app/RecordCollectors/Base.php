<?php
/**
 * Base record collector file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\RecordCollectors;

/**
 * Base record collector class.
 */
class Base
{
	/**
	 * Module name.
	 *
	 * @var string
	 */
	public $moduleName;
	/**
	 * Allowed modules.
	 *
	 * @var string[]
	 */
	protected static $allowedModules = [];
	/**
	 * Icon.
	 *
	 * @var string
	 */
	public $icon;
	/**
	 * Label.
	 *
	 * @var string
	 */
	public $label;
	/**
	 * Search results display type.
	 *
	 * @var string
	 */
	public $displayType;
	/**
	 * List of fields for the modal search window.
	 *
	 * @var array
	 */
	protected $fields = [];
	/**
	 * Request instance.
	 *
	 * @var \App\Request
	 */
	protected $request;
	/**
	 * Fields mapping for loading record data.
	 *
	 * @var array
	 */
	protected $modulesFieldsMap = [];

	/**
	 * Undocumented function.
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
	 * @return \Vtiger_Field_Model
	 */
	public function getFields(): array
	{
		$fields = [];
		foreach ($this->fields as $fieldName => $data) {
			if (isset($data['picklistValues']) && false !== $data['picklistModule']) {
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
				}
			}
			$fields[$fieldName] = $fieldModel;
		}
		return $fields;
	}

	/**
	 * Check whether it is active.
	 *
	 * @return bool
	 */
	public function isActive(): bool
	{
		return \in_array($this->moduleName, static::$allowedModules);
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
}
