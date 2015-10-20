<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

/**
 * Class to handler language translations
 */
class Vtiger_Language_Handler
{

	//Contains module language translations
	protected static $languageContainer;

	/**
	 * Functions that gets translated string
	 * @param <String> $key - string which need to be translated
	 * @param <String> $module - module scope in which the translation need to be check
	 * @return <String> - translated string
	 */
	public static function getTranslatedString($key, $module = '', $currentLanguage = '')
	{
		if (empty($currentLanguage)) {
			$currentLanguage = self::getLanguage();
		}
		//decoding for Start Date & Time and End Date & Time 
		if (!is_array($key))
			$key = decode_html($key);
		$translatedString = self::getLanguageTranslatedString($currentLanguage, $key, $module);

		// label not found in users language pack, then check in the default language pack(config.inc.php)
		if ($translatedString === null) {
			$defaultLanguage = vglobal('default_language');
			if (!empty($defaultLanguage) && strcasecmp($defaultLanguage, $currentLanguage) !== 0) {
				$translatedString = self::getLanguageTranslatedString($defaultLanguage, $key, $module);
			}
		}

		// If translation is not found then return label
		if ($translatedString === null) {
			$translatedString = $key;
		}
		return $translatedString;
	}

	/**
	 * Function returns language specific translated string
	 * @param <String> $language - en_us etc
	 * @param <String> $key - label
	 * @param <String> $module - module name
	 * @return <String> translated string or null if translation not found
	 */
	public static function getLanguageTranslatedString($language, $key, $module = '')
	{
		$moduleStrings = array();

		$module = str_replace(':', '.', $module);
		if (is_array($module))
			return null;
		$moduleStrings = self::getModuleStringsFromFile($language, $module);
		if (!empty($moduleStrings['languageStrings'][$key])) {
			return stripslashes($moduleStrings['languageStrings'][$key]);
		}
		// Lookup for the translation in base module, in case of sub modules, before ending up with common strings
		if (strpos($module, '.') > 0) {
			$baseModule = substr($module, 0, strpos($module, '.'));
			if ($baseModule == 'Settings') {
				$baseModule = 'Settings.Vtiger';
			}
			$moduleStrings = self::getModuleStringsFromFile($language, $baseModule);
			if (!empty($moduleStrings['languageStrings'][$key])) {
				return stripslashes($moduleStrings['languageStrings'][$key]);
			}
		}

		$commonStrings = self::getModuleStringsFromFile($language);
		if (!empty($commonStrings['languageStrings'][$key]))
			return stripslashes($commonStrings['languageStrings'][$key]);

		return null;
	}

	/**
	 * Functions that gets translated string for Client side
	 * @param <String> $key - string which need to be translated
	 * @param <String> $module - module scope in which the translation need to be check
	 * @return <String> - translated string
	 */
	public static function getJSTranslatedString($language, $key, $module = '')
	{
		$moduleStrings = array();

		$module = str_replace(':', '.', $module);
		$moduleStrings = self::getModuleStringsFromFile($language, $module);
		if (!empty($moduleStrings['jsLanguageStrings'][$key])) {
			return $moduleStrings['jsLanguageStrings'][$key];
		}
		// Lookup for the translation in base module, in case of sub modules, before ending up with common strings
		if (strpos($module, '.') > 0) {
			$baseModule = substr($module, 0, strpos($module, '.'));
			if ($baseModule == 'Settings') {
				$baseModule = 'Settings.Vtiger';
			}
			$moduleStrings = self::getModuleStringsFromFile($language, $baseModule);
			if (!empty($moduleStrings['jsLanguageStrings'][$key])) {
				return $moduleStrings['jsLanguageStrings'][$key];
			}
		}

		$commonStrings = self::getModuleStringsFromFile($language);
		if (!empty($commonStrings['jsLanguageStrings'][$key]))
			return $commonStrings['jsLanguageStrings'][$key];

		return $key;
	}

	/**
	 * Function that returns translation strings from file
	 * @global <array> $languageStrings - language specific string which is used in translations
	 * @param <String> $module - module Name
	 * @return <array> - array if module has language strings else returns empty array
	 */
	public static function getModuleStringsFromFile($language, $module = 'Vtiger')
	{
		$module = str_replace(':', '.', $module);
		if (empty(self::$languageContainer[$language][$module])) {
			$qualifiedName = 'languages.' . $language . '.' . $module;
			$file = Vtiger_Loader::resolveNameToPath($qualifiedName);

			$languageStrings = $jsLanguageStrings = [];
			if (file_exists($file)) {
				require $file;
				self::$languageContainer[$language][$module]['languageStrings'] = $languageStrings;
				self::$languageContainer[$language][$module]['jsLanguageStrings'] = $jsLanguageStrings;
			}
			$qualifiedName = 'custom.languages.' . $language . '.' . $module;
			$file = Vtiger_Loader::resolveNameToPath($qualifiedName);

			if (file_exists($file)) {
				require $file;
				foreach ($languageStrings as $key => $val) {
					self::$languageContainer[$language][$module]['languageStrings'][$key] = $val;
				}
				foreach ($jsLanguageStrings as $key => $val) {
					self::$languageContainer[$language][$module]['jsLanguageStrings'][$key] = $val;
				}
			}
		}
		return self::$languageContainer[$language][$module];
	}

	/**
	 * Function that returns current language
	 * @return <String> -
	 */
	public static function getLanguage()
	{
		if (vglobal('translated_language')) {
			$language = vglobal('translated_language');
		} elseif (Vtiger_Session::get('language') != '') {
			$language = Vtiger_Session::get('language');
		} else {
			$language = Users_Record_Model::getCurrentUserModel()->get('language');
		}
		$language = empty($language) ? vglobal('default_language') : $language;
		return $language;
	}

	/**
	 * Function that returns current language short name
	 * @return <String> -
	 */
	public static function getShortLanguageName()
	{
		$language = self::getLanguage();
		return substr($language, 0, 2);
	}

	/**
	 * Function returns module strings
	 * @param <String> $module - module Name
	 * @param <String> languageStrings or jsLanguageStrings
	 * @return <Array>
	 */
	public static function export($module, $type = 'languageStrings')
	{
		$userSelectedLanguage = self::getLanguage();
		$defaultLanguage = vglobal('default_language');
		$languages = array($userSelectedLanguage);
		//To merge base language and user selected language translations
		if ($userSelectedLanguage != $defaultLanguage) {
			array_push($languages, $defaultLanguage);
		}


		$resultantLanguageString = array();
		foreach ($languages as $currentLanguage) {
			$exportLangString = array();

			$moduleStrings = self::getModuleStringsFromFile($currentLanguage, $module);
			if (!empty($moduleStrings[$type])) {
				$exportLangString = $moduleStrings[$type];
			}

			// Lookup for the translation in base module, in case of sub modules, before ending up with common strings
			if (strpos($module, '.') > 0) {
				$baseModule = substr($module, 0, strpos($module, '.'));
				if ($baseModule == 'Settings') {
					$baseModule = 'Settings.Vtiger';
				}
				$moduleStrings = self::getModuleStringsFromFile($currentLanguage, $baseModule);
				if (!empty($moduleStrings[$type])) {
					$exportLangString += $commonStrings[$type];
				}
			}

			$commonStrings = self::getModuleStringsFromFile($currentLanguage);
			if (!empty($commonStrings[$type])) {
				$exportLangString += $commonStrings[$type];
			}
			$resultantLanguageString += $exportLangString;
		}

		return $resultantLanguageString;
		;
	}

	/**
	 * Function to returns all language information
	 * @return <Array>
	 */
	public static function getAllLanguages()
	{
		return Vtiger_Language::getAll();
	}

	/**
	 * Function to get the label name of the Langauge package
	 * @param <String> $name
	 */
	public static function getLanguageLabel($name)
	{
		$db = PearDatabase::getInstance();
		$languageResult = $db->pquery('SELECT label FROM vtiger_language WHERE prefix = ?', array($name));
		if ($db->num_rows($languageResult)) {
			return $db->query_result($languageResult, 0, 'label');
		}
		return false;
	}
}

function vtranslate($key, $moduleName = '')
{
	$args = func_get_args();
	$formattedString = call_user_func_array(array('Vtiger_Language_Handler', 'getTranslatedString'), $args);
	array_shift($args);
	array_shift($args);
	if (is_array($args) && !empty($args)) {
		$formattedString = call_user_func_array('vsprintf', array($formattedString, $args));
	}
	return $formattedString;
}

function vJSTranslate($key, $moduleName = '')
{
	$args = func_get_args();
	return call_user_func_array(array('Vtiger_Language_Handler', 'getJSTranslatedString'), $args);
}
