<?php
/**
 * UIType CurrencyList Field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * CurrencyList UIType class.
 */
class Users_CurrencyList_UIType extends Vtiger_CurrencyList_UIType
{
	/** {@inheritDoc} */
	public function getDefaultValue()
	{
		return \App\Fields\Currency::getDefault()['id'];
	}
}
