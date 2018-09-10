<?php

/**
 * Settings inventory Taxes view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Inventory_Taxes_View extends Settings_Inventory_CreditLimits_View
{
	public function getView()
	{
		return 'Taxes';
	}

	/**
	 * {@inheritdoc}
	 */
	public function processTplName(\App\Request $request)
	{
		return 'Taxes.tpl';
	}
}
