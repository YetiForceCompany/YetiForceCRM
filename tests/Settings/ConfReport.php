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
	 * All configuration values.
	 *
	 * @var array
	 */
	public static $confReportAll;

	/**
	 * @codeCoverageIgnore
	 * Setting of tests.
	 */
	public static function setUpBeforeClass()
	{
		static::$confReportAll = \App\Utils\ConfReport::getAll();
	}

	/**
	 * Testing database configuration report.
	 */
	public function testDbConf()
	{
		$this->assertNotEmpty(\Settings_ConfReport_Module_Model::getDbConf(), 'Database configuration report should be not empty');
		$this->assertIsArray(
			\Settings_ConfReport_Module_Model::getDbConf(true),
			'Database configuration report should be array even if empty'
		);
	}

	/**
	 * Testing security configuration report.
	 */
	public function testSecurityConf()
	{
		$this->assertIsArray(
			static::$confReportAll['security'] ?? null,
			'Security configuration (normal mode, show all) report should be not empty'
		);
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
		$this->assertIsArray(
			static::$confReportAll['stability'] ?? null,
			'Security configuration (normal mode, show all) report should be not empty'
		);
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
		$this->assertIsArray(
			\Settings_ConfReport_Module_Model::getDenyPublicDirState(),
			'getDenyPublicDirState returned data type should be array even if empty'
		);
	}

	/**
	 * Testing getPermissionsFiles method.
	 */
	public function testGetPermissionsFiles()
	{
		$this->assertIsArray(
			\Settings_ConfReport_Module_Model::getPermissionsFiles(false),
			'getPermissionsFiles(show all) returned data type should be array even if empty'
		);
		$this->assertIsArray(
			\Settings_ConfReport_Module_Model::getPermissionsFiles(true),
			'getPermissionsFiles(show errors only) returned data type should be array even if empty'
		);
	}
}
