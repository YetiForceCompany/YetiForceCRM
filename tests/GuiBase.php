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
			file_put_contents(ROOT_DIRECTORY . '/cache/logs/selenium_logs.log', print_r($this->logs, true));
		}
		echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
		print_r($t->__toString());
		echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
		if (null !== $this->driver) {
			file_put_contents(ROOT_DIRECTORY . '/cache/logs/selenium_source.html', $this->driver->getPageSource());
			echo 'URL: ';
			$this->driver->getCurrentURL();
			echo PHP_EOL;
			echo 'Title: ';
			$this->driver->getTitle();
			echo PHP_EOL;
			echo 'Browser logs: ';
			print_r($this->driver->manage()->getLog('browser'));
			$this->driver->takeScreenshot(ROOT_DIRECTORY . '/cache/logs/selenium_screenshot.png');
		} else {
			echo 'No $this->driver';
		}
		echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
		throw $t;
	}

	/**
	 * Setup test.
	 *
	 * @return void
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
	 *
	 * @return void
	 */
	public function url(string $url): void
	{
		$this->driver->get(\App\Config::main('site_URL') . $url);
	}

	/**
	 * Testing login page display.
	 *
	 * @return void
	 */
	public function login(): void
	{
		$this->driver->get(\App\Config::main('site_URL') . 'index.php?module=Users&view=Login');
		$this->logs = [
			'test' => __METHOD__,
			'url' => $this->driver->getCurrentURL(),
			'getPageSource' => $this->driver->getPageSource(),
		];
		$this->driver->findElement(WebDriverBy::id('username'))->sendKeys('demo');
		$this->driver->findElement(WebDriverBy::id('password'))->sendKeys(\Tests\Base\A_User::$defaultPassrowd);
		$this->driver->findElement(WebDriverBy::tagName('form'))->submit();
		$this->isLogin = true;
	}

	/**
	 * Find error or exceptions.
	 *
	 * @return void
	 */
	public function findError(): void
	{
		$source = $this->driver->getPageSource();
		if (false !== stripos($source, 'YetiError!!!')) {
			// @codeCoverageIgnoreStart
			throw new \Exception('An error has been found');
			// @codeCoverageIgnoreEnd
		}
		if (false !== stripos($source, 'YetiForceError!!!')) {
			// @codeCoverageIgnoreStart
			throw new \Exception('An error has been found');
			// @codeCoverageIgnoreEnd
		}
		if (false !== stripos($source, 'Undefined variable:')) {
			// @codeCoverageIgnoreStart
			throw new \Exception('Undefined variable found');
			// @codeCoverageIgnoreEnd
		}
		$browserLogs = $this->driver->manage()->getLog('browser');
		if ($browserLogs) {
			/** @codeCoverageIgnoreStart */
			$log = '';
			foreach ($browserLogs as $value) {
				if ('SEVERE' === $value['level']) {
					$log .= "[{$value['level']}] {$value['message']}\n";
				}
			}
			if ($log) {
				throw new \Exception('A JavaScript error has occurred: ' . PHP_EOL . $log);
			}
			// @codeCoverageIgnoreEnd
		}
		$this->logs = [
			'test' => __METHOD__,
			'url' => $this->driver->getCurrentURL(),
			'getPageSource' => $source,
		];
	}
}
