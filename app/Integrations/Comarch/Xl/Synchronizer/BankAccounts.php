<?php

/**
 * Comarch bank accounts synchronization file.
 *
 * The file is part of the paid functionality. Using the file is allowed only after purchasing a subscription.
 * File modification allowed only with the consent of the system producer.
 *
 * @package Integration
 *
 * @see \App\Integrations\Comarch\Xl\Maps\Account::$dependentSynchronizations
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Integrations\Comarch\Xl\Synchronizer;

/**
 * Comarch bank accounts synchronization class.
 */
class BankAccounts extends \App\Integrations\Comarch\Synchronizer
{
	/** @var \App\Integrations\Comarch\Map Parent instance */
	protected $parent;

	/**
	 * Export data from dependent map.
	 *
	 * @param \App\Integrations\Comarch\Map $parent
	 *
	 * @return void
	 */
	public function exportFromDependent(\App\Integrations\Comarch\Map $parent): void
	{
		$this->parent = $parent;
		if ($this->config->get('log_all')) {
			$this->controller->log('Start export ' . $this->name, []);
		}
		$data = $parent->getDataApi(false);
		foreach ($this->getFromApi('Customer/BankAccounts/' . $data['id']) as $row) {
			$this->importItem($row);
		}
		if ($this->config->get('log_all')) {
			$this->controller->log('End export ' . $this->name, []);
		}
	}

	/**
	 * Import bank account from Comarch to YetiFoce.
	 *
	 * @param array $row
	 *
	 * @return void
	 */
	public function importItem(array $row): void
	{
		$mapModel = $this->getMapModel();
		$mapModel->setDataApi($row);
		if ($dataYf = $mapModel->getDataYf()) {
			$dataYf = $this->parent->getDataYf('fieldMap', false);
			if (!$this->findYfId($row['rkB_NrRachunku'], $dataYf['id'])) {
				$mapModel->loadRecordModel(0);
				$mapModel->getRecordModel()->set('related_to', $dataYf['id']);
				$mapModel->saveInYf();
				$dataYf['id'] = $mapModel->getRecordModel()->getId();
			}
		} else {
			\App\Log::error('Empty map details in ' . __FUNCTION__, self::LOG_CATEGORY);
		}
		if ($this->config->get('log_all')) {
			$this->controller->log($this->name . ' ' . __FUNCTION__ . ' | ' . (empty($dataYf['id']) ? 'skipped' : 'imported'), [
				'API' => $row,
				'YF' => $dataYf ?? [],
			]);
		}
	}

	/** {@inheritdoc} */
	public function findYfId(string $accountNumber, int $relatedTo): int
	{
		$queryGenerator = new \App\QueryGenerator($this->getMapModel()->getModule());
		$queryGenerator->setStateCondition('All');
		$queryGenerator->setFields(['id'])->permissions = false;
		$queryGenerator->addCondition('account_number', $accountNumber, 'e');
		$queryGenerator->addCondition('related_to', $relatedTo, 'eid');
		return $queryGenerator->createQuery()->scalar() ?: 0;
	}
}
