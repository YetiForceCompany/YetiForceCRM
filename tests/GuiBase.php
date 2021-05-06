<?php
/**
 * Base test class.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace tests;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;

abstract class GuiBase extends TestCase
{
	/** @var mixed Last logs. */
	public $logs;

	/** @var \Facebook\WebDriver\Remote\RemoteWebDriver Web driver. */
	protected $driver;

	/** @var bool Is login */
	protected $isLogin = false;

	/**
	 * Not success test.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param \Throwable $t
	 *
	 * @return void
	 */
	protected function onNotSuccessfulTest(\Throwable $t): void
	{
		if (isset($this->logs)) {
			echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
			\print_r($this->logs);
		}
		echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
		if (null !== $this->driver) {
			echo 'URL: ';
			$this->driver->getCurrentURL();
			echo PHP_EOL;
			echo 'Title: ';
			$this->driver->getTitle();
			echo PHP_EOL;
			file_put_contents(ROOT_DIRECTORY . '/cache/logs/selenium_source.html', $this->driver->getPageSource());
			$this->driver->takeScreenshot(ROOT_DIRECTORY . '/cache/logs/selenium_screenshot.png');
		} else {
			echo 'No $this->driver';
			print_r($t->__toString());
		}
		echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
		throw $t;
	}

	/**
	 * Setup test.
	 */
	protected function setUp(): void
	{
		parent::setUp();
		if (empty($this->driver)) {
			$capabilities = DesiredCapabilities::chrome();
			$capabilities->setCapability('chromeOptions', ['args' => ['headless', 'disable-dev-shm-usage', 'no-sandbox']]);
			$this->driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities, 5000);
		}
		if (!$this->isLogin) {
			$this->login();
		}
	}

	/**
	 * Go to URL.
	 *
	 * @param string $url
	 *
	 * @throws \ReflectionException
	 */
	public function url(string $url): void
	{
		$this->driver->get(\App\Config::main('site_URL') . $url);
	}

	/**
	 * Testing login page display.
	 */
	public function login(): void
	{
		$this->driver->get(\App\Config::main('site_URL') . 'index.php?module=Users&view=Login');
		$this->driver->findElement(WebDriverBy::id('username'))->sendKeys('demo');
		$this->driver->findElement(WebDriverBy::id('password'))->sendKeys(\Tests\Base\A_User::$defaultPassrowd);
		$this->driver->findElement(WebDriverBy::tagName('form'))->submit();
		$this->isLogin = true;
	}
}
