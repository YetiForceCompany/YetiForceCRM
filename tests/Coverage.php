<?php
/**
 * Code coverage file.
 *
 * @package   Tests
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
	private $coverage;

	/**
	 * Get instance and Initialize.
	 *
	 * @return self
	 */
	public static function getInstance(): self
	{
		if (!isset(self::$self)) {
			\SebastianBergmann\CodeCoverage\Directory::create(ROOT_DIRECTORY . '/tests/coverages/');
			self::log('Initiation CodeCoverage...');
			$self = new self();
			$self->startTime = microtime(true);
			$self->dir = ROOT_DIRECTORY . '/tests/coverages/';
			$self->name = date('Ymd_H_i_s') . '_' . \App\Encryption::generatePassword(10);
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
			$filter->includeDirectory(ROOT_DIRECTORY . '/modules/Settings');
			$filter->includeDirectory(ROOT_DIRECTORY . '/modules');
			$filter->includeDirectory(ROOT_DIRECTORY . '/include');
			$filter->includeDirectory(ROOT_DIRECTORY . '/vtlib/Vtiger');

			$filter->excludeDirectory(ROOT_DIRECTORY . '/modules/Vtiger/pdfs');
			$filter->excludeDirectory(ROOT_DIRECTORY . '/modules/OSSMail');
			$filter->excludeDirectory(ROOT_DIRECTORY . '/modules/MailIntegration/html/outlook');

			$filter->excludeFile(ROOT_DIRECTORY . '/tests/GuiBase.php');
			$filter->excludeFile(ROOT_DIRECTORY . '/tests/Coverage.php');
			$filter->excludeFile(ROOT_DIRECTORY . '/tests/bootstrap.php');
			$filter->excludeFile(ROOT_DIRECTORY . '/tests/auto_prepend_file.php');
			$filter->excludeFile(ROOT_DIRECTORY . '/tests/codeCoverageReport.php');
			$filter->excludeFile(ROOT_DIRECTORY . '/tests/setup/docker_post_install.php');
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
		$filter = $this->getFilter();
		$driver = (new \SebastianBergmann\CodeCoverage\Driver\Selector())->forLineCoverage($filter);
		self::log('Driver: ' . $driver->nameAndVersion());
		$this->coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage($driver, $filter);
		$this->coverage->start($this->name);
		self::log('Started');
	}

	/**
	 * Stop collection of code coverage information.
	 */
	public function __destruct()
	{
		try {
			$this->coverage->stop();
			self::log('Stop');
			$writer = new \SebastianBergmann\CodeCoverage\Report\PHP();
			$writer->process($this->coverage, "{$this->dir}php/{$this->name}.php");
			self::log('Collection of code coverage time: ' . round(microtime(true) - $this->startTime, 1) . ' s.');
		} catch (\Exception $ex) {
			self::log('Collection exception !!!');
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
			foreach ($coverages as $file) {
				$this->coverage->merge(require_once $file);
				unlink($file);
			}
			$startTime = microtime(true);
			$writer = new \SebastianBergmann\CodeCoverage\Report\Clover();
			$writer->process($this->coverage, "{$this->dir}xml/{$this->name}.xml");
			self::log('Clover Report time: ' . round(microtime(true) - $startTime, 1) . ' s.');

			$startTime = microtime(true);
			$writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade();
			$writer->process($this->coverage, $this->dir . 'html/');
			self::log('Clover Html time: ' . round(microtime(true) - $startTime, 1) . ' s.');

			// $startTime = microtime(true);
			// $writer = new \SebastianBergmann\CodeCoverage\Report\Text();
			// file_put_contents("{$this->dir}coverage{$this->name}.txt", $writer->process($this->coverage));
			// self::log('Clover Text time: ' . round(microtime(true) - $startTime, 1).' s.');
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
		file_put_contents(ROOT_DIRECTORY . '/tests/coverages/codecoverage.log', date('H:i:s') . ' ' . $text . PHP_EOL, FILE_APPEND);
	}
}
