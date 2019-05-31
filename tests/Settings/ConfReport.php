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
	 * Testing database configuration report.
	 */
	public function testDbConf()
	{
		$this->assertNotEmpty(\App\Db::getInstance()->getInfo(), 'Database configuration report should be not empty');
		$this->assertIsArray(
			\App\Db::getInstance()->getInfo(),
			'Database configuration report should be array even if empty'
		);
	}

	/**
	 * Testing system informations report.
	 */
	public function testSystemInfo()
	{
		$this->assertNotEmpty(\App\Utils\ConfReport::getConfig(), 'System information report should be not empty');
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
		$this->assertNotEmpty(\App\Utils\ConfReport::testSpeed());
	}
}
