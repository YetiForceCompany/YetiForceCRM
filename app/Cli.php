<?php
/**
 * CLI file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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

	/** @var bool Php support exec */
	public $exec = true;

	/**
	 * Construct.
	 */
	public function __construct()
	{
		$this->exec = \function_exists('exec');
		$this->climate = new \League\CLImate\CLImate();
		if (!$this->exec) {
			$this->climate->setUtil(new \League\CLImate\Util\UtilFactory(new class() extends \League\CLImate\Util\System\System {
				public function width()
				{
					return 120;
				}

				public function height()
				{
					return 40;
				}

				protected function systemHasAnsiSupport()
				{
					return true;
				}

				public function exec($command, $full = false)
				{
					return '';
				}
			}));
		}
		$this->climate->clear();
		if (\function_exists('getmyuid') && getmyuid() !== fileowner(__FILE__)) {
			$this->climate->to('error')->lightRed('Error:  YetiForce CLI works only on the OS user who owns the CRM files');
			return;
		}
		if (\PHP_SAPI !== 'cli') {
			$this->climate->to('error')->lightRed('Error: YetiForce CLI only works from the operating system console (CLI)');
			return;
		}
		$this->climate->lightGreen()->border('─', 200);
		$this->climate->tab(2)->lightGreen('Y e t i F o r c e     C L I');
		$this->climate->lightGreen()->border('─', 200);
		$this->climate->white('Version: ' . Version::get() . ' | CRM URL: ' . \Config\Main::$site_URL);
		$this->climate->lightGreen()->border('─', 200);
		\App\User::setCurrentUserId(\Users::getActiveAdminId());
		\App\Language::setTemporaryLanguage('en-US');

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
			$this->showHelp();
			$this->climate->usage();
		} elseif ($this->climate->arguments->defined('module') && !$this->climate->arguments->defined('action') && !empty($this->climate->arguments->get('module'))) {
			$this->actionsList($this->climate->arguments->get('module'));
		} elseif ($this->climate->arguments->defined('module') && $this->climate->arguments->defined('action')) {
			$className = "\\App\\Cli\\{$this->climate->arguments->get('module')}";
			$instance = new $className($this);
			if (!method_exists($instance, $this->climate->arguments->get('action'))) {
				$this->climate->to('error')->lightRed("Error: Action '{$this->climate->arguments->get('action')}' does not exist in '{$this->climate->arguments->get('module')}'");
				return;
			}
			$this->climate->backgroundBlue()->out($instance->methods[$this->climate->arguments->get('action')]);
			$this->climate->border('─', 200);
			\call_user_func([$instance, $this->climate->arguments->get('action')]);
		} else {
			$this->modulesList();
		}
	}

	/**
	 * Show modules list.
	 *
	 * @return void
	 */
	public function modulesList(): void
	{
		if (!$this->exec) {
			$this->showHelp();
			$this->climate->usage();
			return;
		}
		$modules = $this->getModulesList();
		$modules['Exit'] = 'Exit';
		$input = $this->climate->radio('Module:', $modules);
		$module = $input->prompt();
		if ('Exit' === $module || empty($module)) {
			return;
		}
		$this->climate->clear();
		$this->actionsList($module);
	}

	/**
	 * Get modules list.
	 *
	 * @return string[]
	 */
	private function getModulesList(): array
	{
		$modules = [];
		foreach (new \DirectoryIterator(ROOT_DIRECTORY . '/app/Cli') as $fileInfo) {
			if ($fileInfo->isFile() && 'Base' !== $fileInfo->getBasename('.php')) {
				$module = $fileInfo->getBasename('.php');
				$className = "\\App\\Cli\\{$module}";
				$instance = new $className($this);
				$modules[$module] = $instance->moduleName;
			}
		}
		return $modules;
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
		if (!class_exists($className)) {
			$this->climate->to('error')->lightRed("Error: Module '$module' does not exist");
			return;
		}
		if (!$this->exec) {
			$this->showHelp();
			$this->climate->usage();
			return;
		}
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

	/**
	 * Show help.
	 *
	 * @return void
	 */
	private function showHelp(): void
	{
		if ($this->climate->arguments->defined('module')) {
			$module = $this->climate->arguments->get('module');
			$className = "\\App\\Cli\\{$module}";
			if (!class_exists($className)) {
				$this->climate->to('error')->lightRed("Error: Module '{$this->climate->arguments->get('module')}' does not exist");
				return;
			}
			$instance = new $className($this);
			if ($this->climate->arguments->defined('action') && !empty($this->climate->arguments->get('action'))) {
				if (!method_exists($instance, $this->climate->arguments->get('action'))) {
					$this->climate->to('error')->lightRed("Error: Action '{$this->climate->arguments->get('action')}' does not exist in '{$this->climate->arguments->get('module')}'");
					return;
				}
				$instance->helpMode = true;
				\call_user_func([$instance, $this->climate->arguments->get('action')]);
			} else {
				$this->climate->white('Action list for module ' . $this->climate->arguments->get('module'));
				$this->climate->columns(array_merge([' > Action name <' => ' > Description <'], $instance->methods));
				$this->climate->lightGreen()->border('─', 200);
				foreach (array_keys($instance->methods) as $method) {
					$this->climate->white("php cli.php -m $module -a $method");
				}
				$this->climate->lightGreen()->border('─', 200);
			}
		} else {
			$modules = $this->getModulesList();
			$modules = array_keys($modules);
			$this->climate->white('Modules list:')->columns($modules);
			$this->climate->lightGreen()->border('─', 200);
			foreach ($modules as $module) {
				$this->climate->white("php cli.php -m $module");
			}
			$this->climate->lightGreen()->border('─', 200);
		}
	}
}
