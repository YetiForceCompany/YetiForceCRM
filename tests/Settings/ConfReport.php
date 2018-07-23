<?php
/**
 * ConfReport test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\Settings;

class ConfReport extends \Tests\Base
{
	/**
	 * Testing database configuration report.
	 */
	public function testDbConf()
	{
		$this->assertNotEmpty(\Settings_ConfReport_Module_Model::getDbConf(), 'Database configuration report should be not empty');
		$this->assertInternalType('array', \Settings_ConfReport_Module_Model::getDbConf(true), 'Database configuration report should be array even if empty');
	}

	/**
	 * Testing security configuration report.
	 */
	public function testSecurityConf()
	{
		$this->assertNotEmpty(\Settings_ConfReport_Module_Model::getSecurityConf(), 'Security configuration (normal mode, show all) report should be not empty');
		$this->assertNotEmpty(\Settings_ConfReport_Module_Model::getSecurityConf(true), 'Security configuration (install mode, show all) report should be not empty');
		$this->assertInternalType('array', \Settings_ConfReport_Module_Model::getSecurityConf(false, true), 'Security configuration (normal mode, show errors) report should be array even if empty');
		$this->assertInternalType('array', \Settings_ConfReport_Module_Model::getSecurityConf(true, true), 'Security configuration (install mode, show errors) report should be array even if empty');
	}

	/**
	 * Testing system informations report.
	 */
	public function testSystemInfo()
	{
		$this->assertNotEmpty(\Settings_ConfReport_Module_Model::getSystemInfo(), 'System information report should be not empty');
	}

	/**
	 * Testing system stability configuration report.
	 */
	public function testStabilityConf()
	{
		$this->assertNotEmpty(\Settings_ConfReport_Module_Model::getStabilityConf(false, false, true), 'System stability configuration(normal mode, show all) report should be not empty');
		$this->assertNotEmpty(\Settings_ConfReport_Module_Model::getStabilityConf(true, false, true), 'System stability configuration(install mode, show all) report should be not empty');
		$this->assertInternalType('array', \Settings_ConfReport_Module_Model::getStabilityConf(false, true, true), 'System stability configuration(normal mode, show errors) report should be not empty');
		$this->assertInternalType('array', \Settings_ConfReport_Module_Model::getStabilityConf(true, true, true), 'System stability configuration(install mode, show errors) report should be not empty');

		$this->assertNotEmpty(\Settings_ConfReport_Module_Model::getStabilityConf(false, false, true), 'System stability configuration(normal mode, show all, cli) report should be not empty');
		$this->assertNotEmpty(\Settings_ConfReport_Module_Model::getStabilityConf(true, false, true), 'System stability configuration(install mode, show all, cli) report should be not empty');
		$this->assertInternalType('array', \Settings_ConfReport_Module_Model::getStabilityConf(false, true, true), 'System stability configuration(normal mode, show errors, cli) report should be not empty');
		$this->assertInternalType('array', \Settings_ConfReport_Module_Model::getStabilityConf(true, true, true), 'System stability configuration(install mode, show errors, cli) report should be not empty');
	}

	/**
	 * Testing system performance.
	 */
	public function testSpeed()
	{
		$this->assertNotEmpty(\Settings_ConfReport_Module_Model::testSpeed());
	}

	/**
	 * Testing getDenyPublicDirState method.
	 */
	public function testGetDenyPublicDirState()
	{
		$this->assertNotEmpty(\Settings_ConfReport_Module_Model::getDenyPublicDirState(), 'getDenyPublicDirState data should be not empty');
		$this->assertInternalType('array', \Settings_ConfReport_Module_Model::getDenyPublicDirState(), 'getDenyPublicDirState returned data type should be array even if empty');
	}

	/**
	 * Testing getPermissionsFiles method.
	 */
	public function testGetPermissionsFiles()
	{
		$this->assertInternalType('array', \Settings_ConfReport_Module_Model::getPermissionsFiles(false), 'getPermissionsFiles(show all) returned data type should be array even if empty');
		$this->assertInternalType('array', \Settings_ConfReport_Module_Model::getPermissionsFiles(true), 'getPermissionsFiles(show errors only) returned data type should be array even if empty');
	}
}
