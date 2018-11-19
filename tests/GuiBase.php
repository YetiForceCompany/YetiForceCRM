<?php
/**
 * Base test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace tests;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

abstract class GuiBase extends \PHPUnit\Framework\TestCase
{
	/**
	 * Last logs.
	 *
	 * @var mixed
	 */
	public $logs;
	public $driver;
	protected static $isLogin = false;

	/**
	 * @codeCoverageIgnore
	 */
	protected function onNotSuccessfulTest(\Throwable $t)
	{
		if (isset($this->logs)) {
			echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
			echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
			//var_export(array_shift($t->getTrace()));
			\print_r($this->logs, true);
			echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
			echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
		}
		throw $t;
	}

	public function setUp()
	{
		parent::setUp();

		$this->driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', DesiredCapabilities::chrome(), 5000);

		$this->login();
	}

	public function url($url)
	{
		$this->driver->get(\AppConfig::main('site_URL') . $url);
	}

	/**
	 * Testing login page display.
	 */
	public function login()
	{
		if (!$this->getLoginStatus()) {
			$this->url('index.php');
			$this->driver->findElement(WebDriverBy::id('username'))->sendKeys('demo');
			$this->driver->findElement(WebDriverBy::id('password'))->sendKeys(\Tests\Base\A_User::$defaultPassrowd);
			$this->driver->findElement(WebDriverBy::tagName('form'))->submit();
		}
	}

	/**
	 * Check if we are already logged in
	 * @return bool
	 */
	protected function getLoginStatus()
	{
		$this->url('index.php?module=Users&view=LoginStatus');
		return ($this->driver->getPageSource() === '1');
	}
}
