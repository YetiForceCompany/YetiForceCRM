<?php
/**
 * Cleaner cli file.
 *
 * @package Cli
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Cli;

/**
 * Cleaner cli class.
 */
class Cleaner extends Base
{
	/** {@inheritdoc} */
	public $moduleName = 'Cleaner';

	/** @var string[] Methods list */
	public $methods = [
		'logs' => 'Delete all logs',
		'session' => 'Delete all sessions',
		'cacheData' => 'Cache data',
		'cacheFiles' => 'Cache files',
	];

	/**
	 * Delete all logs.
	 *
	 * @return void
	 */
	public function logs(): void
	{
		$i = 0;
		$this->climate->bold('Removing all logs...');
		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(ROOT_DIRECTORY . '/cache/logs', \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if ($item->isFile() && 'index.html' !== $item->getBasename()) {
				$this->climate->bold($iterator->getSubPathName() . ' - ' . \vtlib\Functions::showBytes($item->getSize()));
				unlink($item->getPathname());
				++$i;
			}
		}
		$this->climate->lightYellow()->border('─', 200);
		$this->climate->bold('Number of deleted log files: ' . $i);
		$this->climate->lightYellow()->border('─', 200);
	}

	/**
	 * Delete all session.
	 *
	 * @return void
	 */
	public function session(): void
	{
		$this->climate->bold('Removing all sessions...');
		\App\Session::load();
		$this->climate->bold('Number of deleted sessions: ' . \App\Session::cleanAll());
		$this->climate->lightYellow()->border('─', 200);
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('Cleaner');
		}
	}

	/**
	 * Clear cache data.
	 *
	 * @return void
	 */
	public function cacheData(): void
	{
		$this->climate->bold('Clear: ' . \App\Cache::clear());
		$this->climate->bold('Clear opcache: ' . \App\Cache::clearOpcache());
		$this->climate->lightYellow()->border('─', 200);
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('Cleaner');
		}
	}

	/**
	 * Clear cache files.
	 *
	 * @return void
	 */
	public function cacheFiles(): void
	{
		$stats = \App\Cache::clearTemporaryFiles('now');
		$this->climate->bold(" - files: {$stats['counter']} , size: " . \vtlib\Functions::showBytes($stats['size']));
		$this->climate->lightYellow()->border('─', 200);
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('Cleaner');
		}
	}
}
