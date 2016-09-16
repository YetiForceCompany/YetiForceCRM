<?php namespace includes;

/**
 * Record basic class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Language
{

	public static function translate($key, $moduleName = 'Vtiger')
	{
		return \Vtiger_Language_Handler::getTranslatedString($key, $moduleName);
	}

	public static function translateArgs($key, $moduleName = 'Vtiger')
	{
		$formattedString = self::translate($key, $moduleName);
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		if (is_array($args) && !empty($args)) {
			$formattedString = call_user_func_array('vsprintf', [$formattedString, $args]);
		}
		return $formattedString;
	}
}
