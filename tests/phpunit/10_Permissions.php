<?php

/**
 * Permissions test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use PHPUnit\Framework\TestCase;

/**
 * @covers Permissions::<public>
 */
class Permissions extends TestCase
{

	public function testIsPermitted()
	{
		$this->assertTrue(\App\Privilege::isPermitted('Accounts', 'DetailView', ACCOUNT_ID));
	}

	public function testRecalculateSharingRules()
	{
		RecalculateSharingRules();
		$this->assertTrue(true);
	}

	public function testCreateModuleMetaFile()
	{
		\vtlib\Deprecated::createModuleMetaFile();
		$this->assertTrue(true);
	}
}
