<?php

/**
 * Comarch bank account synchronization map file.
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

namespace App\Integrations\Comarch\Xl\Maps;

/**
 * Comarch bank account synchronization map class.
 */
class BankAccount extends \App\Integrations\Comarch\Map
{
	/** {@inheritdoc} */
	protected $moduleName = 'BankAccounts';
	/** {@inheritdoc} */
	protected $fieldMap = [
		'name' => 'rkB_Id',
		'bank_name' => 'bnk_Nazwa',
		'account_number' => 'rkB_NrRachunku',
		'currency_id' => ['name' => 'rkB_Waluta', 'fn' => 'convertCurrency'],
	];
	/** {@inheritdoc} */
	protected $defaultDataYf = [
		'fieldMap' => [
			'bankaccount_status' => 'PLL_ACTIVE'
		]
	];

	/** {@inheritdoc} */
	protected function convertCurrency($value, array $field, bool $fromApi)
	{
		if ($fromApi) {
			if ($value) {
				$currency = \App\Fields\Currency::getIdByCode($value);
				if (empty($currency)) {
					$currency = \App\Fields\Currency::addCurrency($value);
				}
			} else {
				$currency = \App\Fields\Currency::getDefault()['id'];
			}
		} else {
			$currency = \App\Fields\Currency::getById($value)['currency_code'];
		}
		return $currency;
	}
}
