<?php
/**
 * User test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class User extends \Tests\Base
{
	/**
	 * Testing instance creation.
	 */
	public function testInstance()
	{
		$this->assertInstanceOf('\App\User', \App\User::getCurrentUserModel(), 'Expected User current user object');
		$this->assertSame(\App\User::getCurrentUserId(), \App\User::getCurrentUserModel()->getId(), 'Expected that user ids will be equal');
	}

	/**
	 * Testing cache clearance.
	 */
	public function testClearCache()
	{
		$this->assertNull(\App\User::clearCache(), 'Cache clearance error');
	}

	/**
	 * Testing user sharing file loader.
	 */
	public function testGetSharingFile()
	{
		$this->assertNotNull(\App\User::getSharingFile(\App\User::getCurrentUserId()), 'Sharing file should be not null');
	}

	/**
	 * Testing get name.
	 */
	public function testGetName()
	{
		$this->assertNotEmpty(\App\User::getCurrentUserModel()->getName(), 'Expected current user name');
	}

	/**
	 * Testing get role instance.
	 */
	public function testGetRoleInstance()
	{
		$this->assertInstanceOf('\Settings_Roles_Record_Model', \App\User::getCurrentUserModel()->getRoleInstance(), 'Expected current user role object');
	}
}
