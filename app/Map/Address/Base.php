<?php

namespace App\Map\Address;

/**
 * Base caching class.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
abstract class Base
{
	/**
	 * Construct.
	 */
	public function __construct()
	{
		$this->config = \App\Map\Address::getConfig()[$this->getName()] ?? [];
	}

	/**
	 * Provider name.
	 *
	 * @var string
	 */
	public $name;
	/**
	 * Config data.
	 *
	 * @var array
	 */
	public $config;
	/**
	 * Custom Fields.
	 *
	 * @var array
	 */
	public $customFields = [];

	/**
	 * Provider documentation url address.
	 *
	 * @var string
	 */
	public $docUrl = '';

	/**
	 * Function checks if provider is active.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		return (bool) ($this->config['active'] ?? 0);
	}

	/**
	 * Function checks if  provider been configured?
	 *
	 * @return bool
	 */
	public function isConfigured()
	{
		return !empty($this->config['key']);
	}

	/**
	 * Get provider custom fields.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function getCustomFields(array $data = []): array
	{
		$fields = [];
		foreach ($this->customFields as $fieldName => $configData) {
			if (isset($data[$fieldName])) {
				$configData['fieldvalue'] = $data[$fieldName];
			}
			$fields[$fieldName] = \Vtiger_Field_Model::init('Settings:ApiAddress', $configData, $fieldName);
		}

		return $fields;
	}

	/**
	 * Gets data from request.
	 *
	 * @param \App\Request $request
	 *
	 * @return array
	 */
	public function getDataFromRequest(\App\Request $request): array
	{
		$data = [];
		foreach ($this->getCustomFields() as $fieldName => $fieldModel) {
			if ($request->has($fieldName)) {
				$value = $request->getByType($fieldName, $fieldModel->get('purifyType'));
				$fieldUITypeModel = $fieldModel->getUITypeModel();
				$fieldUITypeModel->validate($value, true);
				$value = $fieldModel->getDBValue($value);
				if ($value && 'country_codes' === $fieldName) {
					$codes = array_map('trim', explode(',', $value));
					$codes = array_filter($codes, fn ($code) => 2 === \strlen($code) && preg_match('/^[a-zA-Z]+$/', $code));
					$value = implode(',', $codes);
				}
				$data[] = [
					'name' => $fieldName,
					'type' => $this->getName(),
					'val' => $value
				];
			}
		}

		return $data;
	}

	/**
	 * Get provider name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return basename(str_replace('\\', '/', static::class));
	}

	/**
	 * Get provider documentation url address.
	 *
	 * @return string
	 */
	public function getDocUrl(): string
	{
		return $this->docUrl;
	}

	/**
	 * Validate configuration.
	 *
	 * @return bool
	 */
	public function validate(): bool
	{
		return $this->isConfigured();
	}

	/**
	 * Find address.
	 *
	 * @param $value string
	 *
	 * @return array
	 */
	abstract public function find($value);
}
