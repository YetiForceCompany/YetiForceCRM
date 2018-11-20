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
	public $logs = [];
	public $driver;
	protected static $isLogin = false;
	public $currentUrl;
	public $loadedPageSource;
	public $sourceErrorString = 'YF_ERROR';

	/**
	 * @codeCoverageIgnore
	 */
	protected function onNotSuccessfulTest(\Throwable $t)
	{
		if (!empty($this->logs)) {
			echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
			echo "\n+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++\n";
			foreach ($this->logs as $log) {
				echo '--------- ' . \strtoupper($log['level']) . ' ---------' . \PHP_EOL;
				echo "URL: {$log['url']}" . \PHP_EOL;
				echo "TYPE: {$log['source']}" . \PHP_EOL;
				echo "MSG: {$log['message']}" . \PHP_EOL;
				echo "-----------------------------------" . \PHP_EOL;
			}
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
		$this->currentUrl = \AppConfig::main('site_URL') . $url;
		$this->driver->get($this->currentUrl);
		$this->loadedPageSource = $this->driver->getPageSource();

		$this->validateSource($this->loadedPageSource);
		$this->validateConsole();
	}

	public function validateSource($source)
	{
		if (\strpos($source, $this->sourceErrorString) !== false) {
			foreach ($this->getSourceErrors($source) as $error) {
				$this->log($error['msg'], 'page', $error['level']);
			}
		}
	}

	public function validateConsole()
	{
		$logs = $this->driver->manage()->getLog('browser');
		foreach ($logs as $log) {
			if (!$this->ignoredBrowserError($log['message'])) {
				$this->log($log['message'], 'browser', \strtolower($log['level']));
			}
		}
	}

	public function ignoredBrowserError($msg)
	{
		$result = false;
		// Ignore chrome warning about password field in unsecure connection
		if (\strpos($msg, 'https://goo.gl/zmWq3m')) {
			$result = true;
		}
		return $result;
	}

	public function getSourceErrors($source)
	{
		return [];
	}

	public function log($msg, $source = 'page', $level = 'info')
	{
		$this->logs[] = ['url' => $this->currentUrl, 'source' => $source, 'level' => $level, 'message' => $msg, 'pageSource' => $this->loadedPageSource];
		if ($level === 'warning' || $level === 'error') {
			echo $this->loadedPageSource;
			$this->halt();
		}
	}

	/**
	 * Testing login page display.
	 */
	public function login()
	{
		if (!static::$isLogin) {
			$this->url('index.php');
			$this->driver->findElement(WebDriverBy::id('username'))->sendKeys('demo');
			$this->driver->findElement(WebDriverBy::id('password'))->sendKeys(\Tests\Base\A_User::$defaultPassrowd);
			$this->driver->findElement(WebDriverBy::tagName('form'))->submit();
			static::$isLogin = true;
		}
	}

	public function halt()
	{
		$this->fail('Selenium test failed');
	}
}
