<?php

/**
 * WooCommerce contact synchronization map file.
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
 * WooCommerce contact synchronization map class.
 */
class Contact extends Base
{
	/** {@inheritdoc} */
	protected $moduleName = 'Contacts';
	/** {@inheritdoc} */
	protected $fieldMap = [
		'firstname' => ['name' => ['billing', 'first_name'], 'direction' => 'yf'],
		'lastname' => ['name' => ['billing', 'last_name'], 'direction' => 'yf'],
		'email' => ['name' => ['billing', 'email'], 'direction' => 'yf'],
		'phone' => ['name' => ['billing', 'phone'], 'fn' => 'convertPhone', 'direction' => 'yf'],
		'parent_id' => ['name' => 'customer_id', 'fn' => 'addRelationship', 'moduleName' => 'Accounts', 'direction' => 'yf', 'onlyCreate' => true],
	];
	/** {@inheritdoc} */
	protected $defaultDataYf = [
		'fieldMap' => [
			'contactstatus' => 'Active'
		]
	];
	/** @var \App\Integrations\WooCommerce\Synchronizer\Maps\Account Account model instance */
	protected $account;

	/** {@inheritdoc} */
	public function getDataYf(string $type = 'fieldMap'): array
	{
		parent::getDataYf($type);
		$this->convertAddress('billing', 'a');
		return $this->dataYf;
	}

	/** {@inheritdoc} */
	protected function findRecordInYf(): int
	{
		$queryGenerator = new \App\QueryGenerator($this->moduleName);
		$queryGenerator->setStateCondition('All');
		$queryGenerator->setFields(['id'])->permissions = false;
		if (!empty($this->dataYf['email'])) {
			$queryGenerator->addCondition('email', $this->dataYf['email'], 'e');
		} elseif (!empty($this->dataYf['phone'])) {
			$queryGenerator->addCondition('phone', $this->dataYf['phone'], 'e');
		}
		return $queryGenerator->createQuery()->scalar() ?: 0;
	}
}
