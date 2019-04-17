<?php
/**
 * Debuger test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class Debuger extends \Tests\Base
{
	/**
	 * Testing initConsole function.
	 */
	public function testInitConsole()
	{
		$this->assertInstanceOf('\DebugBar\DebugBar', \App\Debuger::initConsole(), 'Expected debug bar object');
	}

	/**
	 * Testing getJavascriptPath function.
	 */
	public function testGetJavascriptPath()
	{
		$this->assertSame('public_html/vendor/yetiforce/debugbar/src/DebugBar/Resources', \App\Debuger::getJavascriptPath(), 'Expected path different from provided');
	}

	/**
	 * Testing getDebugBar function.
	 */
	public function testGetDebugBar()
	{
		$this->assertInstanceOf('\DebugBar\DebugBar', \App\Debuger::getDebugBar(), 'Expected debuger object');
	}

	/**
	 * Testing init function.
	 */
	public function testInit()
	{
		\App\Config::set('debug', 'DISPLAY_DEBUG_CONSOLE', true);
		\App\Config::set('debug', 'LOG_TO_PROFILE', true);
		\App\Config::set('debug', 'LOG_TO_CONSOLE', true);
		$this->assertNull(\App\Debuger::init(), 'Expected null value');
	}

	/**
	 * Testing addLogs function.
	 */
	public function testAddLogs()
	{
		$this->assertNull(\App\Debuger::addLogs('UnitTests test message', 'info', []), 'Expected null');
	}

	/**
	 * Testing checkIP function.
	 */
	public function testCheckIP()
	{
		$this->assertTrue(\App\Debuger::checkIP(), 'Expected true');
		\App\Config::set('debug', 'DEBUG_CONSOLE_ALLOWED_IPS', '127.0.0.1');
		$this->assertFalse(\App\Debuger::checkIP(), 'Expected false');
		\App\Config::set('debug', 'DEBUG_CONSOLE_ALLOWED_IPS', ['127.0.0.1']);
		$this->assertFalse(\App\Debuger::checkIP(), 'Expected false');

		\App\Config::set('debug', 'DEBUG_CONSOLE_ALLOWED_IPS', '');
		$this->assertTrue(\App\Debuger::checkIP(), 'Expected true');
		\App\Config::set('debug', 'DEBUG_CONSOLE_ALLOWED_IPS', ['']);
		$this->assertTrue(\App\Debuger::checkIP(), 'Expected true');
	}
}
