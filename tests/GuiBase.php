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
	public $currentUrl;
	public $loadedPageSource;
	public $sourceErrorString = 'YF_ERROR';
	public $artifactsDir = 'tests' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'artifacts' . \DIRECTORY_SEPARATOR;
	protected $errorMsgTranslations = ['invalid session id' => 'Cant connect to browser window, check if is open'];

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
		parent::onNotSuccessfulTest($t);
	}

	public function setUp()
	{
		parent::setUp();

		$this->driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', DesiredCapabilities::chrome(), 5000);
		$this->login();
	}

	public function tearDown()
	{
		if ($this->hasFailed()) {
			$this->takeScreenshot("{$this->getName()}_fail");
			$this->saveSource($this->loadedPageSource, "{$this->getName()}_fail");
		}
		$this->driver->close();
		parent::tearDown();
	}

	public function url($url, $autoLogin = true)
	{
		$this->currentUrl = \AppConfig::main('site_URL') . $url;
		$this->driver->get($this->currentUrl);
		$this->loadedPageSource = $this->driver->getPageSource();
		if ($this->detectLoginPage() && $autoLogin) {
			$this->login();
			return $this->url($url, false);
		}
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

	public function saveSource($source, $file = null)
	{
		if (!$file) {
			$file = $this->getName() . '_' . date("Ymd_His");
		}
		$dir = $this->artifactsDir . 'pages' . \DIRECTORY_SEPARATOR;
		if (!is_dir($dir) && !mkdir($dir, 0777, true) && !\is_dir($dir)) {
			$this->log('Artifacts "pages" dir creation error in class:' . __CLASS__, 'selenium', 'warning');
			return;
		}
		try {
			\file_put_contents("{$dir}{$file}.html", $source);
		} catch (\Exception $exception) {
			$this->log("Page source save error > {$dir}{$file}.html", 'page', 'error');
		}
	}

	public function validateConsole()
	{
		$logs = $this->driver->manage()->getLog('browser');
		foreach ($logs as $log) {
			if ($this->ignoredBrowserError($log['message'])) {
				$log['level'] = 'info';
			}
			$this->log($log['message'], 'browser', \strtolower($log['level']));
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

	public function takeScreenshot($name = null)
	{
		if (empty($name)) {
			$name = $this->getName() . '_' . date("Ymd_His");
		}
		$dir = $this->artifactsDir . 'screenshots' . \DIRECTORY_SEPARATOR;
		if (!is_dir($dir) && !mkdir($dir, 0777, true) && !\is_dir($dir)) {
			$this->log('Artifacts "screenshots" dir creation error in class:' . __CLASS__, 'selenium', 'warning');
			return;
		}
		try {
			$this->driver->takeScreenshot("{$dir}{$name}.jpg");
		} catch (\Exception $e) {
			$this->fail('exception at selenium screenshot: ' . $this->translateErrorMessage(\substr($e->getMessage(), 0, \strpos($e->getMessage(), "\n"))));
		}
	}

	public function log($msg, $source = 'page', $level = 'info')
	{
		$msg = $this->translateErrorMessage($msg);
		$this->logs[] = ['url' => $this->currentUrl, 'source' => $source, 'level' => $level, 'message' => $msg, 'pageSource' => $this->loadedPageSource];
		if ($level === 'warning' || $level === 'error') {
			$this->fail($msg);
		}
	}

	protected function translateErrorMessage($msg)
	{
		return $this->errorMsgTranslations[$msg] ?? $msg;
	}

	protected function detectLoginPage(): bool
	{
		$return = false;
		try {
			if ($this->driver->findElement(WebDriverBy::id('login-area'))->getAttribute('id') ? true : false) {
				$this->log('Login page detected');
				$return = true;
			}
			return $return;
		} catch (\Exception $exception) {
			return false;
		}
	}

	/**
	 * Testing login page display.
	 */
	public function login()
	{
		$this->url('index.php', false);
		if ($this->detectLoginPage()) {
			$this->findElBy('id', 'username')->sendKeys('demo');
			$this->driver->findElement(WebDriverBy::id('password'))->sendKeys(\Tests\Base\A_User::$defaultPassrowd);
			$this->driver->findElement(WebDriverBy::tagName('form'))->submit();
		} else {
			$this->log('Login page not detected');
		}
	}

	public function findElBy($method, $condition)
	{
		try {
			return $this->driver->findElement(\call_user_func_array('\Facebook\WebDriver\WebDriverBy::' . $method, [$condition]));
		} catch (\Exception $exception) {
			$this->log($this->parseFindElByError($exception->getMessage(), $method, $condition), 'selenium', 'error');
		}
	}

	protected function parseFindElByError($msg, $method, $condition)
	{
		if (\strpos($msg, 'no such element: Unable to locate element: {"method":"') !== false) {
			$msg = "Element not found with params method: {$method}, condition: {$condition}";
		}
		return $msg;
	}
}
