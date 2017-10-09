<?php

/**
 * Base test class
 * @package YetiForce.Test
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class GuiBase extends PHPUnit_Extensions_Selenium2TestCase
{

	protected $captureScreenshotOnFailure = TRUE;

	protected function setUp()
	{
		parent::setUp();

		$this->setBrowserUrl('http://127.0.0.1/');
		$this->setBrowser('chrome');

		$screenshotsDir = __DIR__ . '/../screenshots';
		if (!file_exists($screenshotsDir)) {
			mkdir($screenshotsDir, 0777, true);
		}
		$this->listener = new PHPUnit_Extensions_Selenium2TestCase_ScreenshotListener($screenshotsDir);
		$this->prepareSession();
	}

	public function onNotSuccessfulTest(Throwable $e)
	{
		$this->listener->addError($this, $e, null);
		parent::onNotSuccessfulTest($e);
	}
}
