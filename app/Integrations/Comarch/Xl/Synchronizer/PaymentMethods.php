<?php

/**
 * Comarch payment methods synchronization file.
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
 * Comarch payment methods synchronization class.
 */
class PaymentMethods extends \App\Integrations\Comarch\Synchronizer
{
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
			'payment_methods',
			\Vtiger_Module_Model::getInstance('Accounts')
		);
		if ($this->fieldModel->isActiveField()) {
			$this->getAllFromApi();
			$this->import();
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
		$fieldName = $this->fieldModel->getName();
		$picklistValues = \App\Fields\Picklist::getValues($fieldName);
		$values = [];
		foreach ($picklistValues as $value) {
			$values[mb_strtolower($value['picklistValue'])] = $value['picklistValue'];
			$values[mb_strtolower(\App\Language::translate($value['picklistValue'], 'Accounts'))] = $value['picklistValue'];
		}
		if (\in_array('Przelew', array_column($this->cache, 'kon_Wartosc')) && isset($values['pll_transfer'])) {
			$values['przelew'] = $values['pll_transfer'];
		}
		$i = 0;
		foreach ($this->cache as $value) {
			if (empty($value['kon_Wartosc'])) {
				continue;
			}
			$name = mb_strtolower($value['kon_Wartosc']);
			if (empty($values[$name])) {
				try {
					$itemModel = $this->fieldModel->getItemModel();
					$itemModel->validateValue('name', $value['kon_Wartosc']);
					$itemModel->set('name', $value['kon_Wartosc']);
					$itemModel->save();
					$this->cacheList[$value['kon_Wartosc']] = $value['kon_Lp'];
					++$i;
				} catch (\Throwable $ex) {
					$this->controller->log('Import ' . $this->name, ['API' => $value], $ex);
					\App\Log::error("Error during import {$this->name}: \n{$ex->__toString()}", self::LOG_CATEGORY);
				}
			} else {
				$this->cacheList[$values[$name]] = $value['kon_Lp'];
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
		return $key ?? '';
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
	 * Get all account type from API.
	 *
	 * @return array
	 */
	private function getAllFromApi(): array
	{
		if (null === $this->cache) {
			$this->cache = [];
			try {
				$this->cache = $this->getFromApi('PaymentMethod/Get');
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
