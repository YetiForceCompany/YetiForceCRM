<?php

/**
 * Dashboard Module Model Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Dashboard_Module_Model extends Vtiger_Module_Model
{
	public function isUtilityActionEnabled()
	{
		return true;
	}
}
