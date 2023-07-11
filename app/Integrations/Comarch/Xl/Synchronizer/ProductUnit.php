<?php

/**
 * Comarch product unit synchronization file.
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
 * Comarch product unit synchronization class.
 */
class ProductUnit extends \App\Integrations\Comarch\Synchronizer
{
	/** @var array Cache for data from the API */
	private $cache;
	/** @var array ID by name cache from the API */
	private $cacheList = [];
	/** @var int[] */
	private $roleIdList = [];
	/** @var \Settings_Picklist_Field_Model */
	private $fieldModel;

	/** {@inheritdoc} */
	public function process(): void
	{
		$this->fieldModel = \Settings_Picklist_Field_Model::getInstance(
			'usageunit',
			\Vtiger_Module_Model::getInstance('Products')
		);
		if ($this->fieldModel->isActiveField()) {
			$this->getAllFromApi();
			if (null !== $this->cache) {
				$this->import();
			} else {
				$this->controller->log('Skip import ' . $this->name, []);
			}
		}
	}

	/**
	 * Import account type from API.
	 *
	 * @return void
	 */
	public function import(): void
	{
		if ($this->config->get('log_all')) {
			$this->controller->log('Start import ' . $this->name, []);
		}
		$isRoleBased = $this->fieldModel->isRoleBased();
		$fieldName = $this->fieldModel->getName();
		$picklistValues = \App\Fields\Picklist::getValues($fieldName);
		$values = [];
		foreach ($picklistValues as $value) {
			$values[trim(mb_strtolower($value['picklistValue']), '.')] = $value['picklistValue'];
			$values[trim(mb_strtolower(\App\Language::translate($value['picklistValue'], 'Products')), '.')] = $value['picklistValue'];
		}
		$i = 0;
		foreach ($this->cache as $value) {
			if (empty($value['key'])) {
				continue;
			}
			$name = mb_strtolower(trim($value['key'], '.'));
			if (empty($values[$name])) {
				try {
					$itemModel = $this->fieldModel->getItemModel();
					$itemModel->validateValue('name', $value['key']);
					$itemModel->set('name', $value['key']);
					if ($isRoleBased) {
						if (empty($this->roleIdList)) {
							$this->roleIdList = array_keys(\Settings_Roles_Record_Model::getAll());
						}
						$itemModel->set('roles', $this->roleIdList);
					}
					$itemModel->save();
					$this->cacheList[$value['key']] = $value['key'];
					++$i;
				} catch (\Throwable $ex) {
					$this->controller->log('Import ' . $this->name, ['API' => $value], $ex);
					\App\Log::error("Error during import {$this->name}: \n{$ex->__toString()}", self::LOG_CATEGORY);
				}
			} else {
				$this->cacheList[$values[$name]] = $value['key'];
			}
		}
		if ($this->config->get('log_all')) {
			$this->controller->log('End import ' . $this->name, ['imported' => $i]);
		}
	}

	/** {@inheritdoc} */
	public function getYfValue($apiValue, array $field)
	{
		$this->loadCacheList();
		$key = array_search($apiValue, $this->cacheList);
		return $key ?? null;
	}

	/** {@inheritdoc} */
	public function getApiValue($yfValue, array $field)
	{
		$this->loadCacheList();
		if ($value = $this->cacheList[$yfValue] ?? null) {
			return $value;
		}
		if ($value = $this->cacheList[\App\Language::translate($yfValue, 'Products')] ?? null) {
			return $value;
		}
		return null;
	}

	/**
	 * Get all unit measure from API.
	 *
	 * @return array|null
	 */
	private function getAllFromApi(): ?array
	{
		if (null === $this->cache) {
			try {
				$this->cache = $this->getFromApi('Dictionary/UnitMeasure');
			} catch (\Throwable $ex) {
				$this->controller->log('Get ' . $this->name, null, $ex);
				\App\Log::error("Error during getAllFromApi {$this->name}: \n{$ex->__toString()}", self::LOG_CATEGORY);
			}
		}
		return $this->cache;
	}

	/**
	 * Load cache list.
	 *
	 * @return void
	 */
	private function loadCacheList(): void
	{
		if (empty($this->cacheList)) {
			$this->process();
		}
	}
}
