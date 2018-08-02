<?php

/**
 * Dashboard Module Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Dashboard_Module_Model extends Vtiger_Module_Model
{
	public function isUtilityActionEnabled()
	{
		return true;
	}
}
