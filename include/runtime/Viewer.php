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

class Vtiger_Viewer extends \Smarty
{
	const DEFAULTLAYOUT = 'basic';
	const DEFAULTTHEME = 'twilight';

	public static $currentLayout;
	// Turn-it on to analyze the data pushed to templates for the request.
	protected static $debugViewer = false;
	protected static $instance = false;

	/** @var string Complete template path */
	public static $completeTemplatePath;

	/**
	 * log message into the file if in debug mode.
	 *
	 * @param type $message
	 * @param type $delimiter
	 */
	protected function log($message, $delimiter = '\n')
	{
		static $file = null;
		if (null === $file) {
			$file = __DIR__ . '/../../cache/logs/viewer-debug.log';
		}
		if (self::$debugViewer) {
			file_put_contents($file, $message . $delimiter, FILE_APPEND);
		}
	}

	/**
	 * Constructor - Sets the templateDir and compileDir for the Smarty files.
	 *
	 * @param string - $media Layout/Media name
	 */
	public function __construct($media = '')
	{
		parent::__construct();
		$this->debugging = App\Config::debug('DISPLAY_DEBUG_VIEWER');

		$THISDIR = __DIR__;
		$compileDir = '';
		$templateDir = [];
		if (!empty($media)) {
			self::$currentLayout = $media;
		} else {
			self::$currentLayout = \App\Layout::getActiveLayout();
		}
		if (App\Config::performance('LOAD_CUSTOM_FILES')) {
			$templateDir[] = $THISDIR . '/../../custom/layouts/' . self::$currentLayout;
		}
		$templateDir[] = $THISDIR . '/../../layouts/' . self::$currentLayout;
		$compileDir = $THISDIR . '/../../cache/templates_c/' . self::$currentLayout;
		if (App\Config::performance('LOAD_CUSTOM_FILES')) {
			$templateDir[] = $THISDIR . '/../../custom/layouts/' . self::getDefaultLayoutName();
		}
		$templateDir[] = $THISDIR . '/../../layouts/' . self::getDefaultLayoutName();
		if (!file_exists($compileDir)) {
			mkdir($compileDir, 0755, true);
		}
		$this->setTemplateDir(array_unique($templateDir));
		$this->setCompileDir($compileDir);

		self::$debugViewer = App\Config::debug('DEBUG_VIEWER');

		// FOR SECURITY
		// Escape all {$variable} to overcome XSS
		// We need to use {$variable nofilter} to overcome double escaping
		static $debugViewerURI = false;
		if (self::$debugViewer && false === $debugViewerURI) {
			$debugViewerURI = parse_url(\App\Request::_getServer('REQUEST_URI'), PHP_URL_PATH);
			if (!empty($_POST)) {
				$debugViewerURI .= '?' . http_build_query($_POST);
			} else {
				$debugViewerURI = \App\Request::_getServer('REQUEST_URI');
			}
			$this->log("URI: $debugViewerURI, TYPE: " . \App\Request::_getServer('REQUEST_METHOD'));
		}
	}

	/**
	 * Function to get the current layout name.
	 *
	 * @return string - Current layout name if not empty, otherwise Default layout name
	 */
	public static function getLayoutName()
	{
		if (!empty(self::$currentLayout)) {
			return self::$currentLayout;
		}
		return self::getDefaultLayoutName();
	}

	/**
	 * Function to return for default layout name.
	 *
	 * @return string - Default Layout Name
	 */
	public static function getDefaultLayoutName()
	{
		return self::DEFAULTLAYOUT;
	}

	/**
	 * Function to get the module specific template path for a given template.
	 *
	 * @param string $templateName
	 * @param string $moduleName
	 *
	 * @return string - Module specific template path if exists, otherwise default template path for the given template name
	 */
	public function getTemplatePath($templateName, $moduleName = ''): string
	{
		$moduleName = str_replace(':', '/', $moduleName);
		$cacheKey = $templateName . $moduleName;
		if (\App\Cache::has('ViewerTemplatePath', $cacheKey)) {
			return \App\Cache::get('ViewerTemplatePath', $cacheKey);
		}
		foreach ($this->getTemplateDir() as $templateDir) {
			if ('AppComponents' === $moduleName && file_exists($templateDir . "components/$templateName")) {
				$filePath = "components/$templateName";
				break;
			}
			self::$completeTemplatePath = $templateDir . "modules/$moduleName/$templateName";
			if (!empty($moduleName) && file_exists(self::$completeTemplatePath)) {
				$filePath = "modules/$moduleName/$templateName";
				break;
			}
			// Fall back lookup on actual module, in case where parent module doesn't contain actual module within in (directory structure)
			if (strpos($moduleName, '/')) {
				$moduleHierarchyParts = explode('/', $moduleName);
				$actualModuleName = $moduleHierarchyParts[\count($moduleHierarchyParts) - 1];
				$baseModuleName = $moduleHierarchyParts[0];
				$fallBackOrder = [
					"$actualModuleName",
					"$baseModuleName/Vtiger",
				];
				foreach ($fallBackOrder as $fallBackModuleName) {
					$intermediateFallBackFileName = 'modules/' . $fallBackModuleName . '/' . $templateName;
					self::$completeTemplatePath = $templateDir . DIRECTORY_SEPARATOR . $intermediateFallBackFileName;
					if (file_exists(self::$completeTemplatePath)) {
						\App\Cache::save('ViewerTemplatePath', $cacheKey, $intermediateFallBackFileName, \App\Cache::LONG);
						return $intermediateFallBackFileName;
					}
				}
			}
			$filePath = "modules/Vtiger/$templateName";
		}
		\App\Cache::save('ViewerTemplatePath', $cacheKey, $filePath, \App\Cache::LONG);
		return $filePath;
	}

	/**
	 * Function to display/fetch the smarty file contents.
	 *
	 * @param string $templateName
	 * @param string $moduleName
	 * @param bool   $fetch
	 *
	 * @return string html data
	 */
	public function view($templateName, $moduleName = '', $fetch = false)
	{
		$templatePath = $this->getTemplatePath($templateName, $moduleName);
		if (\App\Cache::has('ViewerTemplateExists', $templatePath)) {
			$templateFound = \App\Cache::get('ViewerTemplateExists', $templatePath);
		} else {
			$templateFound = $this->templateExists($templatePath);
			\App\Cache::save('ViewerTemplateExists', $templatePath, $templateFound, \App\Cache::LONG);
		}
		// Logging
		if (self::$debugViewer) {
			$templatePathToLog = $templatePath;
			$qualifiedModuleName = str_replace(':', '/', $moduleName);
			// In case we found a fallback template, log both lookup and target template resolved to.
			if (!empty($moduleName) && 0 !== strpos($templatePath, "modules/$qualifiedModuleName/")) {
				$templatePathToLog = "modules/$qualifiedModuleName/$templateName > $templatePath";
			}
			$this->log("VIEW: $templatePathToLog, FOUND: " . ($templateFound ? '1' : '0'));
			foreach ($this->tpl_vars as $key => $smarty_variable) {
				// Determine type of value being pased.
				$valueType = 'literal';
				if (\is_object($smarty_variable->value)) {
					$valueType = \get_class($smarty_variable->value);
				} elseif (\is_array($smarty_variable->value)) {
					$valueType = 'array';
				}
				$this->log(sprintf('DATA: %s, TYPE: %s', $key, $valueType));
			}
		}
		// END
		if ($templateFound) {
			if (!empty(App\Config::debug('SMARTY_ERROR_REPORTING'))) {
				$this->error_reporting = App\Config::debug('SMARTY_ERROR_REPORTING');
			}
			if ($fetch) {
				return $this->fetch($templatePath);
			}
			$this->display($templatePath);
			return true;
		}
		return false;
	}

	/**
	 * Static function to get the Instance of the Class Object.
	 *
	 * @param string $media Layout/Media
	 *
	 * @return Vtiger_Viewer instance
	 */
	public static function getInstance($media = '')
	{
		if (self::$instance) {
			return self::$instance;
		}
		$instance = new self($media);
		self::$instance = $instance;
		return $instance;
	}
}
