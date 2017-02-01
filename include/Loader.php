<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Vtiger_Loader
{

	protected static $includeCache = [];
	protected static $includePathCache = [];
	protected static $loaderDirs = [
		'custom.modules.',
		'modules.',
		'admin.modules.',
	];

	/**
	 * Static function to resolve the qualified php filename to absolute path
	 * @param string $qualifiedName
	 * @return string Absolute File Name
	 */
	static function resolveNameToPath($qualifiedName, $fileExtension = 'php')
	{
		$allowedExtensions = array('php', 'js', 'css', 'less');
		$file = '';
		if (!in_array($fileExtension, $allowedExtensions)) {
			return '';
		}

		// TO handle loading vtiger files
		if (strpos($qualifiedName, '~') === 0) {
			$file = str_replace('~', '', $qualifiedName);
			$file = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $file;
		} else {
			$file = str_replace('.', DIRECTORY_SEPARATOR, $qualifiedName) . '.' . $fileExtension;
			$file = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $file;
		}
		return $file;
	}

	/**
	 * Function to include a given php file through qualified file name
	 * @param string $qualifiedName
	 * @param boolean $supressWarning
	 * @return boolean
	 */
	static function includeOnce($qualifiedName, $supressWarning = false)
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

		$status = -1;
		if ($supressWarning) {
			$status = @include_once $file;
		} else {
			$status = include_once $file;
		}

		$success = ($status === 0) ? false : true;

		if ($success) {
			self::$includeCache[$qualifiedName] = $file;
		}

		return $success;
	}

	static function includePath($qualifiedName)
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
	 * Function to get the class name of a given Component, of given Type, for a given Module
	 * @param string $componentType
	 * @param string $componentName
	 * @param string $moduleName
	 * @return string Required Class Name
	 * @throws \Exception\AppException
	 */
	public static function getComponentClassName($componentType, $componentName, $moduleName = 'Vtiger', $throwException = true)
	{
		// Change component type from view to views, action to actions to navigate to the right path.
		$componentTypeDirectory = strtolower($componentType) . 's';
		// Fall Back Directory & Fall Back Class
		$fallBackModuleDir = $fallBackModuleClassPath = 'Vtiger';
		// Intermediate Fall Back Directories & Classes, before relying on final fall back
		$firstFallBackModuleDir = $firstFallBackModuleClassPath = '';
		$secondFallBackDir = $secondFallBackClassPath = '';
		// Default module directory & class name
		$moduleDir = $moduleClassPath = $moduleName;
		// Change the Module directory & class, along with intermediate fall back directory and class, if module names has submodule as well
		if (strpos($moduleName, ':') > 0) {
			$moduleHierarchyParts = explode(':', $moduleName);
			$moduleDir = str_replace(':', '.', $moduleName);
			$moduleClassPath = str_replace(':', '_', $moduleName);
			$actualModule = $moduleHierarchyParts[count($moduleHierarchyParts) - 1];
			$secondFallBackModuleDir = $secondFallBackModuleClassPath = $actualModule;
			if ($actualModule != 'Users') {
				$baseModule = $moduleHierarchyParts[0];
				if ($baseModule == 'Settings')
					$baseModule = 'Settings:Vtiger';
				$firstFallBackDir = str_replace(':', '.', $baseModule);
				$firstFallBackClassPath = str_replace(':', '_', $baseModule);
			}
		}
		// Build module specific file path and class name
		$moduleSpecificComponentFilePath = Vtiger_Loader::resolveNameToPath('modules.' . $moduleDir . '.' . $componentTypeDirectory . '.' . $componentName);
		$moduleSpecificComponentClassName = $moduleClassPath . '_' . $componentName . '_' . $componentType;
		if (file_exists($moduleSpecificComponentFilePath)) {
			return $moduleSpecificComponentClassName;
		}


		// Build first intermediate fall back file path and class name
		if (!empty($firstFallBackDir) && !empty($firstFallBackClassPath)) {
			$fallBackComponentFilePath = Vtiger_Loader::resolveNameToPath('modules.' . $firstFallBackDir . '.' . $componentTypeDirectory . '.' . $componentName);
			$fallBackComponentClassName = $firstFallBackClassPath . '_' . $componentName . '_' . $componentType;

			if (file_exists($fallBackComponentFilePath)) {
				return $fallBackComponentClassName;
			}
		}

		// Build intermediate fall back file path and class name
		if (!empty($secondFallBackModuleDir) && !empty($secondFallBackModuleClassPath)) {
			$fallBackComponentFilePath = Vtiger_Loader::resolveNameToPath('modules.' . $secondFallBackModuleDir . '.' . $componentTypeDirectory . '.' . $componentName);
			$fallBackComponentClassName = $secondFallBackModuleClassPath . '_' . $componentName . '_' . $componentType;

			if (file_exists($fallBackComponentFilePath)) {
				return $fallBackComponentClassName;
			}
		}

		// Build fall back file path and class name
		$fallBackComponentFilePath = Vtiger_Loader::resolveNameToPath('modules.' . $fallBackModuleDir . '.' . $componentTypeDirectory . '.' . $componentName);
		$fallBackComponentClassName = $fallBackModuleClassPath . '_' . $componentName . '_' . $componentType;
		if (file_exists($fallBackComponentFilePath)) {
			return $fallBackComponentClassName;
		}

		if ($throwException) {
			\App\Log::error("Error Vtiger_Loader::getComponentClassName($componentType, $componentName, $moduleName): Handler not found");
			throw new \Exception\AppException('LBL_HANDLER_NOT_FOUND');
		}
		return false;
	}

	/**
	 * Function to auto load the required class files matching the directory pattern modules/xyz/types/Abc.php for class xyz_Abc_Type
	 * @param string $className
	 * @return boolean
	 */
	public static function autoLoad($className)
	{
		$parts = explode('_', $className);
		$noOfParts = count($parts);
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
					return Vtiger_Loader::includeOnce($filePath);
				}
			}
		}
		return false;
	}
}

function vimport($qualifiedName)
{
	return Vtiger_Loader::includeOnce($qualifiedName);
}

spl_autoload_register('Vtiger_Loader::autoLoad');
