<?php
/**
 * HolidaysEntitlement module model class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
class HolidaysEntitlement_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to check whether the module is summary view supported.
	 *
	 * @return bool - true/false
	 */
	public function isSummaryViewSupported()
	{
		return false;
	}
}
