<?php
/**
 * Code coverage file.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @codeCoverageIgnore
 */

namespace Tests;

/**
 * Code coverage class.
 */
class Coverage
{
	/** @var self */
	private static $self;
	/** @var \SebastianBergmann\CodeCoverage\Filter */
	private $filter;
	/** @var string|float */
	public $startTime;
	/** @var string */
	public $dir;
	/** @var \SebastianBergmann\CodeCoverage\CodeCoverage */
	public $coverage;

	/**
	 * Get instance and Initialize.
	 *
	 * @return self
	 */
	public static function getInstance(): self
	{
		if (!isset(self::$self)) {
			\SebastianBergmann\CodeCoverage\Directory::create(ROOT_DIRECTORY . '/tests/coverages/');
			self::log('Initiation... ' . ($_SERVER['REQUEST_METHOD'] ?? '') . ':' . ($_SERVER['REQUEST_URI'] ?? ''));
			$self = new self();
			$self->startTime = microtime(true);
			$self->dir = ROOT_DIRECTORY . '/tests/coverages/';
			$self->name = date('H_i_s') . '_' . md5($_SERVER['REQUEST_URI'] ?? $_SERVER['REQUEST_TIME_FLOAT']) . '_' . \App\Encryption::generatePassword(10);
			$filter = $self->getFilter();
			$driver = (new \SebastianBergmann\CodeCoverage\Driver\Selector())->forLineCoverage($filter);
			self::log('Driver: ' . $driver->nameAndVersion() . ' ');
			$self->coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage($driver, $filter);
			self::$self = $self;
		}
		return self::$self;
	}

	/**
	 * Get coverage filter.
	 *
	 * @return \SebastianBergmann\CodeCoverage\Filter
	 */
	public function getFilter(): \SebastianBergmann\CodeCoverage\Filter
	{
		if (!isset($this->filter)) {
			$filter = new \SebastianBergmann\CodeCoverage\Filter();
			$filter->includeDirectory(ROOT_DIRECTORY . '/api');
			$filter->includeDirectory(ROOT_DIRECTORY . '/app');
			$filter->includeDirectory(ROOT_DIRECTORY . '/config');
			$filter->includeDirectory(ROOT_DIRECTORY . '/include');
			$filter->includeDirectory(ROOT_DIRECTORY . '/install');
			$filter->includeDirectory(ROOT_DIRECTORY . '/modules');
			$filter->includeDirectory(ROOT_DIRECTORY . '/vtlib/Vtiger');
			$filter->includeDirectory(ROOT_DIRECTORY . '/tests');

			$filter->excludeDirectory(ROOT_DIRECTORY . '/vendor');
			$filter->excludeDirectory(ROOT_DIRECTORY . '/tests/setup');
			$filter->excludeDirectory(ROOT_DIRECTORY . '/tests/coverages');
			$filter->excludeDirectory(ROOT_DIRECTORY . '/modules/Vtiger/pdfs');
			$filter->excludeDirectory(ROOT_DIRECTORY . '/modules/OSSMail');
			$filter->excludeDirectory(ROOT_DIRECTORY . '/modules/MailIntegration/html/outlook');

			$filter->excludeFile(ROOT_DIRECTORY . '/tests/GuiBase.php');
			$filter->excludeFile(ROOT_DIRECTORY . '/tests/Coverage.php');
			$this->filter = $filter;
		}
		return $this->filter;
	}

	/**
	 * Start collection of code coverage information.
	 *
	 * @return void
	 */
	public function start(): void
	{
		$this->coverage->start($this->name);
		self::log('Started ');
	}

	/**
	 * Stop collection of code coverage information.
	 */
	public function __destruct()
	{
		try {
			$this->coverage->stop();
			self::log('Stop ');
			$writer = new \SebastianBergmann\CodeCoverage\Report\PHP();
			$writer->process($this->coverage, "{$this->dir}php/{$this->name}.php");
			self::log('Collection time: ' . round(microtime(true) - $this->startTime, 1) . ' s.' . PHP_EOL);
		} catch (\Exception $ex) {
			self::log(PHP_EOL . 'Collection exception !!!' . PHP_EOL);
			self::log($ex->__toString());
		}
	}

	/**
	 * Generate report.
	 *
	 * @return void
	 */
	public function generateReport(): void
	{
		try {
			$coverages = glob("{$this->dir}/php/*.php");
			$i = 0;
			foreach ($coverages as $file) {
				$this->coverage->merge(require $file);
				rename($file, $this->dir . '' . basename($file) . '.old');
				++$i;
			}
			self::log('Number of merged files: ' . $i);
			$startTime = microtime(true);
			$writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade();
			$writer->process($this->coverage, $this->dir . 'html/');
			self::log('Clover Html time: ' . round(microtime(true) - $startTime, 1) . ' s.');

			$startTime = microtime(true);
			$writer = new \SebastianBergmann\CodeCoverage\Report\Clover();
			$clover = $writer->process($this->coverage);
			file_put_contents("{$this->dir}coverage.xml", $clover);
			file_put_contents("{$this->dir}coverage2.xml", str_replace('/var/www/html/', '/', $clover));
			file_put_contents("{$this->dir}coverage3.xml", str_replace('/var/www/html/', '/home/runner/work/YetiForceCRM/YetiForceCRM/', $clover));
			file_put_contents("{$this->dir}coverage4.xml", str_replace('/var/www/html/', '/github/workspace/', $clover));
			self::log('Clover Report time: ' . round(microtime(true) - $startTime, 1) . ' s.');
		} catch (\Exception $ex) {
			self::log('Generate report exception !!!');
			self::log($ex->__toString());
		}
		echo file_get_contents(ROOT_DIRECTORY . '/tests/coverages/codecoverage.log');
	}

	/**
	 * Log.
	 *
	 * @param string $text
	 */
	public static function log(string $text): void
	{
		file_put_contents(ROOT_DIRECTORY . '/tests/coverages/codecoverage.log', '| ' . date('H:i:s') . ' ' . $text, FILE_APPEND);
	}
}
