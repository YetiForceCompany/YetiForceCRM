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
	use \App\Integrations\Traits\SynchronizerPicklist;

	/** @var array Cache for data from the API */
	private $cache;
	/** @var array ID by name cache from the API */
	private $cacheList = [];
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
		if ($value = $this->cacheList[\App\Language::translate($yfValue, 'Accounts', 'pl-PL')] ?? null) {
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
			$values[mb_strtolower(
				\App\Language::translate(
					$value['picklistValue'],
					'Accounts',
					'pl-PL'
				)
			)] = $value['picklistValue'];
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
				$this->logError('getAllFromApi ' . $this->name, null, $ex);
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
