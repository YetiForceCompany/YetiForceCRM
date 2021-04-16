<?php

declare(strict_types=1);
$startTime = microtime(true);
file_put_contents(ROOT_DIRECTORY . '/tests/codecoverage.log', date('H:i:s') . ' Initiation CodeCoverage...' . PHP_EOL, FILE_APPEND);

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

file_put_contents(ROOT_DIRECTORY . '/tests/codecoverage.log', date('H:i:s') . ' CodeCoverage driver: ' . $driver->nameAndVersion() . PHP_EOL, FILE_APPEND);

file_put_contents(ROOT_DIRECTORY . '/xxxx_codeCoverageDriver2.txt', print_r([$driver], true), FILE_APPEND);
file_put_contents(ROOT_DIRECTORY . '/xxxx_codeCoverageFilter2.txt', print_r([$filter], true), FILE_APPEND);

$coverage = new CodeCoverage($driver, $filter);
file_put_contents(ROOT_DIRECTORY . '/xxxx_codeCoverage1112.txt', print_r([$coverage], true), FILE_APPEND);
$name = \App\Encryption::generatePassword(10);
$coverage->start($name);

file_put_contents(ROOT_DIRECTORY . '/tests/codecoverage.log', date('H:i:s') . ' CodeCoverage started . ' . $name . PHP_EOL, FILE_APPEND);

class YetiCodeCoverage
{
	private $driver;
	private $coverage;
	private $dir;
	private $name;
	private $startTime;

	public function __construct(array $config)
	{
		foreach ($config as $key => $value) {
			$this->{$key} = $value;
		}
	}

	public function __destruct()
	{
		try {
			$this->coverage->stop();
			$startTimeBase = microtime(true);
			file_put_contents(ROOT_DIRECTORY . '/tests/codecoverage.log', date('H:i:s') . ' stop' . PHP_EOL, FILE_APPEND);

			// $startTime = microtime(true);
			// $writer = new Report\Html\Facade();
			// $writer->process($this->coverage, $this->dir . '/tests/coverages/html/');
			// file_put_contents(ROOT_DIRECTORY . '/tests/codecoverage.log', date('H:i:s') . ' HTML ' . round(microtime(true) - $startTime, 1) . PHP_EOL, FILE_APPEND);

			// $startTime = microtime(true);
			// $writer = new Report\Xml\Facade('9.5.4');
			// $writer->process($this->coverage, $this->dir . '/tests/coverages/xml/');
			// file_put_contents(ROOT_DIRECTORY . '/tests/codecoverage.log', date('H:i:s') . ' XML ' . round(microtime(true) - $startTime, 1) . PHP_EOL, FILE_APPEND);

			$startTime = microtime(true);
			$writer = new Report\Clover();
			// file_put_contents(ROOT_DIRECTORY . '/xxxx_arguments.txt', print_r([$arguments], true), FILE_APPEND);
			$writer->process($this->coverage, "{$this->dir}/tests/coverages/xml/coverage{$this->name}.php");
			file_put_contents(ROOT_DIRECTORY . '/tests/codecoverage.log', date('H:i:s') . ' Clover ' . round(microtime(true) - $startTime, 1) . PHP_EOL, FILE_APPEND);

			// $startTime = microtime(true);
			// $writer = new Report\PHP();
			// $writer->process($this->coverage, "{$this->dir}/tests/coverages/php/coverage{$this->name}.php");
			// file_put_contents(ROOT_DIRECTORY . '/tests/codecoverage.log', date('H:i:s') . ' PHP ' . round(microtime(true) - $startTime, 1) . PHP_EOL, FILE_APPEND);

			// $startTime = microtime(true);
			// $writer = new Report\Text();
			// file_put_contents("{$this->dir}/tests/coverages/text/coverage{$this->name}.txt", $writer->process($this->coverage));
			// file_put_contents(ROOT_DIRECTORY . '/tests/codecoverage.log', date('H:i:s') . ' Text ' . round(microtime(true) - $startTime, 1) . PHP_EOL, FILE_APPEND);

			file_put_contents(ROOT_DIRECTORY . '/tests/codecoverage.log', date('H:i:s') . ' finish ' . round(microtime(true) - $startTimeBase, 1) . '/' . round(microtime(true) - $this->startTime, 1) . PHP_EOL, FILE_APPEND);
		} catch (Exception $ex) {
			file_put_contents($this->dir . '/tests/codecoverage_exception.log', $ex->__toString());
		}
	}
}

new YetiCodeCoverage([
	'driver' => $driver,
	'coverage' => $coverage,
	'dir' => ROOT_DIRECTORY,
	'name' => $name,
	'startTime' => $startTime,
]);
