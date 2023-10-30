<?php
/**
 * HolidaysEntitlement module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
class HolidaysEntitlement_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to check whether the module is summary view supported.
	 *
	 * @return bool
	 */
	public function isSummaryViewSupported()
	{
		return false;
	}
}
