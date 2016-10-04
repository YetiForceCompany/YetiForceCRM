<?php
/**
 * Permissions test class
 * @package YetiForce.Tests
 * @license licenses/License.html
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
		\App\Privilege::isPermitted('Accounts', 'DetailView', ACCOUNT_ID);
	}
	
	public function testRecalculateSharingRules()
	{
		RecalculateSharingRules();
	}
	
	public function testCreateModuleMetaFile()
	{
		\vtlib\Deprecated::createModuleMetaFile();
	}	
	
}
