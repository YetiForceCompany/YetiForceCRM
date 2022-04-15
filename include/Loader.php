<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Vtiger_Loader
{
	protected static $includeCache = [];
	protected static $includePathCache = [];
	protected static $componentClassCache = [];
	protected static $loaderDirs = [
		'custom.modules.',
		'modules.',
	];

	/**
	 * Static function to resolve the qualified php filename to absolute path.
	 *
	 * @param string $qualifiedName
	 * @param mixed  $fileExtension
	 *
	 * @return string Absolute File Name
	 */
	public static function resolveNameToPath($qualifiedName, $fileExtension = 'php')
	{
		if ($file = self::resolveRelativePath($qualifiedName, $fileExtension)) {
			$file = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . ('php' !== $fileExtension ? 'public_html' . DIRECTORY_SEPARATOR : '') . $file;
		}
		return $file;
	}

	/**
	 * Static function to resolve the qualified php filename to relative path.
	 *
	 * @param string $qualifiedName
	 * @param string $fileExtension
	 *
	 * @return string
	 */
	public static function resolveRelativePath(string $qualifiedName, string $fileExtension = 'php'): string
	{
		$allowedExtensions = ['php', 'js', 'css', 'less'];
		$file = '';
		if (\in_array($fileExtension, $allowedExtensions)) {
			if (0 === strpos($qualifiedName, '~')) {
				$file = str_replace('~', '', $qualifiedName);
			} else {
				$file = str_replace('.', DIRECTORY_SEPARATOR, $qualifiedName) . '.' . $fileExtension;
			}
		}
		return $file;
	}

	/**
	 * Returns canonicalized absolute pathname for css/js files.
	 *
	 * @param string $filePath
	 * @param string $fileExtension
	 * @param array  $layoutPaths
	 *
	 * @return string
	 */
	public static function getRealPathFile(string $filePath, string $fileExtension, array $layoutPaths): string
	{
		$realPath = '';
		$checkMin = \vtlib\Functions::getMinimizationOptions($fileExtension);
		foreach ($layoutPaths as $layoutPath) {
			$realPaths = [];
			$completeFilePath = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . $layoutPath . self::resolveRelativePath($filePath, $fileExtension);
			if ($checkMin && false === strpos($completeFilePath, '.min.')) {
				$realPaths[] = substr($completeFilePath, 0, -(\strlen($fileExtension) + 1)) . ".min.{$fileExtension}";
			}
			$realPaths[] = $completeFilePath;
			foreach ($realPaths as $path) {
				if ($path && is_file($path)) {
					$realPath = $path;
					break 2;
				}
			}
		}
		return $realPath;
	}

	/**
	 * Function to include a given php file through qualified file name.
	 *
	 * @param string $qualifiedName
	 *
	 * @return bool
	 */
	public static function includeOnce($qualifiedName)
	{
		if (isset(self::$includeCache[$qualifiedName])) {
			return true;
		}

		$file = self::resolveNameToPath($qualifiedName);

		if (!file_exists($file)) {
			return false;
		}

		// Check file inclusion before including it
		\vtlib\Deprecated::checkFileAccessForInclusion($file);

		$status = include_once $file;

		$success = (0 !== $status);

		if ($success) {
			self::$includeCache[$qualifiedName] = $file;
		}
		return $success;
	}

	public static function includePath($qualifiedName)
	{
		// Already included?
		if (isset(self::$includePathCache[$qualifiedName])) {
			return true;
		}

		$path = realpath(self::resolveNameToPath($qualifiedName));
		self::$includePathCache[$qualifiedName] = $path;

		set_include_path($path . PATH_SEPARATOR . get_include_path());

		return true;
	}

	/**
	 * Function to get the class name of a given Component, of given Type, for a given Module.
	 *
	 * @param string $componentType
	 * @param string $componentName
	 * @param string $moduleName
	 * @param mixed  $throwException
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return string Required Class Name
	 */
	public static function getComponentClassName($componentType, $componentName, $moduleName = 'Vtiger', $throwException = true)
	{
		$cacheKey = "$componentType|$componentName|$moduleName";
		if (isset(self::$componentClassCache[$cacheKey])) {
			return self::$componentClassCache[$cacheKey];
		}
		// Change component type from view to views, action to actions to navigate to the right path.
		$componentTypeDirectory = strtolower($componentType) . 's';
		// Change the Module directory & class, along with intermediate fall back directory and class, if module names has submodule as well
		if (false !== strpos($moduleName, ':')) {
			$load = [
				str_replace(':', '_', $moduleName) => str_replace(':', '.', $moduleName),
			];
			$moduleHierarchyParts = explode(':', $moduleName);
			$actualModule = $moduleHierarchyParts[\count($moduleHierarchyParts) - 1];
			if ('Users' !== $actualModule) {
				$baseModule = $moduleHierarchyParts[0];
				if ('Settings' === $baseModule) {
					$baseModule = 'Settings:Vtiger';
				}
				$load[str_replace(':', '_', $baseModule)] = str_replace(':', '.', $baseModule);
			}
			$load[$actualModule] = $actualModule;
			$load['Vtiger'] = 'Vtiger';
		} else {
			$load = [
				$moduleName => $moduleName,
				'Vtiger' => 'Vtiger',
			];
		}
		foreach ($load as $classPath => $classDir) {
			foreach (self::$loaderDirs as $dir) {
				if (file_exists(self::resolveNameToPath("$dir$classDir.$componentTypeDirectory.$componentName"))) {
					return self::$componentClassCache[$cacheKey] = "{$classPath}_{$componentName}_{$componentType}";
				}
			}
		}
		if ($throwException) {
			\App\Log::error("Error Vtiger_Loader::getComponentClassName($componentType, $componentName, $moduleName): Handler not found");
			throw new \App\Exceptions\AppException('LBL_HANDLER_NOT_FOUND');
		}
		return false;
	}

	/**
	 * Function to auto load the required class files matching the directory pattern modules/xyz/types/Abc.php for class xyz_Abc_Type.
	 *
	 * @param string $className
	 *
	 * @return bool
	 */
	public static function autoLoad($className)
	{
		$parts = explode('_', $className);
		$noOfParts = \count($parts);
		if ($noOfParts > 2) {
			foreach (self::$loaderDirs as $filePath) {
				// Append modules and sub modules names to the path
				for ($i = 0; $i < ($noOfParts - 2); ++$i) {
					$filePath .= $parts[$i] . '.';
				}

				$fileName = $parts[$noOfParts - 2];
				$fileComponentName = strtolower($parts[$noOfParts - 1]) . 's';
				$filePath .= $fileComponentName . '.' . $fileName;
				if (file_exists(self::resolveNameToPath($filePath))) {
					return self::includeOnce($filePath);
				}
			}
		}
		return false;
	}
}

spl_autoload_register('Vtiger_Loader::autoLoad');
