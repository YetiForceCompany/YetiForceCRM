<?php

/**
 * SRecurringOrders field model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */
class SRecurringOrders_Field_Model extends Vtiger_Field_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getModulesListValues()
	{
		if ($this->getFieldName() !== 'target_module') {
			return parent::getModulesListValues();
		}
		return [App\Module::getModuleId('SSingleOrders') => ['name' => 'SSingleOrders', 'label' => \App\Language::translate('SSingleOrders', 'SSingleOrders')]];
	}
}
