<?php
/**
 * Privileges test class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Tests\Base;

class I_Privilege extends \Tests\Base
{
	/**
	 * Testing Privilege Utils.
	 */
	public function testPrivilegeUtils()
	{
		$ticketId = (new \App\Db\Query())->from('vtiger_troubletickets')->scalar();
		$this->assertNotFalse(\App\PrivilegeUtil::testPrivileges($ticketId));
	}
}
