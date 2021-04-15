<?php

declare(strict_types=1);

// echo 'Initiation CodeCoverage...' . PHP_EOL;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report;

$filter = new Filter();
$filter->includeDirectory(ROOT_DIRECTORY . '/api');
$filter->includeDirectory(ROOT_DIRECTORY . '/app');
$filter->includeDirectory(ROOT_DIRECTORY . '/modules/Settings');
$filter->includeDirectory(ROOT_DIRECTORY . '/modules');
$filter->includeDirectory(ROOT_DIRECTORY . '/include');
$filter->includeDirectory(ROOT_DIRECTORY . '/vtlib/Vtiger');

$filter->excludeDirectory(ROOT_DIRECTORY . '/modules/Vtiger/pdfs');
$filter->excludeDirectory(ROOT_DIRECTORY . '/modules/OSSMail');
$filter->excludeDirectory(ROOT_DIRECTORY . '/modules/MailIntegration/html/outlook');

$filter->excludeFile(ROOT_DIRECTORY . '/tests/GuiBase.php');
$filter->excludeFile(ROOT_DIRECTORY . '/tests/codecoverage.php');
$filter->excludeFile(ROOT_DIRECTORY . '/tests/bootstrap.php');
$filter->excludeFile(ROOT_DIRECTORY . '/tests/setup/docker_post_install.php');

$driver = (new Selector())->forLineCoverage($filter);
// echo 'CodeCoverage driver: ' . $driver->nameAndVersion() . PHP_EOL;
$coverage = new CodeCoverage($driver, $filter);
$name = \App\Encryption::generatePassword(10);
$coverage->start($name);
// echo 'CodeCoverage started' . PHP_EOL;
class YetiCodeCoverage
{
	private $driver;
	private $coverage;
	private $dir;
	private $name;

	public function __construct(array $config)
	{
		foreach ($config as $key => $value) {
			$this->{$key} = $value;
		}
	}

	public function __destruct()
	{
		echo 'CodeCoverage stop' . PHP_EOL;
		try {
			$this->coverage->stop();
			$startTime = microtime(true);
			echo 'CodeCoverage driver: ' . $this->driver->nameAndVersion() . PHP_EOL;

			// $writer = new Report\Html\Facade();
			// $writer->process($this->coverage, $this->dir . '/tests/coverages/html/');

			$writer = new Report\Xml\Facade('9.5.4');
			$writer->process($this->coverage, $this->dir . '/tests/coverages/xml/');

			// $writer = new Report\PHP();
			// $writer->process($this->coverage, "{$this->dir}/tests/coverages/php/coverage{$this->name}.php");

			// $writer = new Report\Text();
			// file_put_contents("{$this->dir}/tests/coverages/text/coverage{$this->name}.txt", $writer->process($this->coverage));
			echo 'CodeCoverage finish ' . round(microtime(true) - $startTime, 1) . PHP_EOL;
		} catch (Exception $ex) {
			file_put_contents($this->dir . '/tests/coverages/exception.log', $ex);
		}
	}
}

new YetiCodeCoverage([
	'driver' => $driver,
	'coverage' => $coverage,
	'dir' => ROOT_DIRECTORY,
	'name' => $name,
]);
