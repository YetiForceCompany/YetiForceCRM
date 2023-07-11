<?php

/**
 * Comarch account types synchronization file.
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
 * Comarch account types synchronization class.
 */
class AccountTypes extends \App\Integrations\Comarch\Synchronizer
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
			'accounttype',
			\Vtiger_Module_Model::getInstance('Accounts')
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
		$values = $this->getPicklistValues();
		$i = 0;
		foreach ($this->cache as $key => $value) {
			if (empty($value)) {
				continue;
			}
			$name = mb_strtolower($value);
			if (empty($values[$name])) {
				try {
					$itemModel = $this->fieldModel->getItemModel();
					$itemModel->validateValue('name', $value);
					$itemModel->set('name', $value);
					if ($isRoleBased) {
						if (empty($this->roleIdList)) {
							$this->roleIdList = array_keys(\Settings_Roles_Record_Model::getAll());
						}
						$itemModel->set('roles', $this->roleIdList);
					}
					$itemModel->save();
					$this->cacheList[$value] = $key;
					++$i;
				} catch (\Throwable $ex) {
					$this->controller->log('Import ' . $this->name, ['API' => $value], $ex);
					\App\Log::error("Error during import {$this->name}: \n{$ex->__toString()}", self::LOG_CATEGORY);
				}
			} else {
				$this->cacheList[$values[$name]] = $key;
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
		if ($value = $this->cacheList[\App\Language::translate($yfValue, 'Accounts')] ?? null) {
			return $value;
		}
		return null;
	}

	/**
	 * Get picklist values.
	 *
	 * @return array
	 */
	private function getPicklistValues(): array
	{
		$values = [];
		foreach (\App\Fields\Picklist::getValues($this->fieldModel->getName()) as $value) {
			$values[mb_strtolower($value['picklistValue'])] = $value['picklistValue'];
			$values[mb_strtolower(\App\Language::translate($value['picklistValue'], 'Accounts'))] = $value['picklistValue'];
		}
		if (\in_array('Inny', $this->cache) && isset($values['other'])) {
			$values['inny'] = $values['other'];
		}
		return $values;
	}

	/**
	 * Get all account type from API.
	 *
	 * @return array|null
	 */
	private function getAllFromApi(): ?array
	{
		if (null === $this->cache) {
			try {
				$this->cache = $this->getFromApi('Dictionary/CustomerType');
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
