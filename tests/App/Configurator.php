<?php
/**
 * Configurator test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class Configurator extends \Tests\Base
{
	public static $instance = false;

	/**
	 * Testing constructor method.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testInstance()
	{
		static::$instance = new \App\Configurator('yetiforce');
		$this->assertInstanceOf('\App\Configurator', static::$instance);
	}

	/**
	 * Testing set and save methods.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testSave()
	{
		reset(\App\YetiForce\Status::$variables);
		$flagName = key(\App\YetiForce\Status::$variables);
		$this->assertInstanceOf('\App\Configurator', static::$instance->set($flagName, '1'));
		$this->assertNull(static::$instance->save());
	}

	/**
	 * Testing revert method.
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public function testRevert()
	{
		$this->assertNull(static::$instance->revert());
	}
}
