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
	 * Log messages from current call.
	 *
	 * @var mixed
	 */
	public $logs = [];
	/**
	 * Facebook WebDriver connection.
	 *
	 * @var \Facebook\WebDriver\RemoteWebDriver
	 */
	public $driver;
	/**
	 * @var string current page url
	 */
	public $currentUrl;
	/**
	 * @var string current page source
	 */
	public $loadedPageSource;
	/**
	 * @var string string to search for exceptions in source
	 */
	public $sourceErrorString = 'YF_ERROR';
	/**
	 * @var string Artifacts directory path
	 */
	public $artifactsDir = 'tests' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'artifacts' . \DIRECTORY_SEPARATOR;
	protected $errorMsgTranslations = ['invalid session id' => 'Cant connect to browser window, check if is open'];

	/**
	 * Display logs to console.
	 *
	 * @codeCoverageIgnore
	 */
	protected function displayLogs()
	{
		if (!empty($this->logs)) {
			foreach ($this->logs as $log) {
				echo '--------- ' . \strtoupper($log['level']) . ' ---------' . \PHP_EOL;
				echo "URL: {$log['url']}" . \PHP_EOL;
				echo "TYPE: {$log['source']}" . \PHP_EOL;
				echo 'MSG: ' . \PHP_EOL;
				echo $log['message'] . \PHP_EOL;
				echo '-----------------------------------' . \PHP_EOL;
			}
			$this->logs = [];
		}
	}

	/**
	 * Setup connection before each test.
	 */
	public function setUp()
	{
		parent::setUp();

		$this->driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', DesiredCapabilities::chrome(), 5000);
	}

	/**
	 * Close connection and get artifacts after each test.
	 */
	public function tearDown()
	{
		if ($this->hasFailed()) {
			$this->takeScreenshot("{$this->getName()}_fail");
			$this->saveSource($this->loadedPageSource, "{$this->getName()}_fail");
		}
		$this->driver->close();
		$this->displayLogs();
		parent::tearDown();
	}

	/**
	 * Open new url in selenium.
	 *
	 * @param string $url       page url
	 * @param bool   $autoLogin Do autologin if login page detected
	 */
	public function url($url, $autoLogin = true)
	{
		$this->currentUrl = \AppConfig::main('site_URL') . $url;
		$this->driver->get($this->currentUrl);
		$this->loadedPageSource = $this->driver->getPageSource();
		if ($this->detectLoginPage() && $autoLogin) {
			$this->login(false);
			return $this->url($url, false);
		}
		$this->validateSource($this->loadedPageSource);
		$this->validateConsole();
	}

	/**
	 * Validate page source syntax and detect error.
	 *
	 * @param $source html code
	 */
	public function validateSource($source)
	{
		if (\strpos($source, $this->sourceErrorString) !== false || \strpos($source, \strtolower($this->sourceErrorString)) !== false) {
			foreach ($this->getSourceErrors($source) as $error) {
				$this->log($error['msg'], 'source', $error['level']);
			}
		}
		$this->validateHtml($source);
	}

	/**
	 * Validate html source syntax.
	 *
	 * @param string $source Html code
	 * @param bool   $parser Parser version
	 *
	 * @throws \HtmlValidator\Exception\ServerException
	 * @throws \HtmlValidator\Exception\UnknownParserException
	 * @codeCoverageIgnore
	 */
	public function validateHtml($source, $parser = false)
	{
		return;
		if (!$parser) {
			$parser = \HtmlValidator\Validator::PARSER_HTML5;
		}
		$validator = new \HtmlValidator\Validator('https://validator.nu/');
		$validator->setParser($parser);
		try {
			$result = $validator->validateDocument($source);
			if ($result->hasErrors() || $result->hasWarnings()) {
				$this->log((string)$result, 'page');
				$toFile = $this->currentUrl . \PHP_EOL;
				$toFile .= (string)$result;
				echo ('HTML validation error: ' . $this->currentUrl) . \PHP_EOL;
				$file = $this->getName() . '_' . date('Ymd_His');
				$this->takeScreenshot($file . '_htmlValidation');
				$dir = $this->artifactsDir . 'pages' . \DIRECTORY_SEPARATOR;
				if (!is_dir($dir) && !mkdir($dir, 0777, true) && !\is_dir($dir)) {
					$this->log('Artifacts "pages" dir creation error in class:' . __CLASS__, 'selenium', 'warning');
					return;
				}
				try {
					\file_put_contents("{$dir}{$file}_validation.txt", $toFile);
				} catch (\Exception $exception) {
					$this->log("Page source save error > {$dir}{$file}.html", 'page', 'error');
				}
			}
		} catch (\GuzzleHttp\Exception\RequestException $e) {
			$this->log('HTML validation skipped: ' . $e->getMessage(), 'page');
		}
	}

	/**
	 * @param string      $source Html source code
	 * @param null|string $file   file path
	 */
	public function saveSource($source, $file = null)
	{
		if (!$file) {
			$file = $this->getName() . '_' . date('Ymd_His');
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

	/**
	 * Browser console validation.
	 */
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

	/**
	 * Check if console log entry should be ignored.
	 *
	 * @param string $msg Browser console log entry
	 *
	 * @return bool
	 */
	public function ignoredBrowserError($msg)
	{
		$result = false;
		// Ignore chrome warning about password field in unsecure connection
		if (\strpos($msg, 'https://goo.gl/zmWq3m')) {
			$result = true;
		}
		return $result;
	}

	/**
	 * Search and return array of detected source errors.
	 *
	 * @param string $source
	 *
	 * @return array
	 */
	public function getSourceErrors($source)
	{
		$errors = [];
		preg_match_all('/' . $this->sourceErrorString . '>(.*?)<\/' . $this->sourceErrorString . '>/s', $source, $matchesUpper);
		preg_match_all('/' . \strtolower($this->sourceErrorString) . '>(.*?)<\/' . strtolower($this->sourceErrorString) . '>/s', $source, $matchesLower);
		foreach (\array_merge($matchesUpper[1], $matchesLower[1]) as $item) {
			if (\strpos($item, 'E_NOTICE') !== false) {
				$level = 'warning';
			} elseif (\strpos($item, 'E_WARNING') !== false) {
				$level = 'warning';
			} elseif (\strpos($item, 'E_ERROR') !== false) {
				$level = 'error';
			} else {
				$level = 'info';
			}
			$errors[] = ['msg' => $item, 'level' => $level];
		}
		return $errors;
	}

	/**
	 * Take screenshot of current page and save to specified file.
	 *
	 * @param null|string $name
	 */
	public function takeScreenshot($name = null)
	{
		if (empty($name)) {
			$name = $this->getName() . '_' . date('Ymd_His');
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

	/**
	 * Add log message.
	 *
	 * @param string $msg
	 * @param string $source
	 * @param string $level
	 */
	public function log($msg, $source = 'page', $level = 'info')
	{
		$msg = $this->translateErrorMessage($msg);
		$this->logs[] = ['url' => $this->currentUrl, 'source' => $source, 'level' => $level, 'message' => $msg, 'pageSource' => $this->loadedPageSource];
		if ($level === 'warning' || $level === 'error') {
			$this->fail($msg);
		}
	}

	/**
	 * Return more human friendly error info.
	 *
	 * @param string $msg
	 *
	 * @return string
	 */
	protected function translateErrorMessage($msg)
	{
		return $this->errorMsgTranslations[$msg] ?? $msg;
	}

	/**
	 * Check if current open page is login screen.
	 *
	 * @param string $calledFrom
	 *
	 * @return bool
	 */
	protected function detectLoginPage($calledFrom = 'Test'): bool
	{
		$return = false;
		try {
			if ($this->driver->findElement(WebDriverBy::id('login-area'))->getAttribute('id') ? true : false) {
				$this->log("Login page detected [{$calledFrom}]");
				$return = true;
			} else {
				$this->log("Login page NOT detected [{$calledFrom}]");
			}
			return $return;
		} catch (\Exception $exception) {
			$this->log("Login page NOT detected [{$calledFrom}]");
			return false;
		}
	}

	/**
	 * Perform login of user to system.
	 *
	 * @param bool $navigate
	 */
	public function login($navigate = true)
	{
		if ($navigate) {
			$this->url('index.php?module=Users&view=Login', false);
		}
		if ($this->detectLoginPage('GuiBase::login')) {
			$this->findElBy('id', 'username')->sendKeys('demo');
			$this->findElBy('id', 'password')->sendKeys(\Tests\Base\A_User::$defaultPassrowd);
			$this->findElBy('tagName', 'form')->submit();
		} else {
			$this->log('Login not possible, not on login page', 'page', 'error');
		}
	}

	/**
	 * Find element by wrapper for Facebook webdriver functions.
	 *
	 * @param string $method    Find method name
	 * @param string $condition Condition for choosen method
	 *
	 * @return object|null
	 */
	public function findElBy($method, $condition)
	{
		try {
			return $this->driver->findElement(\call_user_func_array('\Facebook\WebDriver\WebDriverBy::' . $method, [$condition]));
		} catch (\Exception $exception) {
			$this->log($this->parseFindElByError($exception->getMessage(), $method, $condition), 'selenium', 'error');
		}
	}

	/**
	 * Translate findElBy errors to more human friendly format.
	 *
	 * @param string $msg
	 * @param string $method
	 * @param string $condition
	 *
	 * @return string
	 */
	protected function parseFindElByError($msg, $method, $condition)
	{
		if (\strpos($msg, 'no such element: Unable to locate element: {"method":"') !== false) {
			$msg = "Element not found with params method: {$method}, condition: {$condition}";
		}
		return $msg;
	}
}
