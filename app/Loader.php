<?php

/**
 * Loader.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Loader class.
 */
class Loader
{
	/**
	 * Basic module name.
	 *
	 * @var string
	 */
	private static $moduleBasic = 'Vtiger';
	/**
	 * Class map.
	 *
	 * @var array
	 */
	private static $classMap = [];

	/**
	 * Folder name to scan.
	 *
	 * @var string
	 */
	public static $loaderDir = 'custom';

	/**
	 * Register autoloader.
	 *
	 * @todo Improve
	 */
	public static function register()
	{
		$path = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . self::$loaderDir;
		if (file_exists($path)) {
			$filePath = $path . \DIRECTORY_SEPARATOR . 'autoload_classmap.php';
			if (!file_exists($filePath)) {
				$loader = new \Nette\Loaders\RobotLoader();
				$loader->addDirectory($path);
				$loader->rebuild();
				$classMap = [];
				$rootDirectory = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR;
				foreach ($loader->getIndexedClasses() as $class => $path) {
					$classMap[$class] = str_replace($rootDirectory, '', $path);
				}
				$content = '<?php return ' . Utils::varExport($classMap) . ';' . PHP_EOL;
				if (false === file_put_contents($filePath, $content, LOCK_EX)) {
					throw new Exceptions\AppException("ERR_CREATE_FILE_FAILURE||{$filePath}");
				}
			}
			self::$classMap = require_once $filePath;
			spl_autoload_register('\App\Loader::autoLoad', true, true);
		}
		require_once ROOT_DIRECTORY . '/include/Loader.php';
	}

	/**
	 * Function to auto load the required class files.
	 *
	 * @param string $className
	 *
	 * @return bool
	 */
	public static function autoLoad(string $className): bool
	{
		if ($result = (isset(self::$classMap[$className]) && file_exists(ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . self::$classMap[$className]))) {
			$result = require_once ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . self::$classMap[$className];
		}
		return $result;
	}

	/**
	 * Function to get the class name of a given Component, of given Type, for a given Module.
	 *
	 * @param string $componentType
	 * @param string $componentName
	 * @param string $moduleName
	 * @param mixed  $throwException
	 *
	 * @throws Exceptions\AppException
	 *
	 * @return string Required Class Name
	 */
	public static function getComponentClassName($componentType, $componentName, $moduleName, $throwException = true)
	{
		$moduleBase = $moduleName;
		if (false !== strpos($moduleName, ':')) {
			$parts = explode(':', $moduleName, 2);
			$moduleBase = $parts[1];
			$moduleName = "{$parts[0]}\\{$moduleBase}";
		}
		$class = "\\Modules\\{$moduleName}\\{$componentType}\\{$componentName}";
		if ($moduleBase !== self::$moduleBasic && !class_exists($class)) {
			$moduleBasic = str_replace($moduleBase, self::$moduleBasic, $moduleName);
			$class = "\\Modules\\{$moduleBasic}\\{$componentType}\\{$componentName}";
		}
		if ($throwException && !class_exists($class)) {
			\App\Log::error("Error ({$componentType}, {$componentName}, {$moduleName}): Handler not found: {$class}");
			throw new Exceptions\AppException('LBL_HANDLER_NOT_FOUND');
		}
		return $class;
	}
}
