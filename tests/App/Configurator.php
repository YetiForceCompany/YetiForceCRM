<?php
/**
 * Configurator test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Tests\App;

/**
 * Class Configurator tests.
 */
class Configurator extends \Tests\Base
{
	/**
	 * Instance container.
	 *
	 * @var \App\ConfigFile
	 */
	public static $instance = false;

	/**
	 * Testing constructor method.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testInstance()
	{
		static::$instance = new \App\ConfigFile('component', 'YetiForce');
		$this->assertInstanceOf('\App\ConfigFile', static::$instance);
	}

	/**
	 * Testing set and save methods.
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \ReflectionException
	 */
	public function testSave()
	{
		$flagName = \array_search('bool', \App\YetiForce\Watchdog::$variables);
		$previousValue = \App\Config::component('YetiForce', $flagName, false);
		static::$instance->set($flagName, !$previousValue);
		static::$instance->create();
		$this->assertNotSame($previousValue, \App\Config::component('YetiForce', $flagName, false));
	}
}
