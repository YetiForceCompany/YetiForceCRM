<?php
/**
 * Base test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace tests;

abstract class GuiBase extends \PHPUnit_Extensions_Selenium2TestCase
{
    /**
     * Whether is login.
     *
     * @var bool
     */
    protected static $isLogin = false;
    public static $browsers = [
        [
            'driver' => 'chrome',
            'host' => 'localhost',
            'port' => 4444,
            'browserName' => 'chrome',
            'sessionStrategy' => 'shared',
        ],
    ];
    public $captureScreenshotOnFailure = true;
    public $logs;
    protected $coverageScriptUrl = 'http://localhost/phpunit_coverage.php';

    public function setUp()
    {
        parent::setUp();

        $this->setBrowserUrl(\AppConfig::main('site_URL'));
        $this->setBrowser('chrome');
        $screenshotsDir = __DIR__.'/../screenshots';
        if (!file_exists($screenshotsDir)) {
            mkdir($screenshotsDir, 0777, true);
        }
        $this->listener = new \PHPUnit_Extensions_Selenium2TestCase_ScreenshotListener($screenshotsDir);
        $this->prepareSession();
        $this->login();
    }

    /**
     * @codeCoverageIgnore
     */
    public function onNotSuccessfulTest(\Throwable $e)
    {
        if ($this->logs) {
            var_export($this->logs);
        }
        $this->listener->addError($this, $e, null);
        parent::onNotSuccessfulTest($e);
    }

    /**
     * Testing login page display.
     */
    public function login()
    {
        if (!static::$isLogin) {
            $this->shareSession(true);
            $this->url('index.php');
            $this->byId('username')->value('demo');
            $this->byId('password')->value('demo');
            $this->byTag('form')->submit();

            $this->url('index.php?module=Home&view=DashBoard');
            $this->assertEquals('Home', $this->byId('module')->value());
            $this->assertEquals('DashBoard', $this->byId('view')->value());

            static::$isLogin = true;
        }
    }
}
