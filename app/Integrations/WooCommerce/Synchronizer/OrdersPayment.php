<?php

/**
 * Synchronization orders payment methods file.
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
 * Synchronization orders payment methods class.
 */
class OrdersPayment extends Base
{
	/** @var string[] Map */
	public $map = [
		'bacs' => 'PLL_TRANSFER',
		'cheque' => 'PLL_CHECK',
		'cod' => 'PLL_CASH_ON_DELIVERY',
		'przelewy24' => 'Przelewy24',
	];
	/** @var \Settings_Picklist_Field_Model */
	private $fieldModel;

	/** {@inheritdoc} */
	public function process(): void
	{
		$this->fieldModel = \Settings_Picklist_Field_Model::getInstance(
			'payment_methods',
			\Vtiger_Module_Model::getInstance('SSingleOrders')
		);
		if (!\App\Module::isModuleActive('SSingleOrders') || !$this->fieldModel->isActiveField()) {
			return;
		}
		if ($paymentMethods = $this->config->get('paymentMethods')) {
			$this->map = $paymentMethods;
		}
		if ($this->config->get('logAll')) {
			$this->log('Start import orders payment methods', []);
		}
		$i = 0;
		try {
			if ($rows = $this->getAllFromApi()) {
				$picklistValues = \App\Fields\Picklist::getValues('payment_methods');
				$keys = array_flip(array_map('mb_strtoupper', array_column($picklistValues, 'payment_methods', 'payment_methodsid')));
				foreach ($rows as $row) {
					$name = mb_strtoupper($this->map[$row['id']] ?? $row['id']);
					if (empty($keys[$name])) {
						try {
							$itemModel = $this->fieldModel->getItemModel();
							$itemModel->validateValue('name', $row['id']);
							$itemModel->set('name', $row['id']);
							$itemModel->save();
							++$i;
						} catch (\Throwable $th) {
							$this->log('Import payment method', $row, $th);
							\App\Log::error('Error during import payment method: ' . PHP_EOL . $th->__toString(), self::LOG_CATEGORY);
						}
					}
				}
			}
		} catch (\Throwable $ex) {
			$this->log('Import payment methods', $rows ?? null, $ex);
			\App\Log::error(
				'Error during import payment methods: ' . PHP_EOL . $ex->__toString(),
				self::LOG_CATEGORY
			);
		}
		if ($this->config->get('logAll')) {
			$this->log('End import orders payment methods', [
				'currency' => $i,
			]);
		}
	}

	/**
	 * Get payment gateways form WooCommerce.
	 *
	 * @return array
	 */
	public function getAllFromApi(): array
	{
		return \App\Json::decode($this->connector->request('GET', 'payment_gateways')) ?? [];
	}
}
