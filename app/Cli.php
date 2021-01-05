<?php
/**
 * CLI file.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * CLI class.
 */
class Cli
{
	/** @var \League\CLImate\CLImate CLImate instance. */
	public $climate;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		$this->climate = new \League\CLImate\CLImate();
		$this->climate->clear();
		if (getmyuid() !== fileowner(__FILE__)) {
			$this->climate->to('error')->red('Something went terribly wrong.');
			return;
		}
		if (\PHP_SAPI !== 'cli') {
			$this->climate->to('error')->red('Something went terribly wrong.');
			return;
		}
		$this->climate->border('─', 200);
		$this->climate->tab(2)->lightGreen('Y e t i F o r c e     C L I');
		$this->climate->border('─', 200);

		$this->climate->arguments->add([
			'module' => [
				'prefix' => 'm',
				'description' => 'Module name',
			],
			'action' => [
				'prefix' => 'a',
				'description' => 'Module action name',
			],
			'help' => [
				'prefix' => 'h',
				'description' => 'Help',
			],
		]);
		$this->climate->arguments->parse();
		if ($this->climate->arguments->defined('help')) {
			$this->climate->usage();
		} elseif (!$this->climate->arguments->defined('module') && !$this->climate->arguments->defined('action')) {
			$this->modulesList();
		} elseif ($this->climate->arguments->defined('module') && !$this->climate->arguments->defined('action')) {
			$this->actionsList($this->climate->arguments->get('module'));
		} elseif ($this->climate->arguments->defined('module') && $this->climate->arguments->defined('action')) {
			$className = "\\App\\Cli\\{$this->climate->arguments->get('module')}";
			$instance = new $className($this);
			$this->climate->backgroundBlue()->out($instance->methods[$this->climate->arguments->get('action')]);
			\call_user_func([$instance, $this->climate->arguments->get('action')]);
		}
	}

	/**
	 * Show modules list.
	 *
	 * @return void
	 */
	public function modulesList(): void
	{
		$options = [];
		foreach (new \DirectoryIterator(ROOT_DIRECTORY . '/app/Cli') as $fileInfo) {
			if ($fileInfo->isFile() && 'Base' !== $fileInfo->getBasename('.php')) {
				$options[] = $fileInfo->getBasename('.php');
			}
		}
		$options[] = ['Exit'];
		$input = $this->climate->radio('Module:', $options);
		$module = $input->prompt();
		if ('Exit' === $module || empty($module)) {
			return;
		}
		$this->climate->clear();
		$this->actionsList($module);
	}

	/**
	 * Show actions list.
	 *
	 * @param string $module
	 *
	 * @return void
	 */
	public function actionsList(string $module): void
	{
		$className = "\\App\\Cli\\{$module}";
		$instance = new $className($this);
		$input = $this->climate->radio('Action:', array_merge($instance->methods, ['Exit' => 'Exit']));
		$action = $input->prompt();
		$this->climate->clear();
		if ('Exit' === $action) {
			$this->modulesList();
		} else {
			\call_user_func([$instance, $action]);
		}
	}
}
