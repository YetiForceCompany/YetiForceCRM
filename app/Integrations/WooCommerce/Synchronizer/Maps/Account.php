<?php

/**
 * WooCommerce account synchronization map file.
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

namespace App\Integrations\WooCommerce\Synchronizer\Maps;

/**
 * WooCommerce account synchronization map class.
 */
class Account extends Base
{
	/** {@inheritdoc} */
	protected $moduleName = 'Accounts';
	/** {@inheritdoc} */
	protected $fieldMap = [
		'accountname' => ['name' => ['billing', 'company']],
		'email1' => ['name' => ['billing', 'email']],
		'phone' => ['name' => ['billing', 'phone'], 'fn' => 'convertPhone'],
	];

	/** {@inheritdoc} */
	public function getDataYf(string $type = 'fieldMap', bool $mapped = true): array
	{
		if ($mapped) {
			parent::getDataYf($type);
			if (empty($this->dataYf['accountname'])) {
				$this->dataYf['accountname'] = "{$this->dataApi['billing']['first_name']}|##|{$this->dataApi['billing']['last_name']}";
				$this->dataYf['legal_form'] = 'PLL_NATURAL_PERSON';
			} else {
				$this->dataYf['legal_form'] = 'PLL_COMPANY';
			}
			$this->convertAddress('billing', 'a');
			$this->convertAddress('shipping', 'b');
		}
		return $this->dataYf;
	}

	/** {@inheritdoc} */
	protected function findRecordInYf(): int
	{
		$queryGenerator = new \App\QueryGenerator($this->moduleName);
		$queryGenerator->setStateCondition('All');
		$queryGenerator->setFields(['id'])->permissions = false;
		if (!empty($this->dataYf['vat_id'])) {
			$queryGenerator->addCondition('vat_id', $this->dataYf['vat_id'], 'e');
		} elseif (!empty($this->dataYf['email'])) {
			$queryGenerator->addCondition('email', $this->dataYf['email'], 'e');
		} elseif (!empty($this->dataYf['phone'])) {
			$queryGenerator->addCondition('phone', $this->dataYf['phone'], 'e');
		}
		return $queryGenerator->createQuery()->scalar() ?: 0;
	}
}
