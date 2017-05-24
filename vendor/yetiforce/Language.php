<?php
namespace App;

/**
 * Language basic class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Language
{

	/**
	 * Functions that gets translated string
	 * @param string $key - string which need to be translated
	 * @param string $moduleName - module scope in which the translation need to be check
	 * @return string - translated string
	 */
	public static function translate($key, $moduleName = 'Vtiger', $currentLanguage = false)
	{
		return \Vtiger_Language_Handler::getTranslatedString($key, $moduleName, $currentLanguage);
	}

	/**
	 * Functions that gets translated string by $args
	 * @param string $key - string which need to be translated
	 * @param string $moduleName - module scope in which the translation need to be check
	 * @return string - translated string
	 */
	public static function translateArgs($key, $moduleName = 'Vtiger')
	{
		$formattedString = static::translate($key, $moduleName);
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		if (is_array($args) && !empty($args)) {
			$formattedString = call_user_func_array('vsprintf', [$formattedString, $args]);
		}
		return $formattedString;
	}

	/**
	 * Get singular module name
	 * @param string $moduleName
	 * @return string
	 */
	public static function getSingularModuleName($moduleName)
	{
		return "SINGLE_$moduleName";
	}
}
