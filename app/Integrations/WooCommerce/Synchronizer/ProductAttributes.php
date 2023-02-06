<?php

/**
 * WooCommerce product attributes synchronization file.
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
 * WooCommerce product attributes synchronization class.
 */
class ProductAttributes extends Base
{
	/** @var array */
	private $attributes = [];
	/** @var int[] */
	private $roleIdList = [];
	/** @var \Settings_Picklist_Field_Model */
	private $fieldModel;

	/** {@inheritdoc} */
	public function process(): void
	{
		$attributes = $this->config->get('attributes');
		if (empty($attributes)) {
			return;
		}
		$this->getListFromApi();
		foreach ($attributes as $value => $fieldName) {
			$this->fieldModel = \Settings_Picklist_Field_Model::getInstance(
				$fieldName,
				\Vtiger_Module_Model::getInstance('Products')
			);
			if (empty($this->fieldModel)) {
				$this->log('Field not found in mapping', ['fieldName' => $fieldName]);
				\App\Log::error('Field not found in mapping: ' . $fieldName, self::LOG_CATEGORY);
			} elseif (!\in_array($this->fieldModel->getFieldDataType(), ['multipicklist'])) {
				$this->log('Invalid field type', ['fieldName' => $fieldName, 'type' => $this->fieldModel->getFieldDataType()]);
				\App\Log::error("Invalid field type: $fieldName ({$this->fieldModel->getFieldDataType()})", self::LOG_CATEGORY);
			} elseif ($this->fieldModel->isActiveField()) {
				$this->import($value);
			}
		}
	}

	/**
	 * Import attributes from API.
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function import(string $name): void
	{
		if ($this->config->get('logAll')) {
			$this->log('Start import product attributes');
		}
		$attr = $this->attributes['pa_' . $name] ?? null;
		if (!$attr) {
			$this->log('Attribute not found', [$name]);
			\App\Log::error('Attribute not found: ' . $name, self::LOG_CATEGORY);
			return;
		}
		$i = 0;
		$rows = $this->getAttrFromApi($attr['id']);
		if ('select' === $attr['type']) {
			$picklistValues = \App\Fields\Picklist::getValues($this->fieldModel->getName());
			$keys = array_flip(array_map('mb_strtolower', array_column($picklistValues, 'picklistValue', 'picklistValueId')));
			foreach ($rows as $row) {
				$name = mb_strtolower($row['name']);
				if (empty($keys[$name]) || !$this->config->get('master')) {
					try {
						$itemModel = $this->fieldModel->getItemModel(empty($keys[$name]) ? null : $keys[$name]);
						$save = false;
						foreach (['name' => 'name', 'description' => 'description', 'prefix' => 'slug'] as $property => $key) {
							if (isset($row[$key]) && $row[$key] != $itemModel->get($property)) {
								$itemModel->validateValue($property, $row[$key]);
								$itemModel->set($property, $row[$key]);
								$save = true;
							}
						}
						if ($save) {
							if (empty($this->roleIdList)) {
								$this->roleIdList = array_keys(\Settings_Roles_Record_Model::getAll());
							}
							$itemModel->set('roles', $this->roleIdList);
							$itemModel->save();
							++$i;
						}
					} catch (\Throwable $th) {
						$this->log('Attribute tag', $row, $th);
						\App\Log::error('Error during import attribute: ' . PHP_EOL . $th->__toString(), self::LOG_CATEGORY);
					}
				}
			}
		}
		if ($this->config->get('logAll')) {
			$this->log('End import product attributes', ['imported' => $i]);
		}
	}

	/**
	 * Get attributes list from API.
	 *
	 * @return array
	 */
	public function getListFromApi(): array
	{
		if (!$this->attributes) {
			try {
				if ($attrs = $this->getFromApi('products/attributes')) {
					$attributes = [];
					foreach ($attrs as $attr) {
						$attributes[$attr['slug']] = $attr;
					}
					$this->attributes = $attributes;
				}
			} catch (\Throwable $ex) {
				$this->log('Get list from API', $attrs ?? [], $ex);
				\App\Log::error('Error during get list from API: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
			}
		}
		return $this->attributes;
	}

	/**
	 * Get attribute details.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	private function getAttrFromApi(int $id): array
	{
		$rows = [];
		try {
			$rows = $this->getFromApi('products/attributes/' . $id . '/terms');
		} catch (\Throwable $ex) {
			$this->log('Get attr from API', $rows ?? [], $ex);
			\App\Log::error('Error during get attr from API: ' . PHP_EOL . $ex->__toString(), self::LOG_CATEGORY);
		}
		return $rows;
	}

	/**
	 * Get field name from fields map.
	 *
	 * @param string $name
	 *
	 * @return string|null
	 */
	public function getMap(string $name): ?string
	{
		return $this->config->get('attributes')[substr($name, 3)] ?? null;
	}
}
