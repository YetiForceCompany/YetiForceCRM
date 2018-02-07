<?php
namespace App;

/**
 * Language basic class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Language
{

	/**
	 * Current language
	 * @var string|bool
	 */
	private static $language = false;

	/**
	 * Temporary language
	 * @var string|bool
	 */
	private static $temporaryLanguage = false;

	/**
	 * Short current language
	 * @var string|bool
	 */
	private static $shortLanguage = false;

	/**
	 * Pluralize cache
	 * @var array
	 */
	private static $pluralizeCache = [];

	/**
	 * Function that returns current language
	 * @return string -
	 */
	public static function getLanguage()
	{
		if (static::$temporaryLanguage) {
			return static::$temporaryLanguage;
		}
		if (static::$language) {
			return static::$language;
		}
		if (!empty(\App\Session::get('language'))) {
			$language = \App\Session::get('language');
		} else {
			$language = User::getCurrentUserModel()->getDetail('language');
		}
		return static::$language = empty($language) ? \AppConfig::main('default_language') : strtolower($language);
	}

	/**
	 * Set temporary language
	 * @param string $language
	 */
	public static function setTemporaryLanguage($language)
	{
		static::$temporaryLanguage = strtolower($language);
	}

	/**
	 * Clear temporary language
	 * @return string
	 */
	public static function clearTemporaryLanguage()
	{
		static::$temporaryLanguage = false;
	}

	/**
	 * Function that returns current language short name
	 * @return string
	 */
	public static function getShortLanguageName()
	{
		if (static::$shortLanguage) {
			return static::$shortLanguage;
		}
		preg_match("/^[a-z]+/i", static::getLanguage(), $match);
		return static::$shortLanguage = (empty($match[0])) ? 'en' : $match[0];
	}

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
	 * Functions that gets translated string with encoding html
	 * @param string $key - string which need to be translated
	 * @param string $moduleName - module scope in which the translation need to be check
	 * @return string - translated string with encoding html
	 */
	public static function translateEncodeHtml($key, $moduleName = 'Vtiger', $currentLanguage = false)
	{
		return \App\Purifier::encodeHtml(static::translate($key, $moduleName, $currentLanguage));
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
		$args = array_slice(func_get_args(), 2);
		if (is_array($args) && !empty($args)) {
			$formattedString = call_user_func_array('vsprintf', [$formattedString, $args]);
		}
		return $formattedString;
	}

	/**
	 * Translation function based on only one file
	 * @param string $key
	 * @param string $moduleName
	 * @param string|bool $language
	 * @return string
	 */
	public static function translateSingleMod($key, $moduleName = 'Vtiger', $language = false)
	{
		if (!$language) {
			$language = static::getLanguage();
		}
		$commonStrings = \Vtiger_Language_Handler::getModuleStringsFromFile($language, $moduleName);
		if (!empty($commonStrings['languageStrings'][$key])) {
			return stripslashes($commonStrings['languageStrings'][$key]);
		}
		return $key;
	}

	/**
	 * Functions that gets pluralized translated string
	 * @param string $key String which need to be translated
	 * @param string $moduleName Module scope in which the translation need to be check
	 * @param int $count Quantityu for plural determination
	 * @link https://www.i18next.com/plurals.html
	 * @link http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html?id=l10n/pluralforms#pluralforms-list
	 * @return string
	 */
	public static function translatePluralized($key, $moduleName = 'Vtiger', $count)
	{
		$currentLanguage = static::getLanguage();
		if (isset(static::$pluralizeCache[$count])) {
			$postfix = static::$pluralizeCache[$count];
		} else {
			$postfix = static::getPluralized((int) $count);
		}
		$translatedString = \Vtiger_Language_Handler::getLanguageTranslatedString($currentLanguage, $key . $postfix, $moduleName);
		// label not found in users language pack, then check in the default language pack(config.inc.php)
		if ($translatedString === null) {
			$defaultLanguage = \AppConfig::main('default_language');
			if (!empty($defaultLanguage) && strcasecmp($defaultLanguage, $currentLanguage) !== 0) {
				$translatedString = \Vtiger_Language_Handler::getLanguageTranslatedString($defaultLanguage, $key . $postfix, $moduleName);
			}
		}
		// If translation is not found then return label
		if ($translatedString === null) {
			$translatedString = $key . $postfix;
		}
		return vsprintf($translatedString, [$count]);
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

	/**
	 * Translate singular module name
	 * @param string $moduleName
	 * @return string
	 */
	public static function translateSingularModuleName($moduleName)
	{
		return static::translate("SINGLE_$moduleName", $moduleName);
	}

	/**
	 * This function returns the modified keycode to match the plural form(s) of a given language and a given count with the same pattern used by i18next JS library
	 * Global patterns for keycode are as below :
	 * - No plural form : only one non modified key is needed :)
	 * - 2 forms : unmodified key for singular values and 'key_PLURAL' for plural values
	 * - 3 or more forms : key_X with X indented for each plural form
	 * @see https://www.i18next.com/plurals.html for some examples
	 * @see http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html?id=l10n/pluralforms for whole plural rules used by getText
	 *
	 * @param float $count Quantityu for plural determination
	 * @return string Pluralized key to look for
	 */
	private static function getPluralized($count)
	{
		//Extract language code from locale with special cases
		if (strcasecmp(static::getLanguage(), 'pt_br') === 0) {
			$lang = 'pt_br';
		} else {
			$lang = static::getShortLanguageName();
		}
		//No plural form
		if (in_array($lang, ['ay', 'bo', 'cgg', 'dz', 'id', 'ja', 'jbo', 'ka', 'km', 'ko', 'lo', 'ms', 'my', 'sah', 'su', 'th', 'tt', 'ug', 'vi', 'wo', 'zh'])) {
			return '_0';
		}
		//Two plural forms
		if (in_array($lang, ['ach', 'ak', 'am', 'arn', 'br', 'fa', 'fil', 'fr', 'gun', 'ln', 'mfe', 'mg', 'mi', 'oc', 'pt_br', 'tg', 'ti', 'tr', 'uz', 'wa'])) {
			return ($count > 1) ? '_1' : '_0';
		}
		if (in_array($lang, [
				'af', 'an', 'anp', 'as', 'ast', 'az', 'bg', 'bn', 'brx', 'ca', 'da', 'de', 'doi', 'dz', 'el', 'en', 'eo', 'es', 'et', 'eu', 'ff', 'fi', 'fo', 'fur', 'fy',
				'gl', 'gu', 'ha', 'he', 'hi', 'hne', 'hu', 'hy', 'ia', 'it', 'kk', 'kl', 'kn', 'ku', 'ky', 'lb', 'mai', 'mk', 'ml', 'mn', 'mni', 'mr', 'nah', 'nap',
				'nb', 'ne', 'nl', 'nn', 'nso', 'or', 'pa', 'pap', 'pms', 'ps', 'pt', 'rm', 'rw', 'sat', 'sco', 'sd', 'se', 'si', 'so', 'son', 'sq', 'sv', 'sw',
				'ta', 'te', 'tk', 'ur', 'yo'
			])) {
			return ($count !== 1) ? '_1' : '_0';
		}
		switch ($lang) {
			case 'is':
				return ($count % 10 !== 1 || $count % 100 === 11) ? '_1' : '_0';
			case 'be':
			case 'bs':
			case 'hr':
			case 'ru':
			case 'sr':
			case 'uk':
				$i = $count % 10;
				$j = $count % 100;
				if ($i === 1 && $j !== 11) {
					return '_0';
				}
				if ($i >= 2 && $i <= 4 && ($j < 10 || $j >= 20)) {
					return '_1';
				}
				return '_2';
			case 'cs':
			case 'sk':
				if ($count === 1) {
					return '_0';
				}
				if ($count >= 2 && $count <= 4) {
					return '_1';
				}
				return '_2';
			case 'csb':
				$i = $count % 10;
				$j = $count % 100;
				if ($count === 1) {
					return '_0';
				}
				if ($i >= 2 && $i <= 4 && ($j < 10 || $j >= 20)) {
					return '_1';
				}
				return '_2';
			case 'lt':
				$i = $count % 10;
				$j = $count % 100;
				if ($i == 1 && $j != 11) {
					return '_0';
				}
				if ($i >= 2 && ($j < 10 || $j >= 20)) {
					return '_1';
				}
				return '_2';
			case 'lv':
				$i = $count % 10;
				$j = $count % 100;
				if ($i == 1 && $j != 11) {
					return '_0';
				}
				if ($count !== 0) {
					return '_1';
				}
				return '_2';
			case 'me':
				$i = $count % 10;
				$j = $count % 100;
				if ($i === 1 && $j !== 11) {
					return '_0';
				}
				if ($i >= 2 && $i <= 4 && ($j < 10 || $j >= 20)) {
					return '_1';
				}
				return '_2';
			case 'pl':
				$i = $count % 10;
				$j = $count % 100;
				if ($count === 1) {
					return '_0';
				}
				if ($i >= 2 && $i <= 4 && ($j < 10 || $j >= 20)) {
					return '_1';
				}
				return '_2';
			case 'ro':
				$j = $count % 100;
				if ($count === 1)
					return '_0';
				if ($count === 0 || ($j > 0 && $j < 20))
					return '_1';
				return '_2';
			case 'cy':
				if ($count === 1)
					return '_0';
				if ($count === 2)
					return '_1';
				if ($count !== 8 && $count !== 11)
					return '_2';
				return '_3';
			case 'gd':
				if ($count === 1 || $count === 11)
					return '_0';
				if ($count === 2 || $count === 12)
					return '_1';
				if ($count > 2 && $count < 20)
					return '_2';
				return '_3';
			case 'kw':
				if ($count === 1)
					return '_0';
				if ($count === 2)
					return '_1';
				if ($count === 3)
					return '_2';
				return '_3';
			case 'mt':
				$j = $count % 100;
				if ($count === 1)
					return '_0';
				if ($count === 0 || ($j > 1 && $j < 11))
					return '_1';
				if ($j > 10 && $j < 20)
					return '_2';
				return '_3';
			case 'sl':
				$j = $count % 100;
				if ($j === 1)
					return '_0';
				if ($j === 2)
					return '_1';
				if ($j === 3 || $j === 4)
					return '_2';
				return '_3';
			case 'ga':
				if ($count === 1)
					return '_0';
				if ($count === 2)
					return '_1';
				if ($count > 2 && $count < 7)
					return '_2';
				if ($count > 6 && $count < 11)
					return '_3';
				return '_4';
			case 'ar':
				if ($count === 0)
					return '_0';
				if ($count === 1)
					return '_1';
				if ($count === 2)
					return '_2';
				if ($count % 100 >= 3 && $count % 100 <= 10)
					return '_3';
				if ($count * 100 >= 11)
					return '_4';
				return '_5';
		}
		//Fallback if no language found
		return '';
	}

	/**
	 * Function to get the label name of the Langauge package
	 * @param string $name
	 * @return string|boolean
	 */
	public static function getLanguageLabel($name)
	{
		if (Cache::has('getLanguageLabel', $name)) {
			return Cache::get('getLanguageLabel', $name);
		}
		$label = (new \App\Db\Query())->select(['label'])->from('vtiger_language')->where(['prefix' => $name])->scalar();
		Cache::save('getLanguageLabel', $name, $label);
		return $label;
	}

	/**
	 * Function return languange
	 * @param boolean $active
	 * @param boolean $allData
	 * @return array
	 */
	public static function getAll($active = true, $allData = false)
	{
		$cacheKey = intval($active) . ':' . intval($allData);
		if (Cache::has('getAll', $cacheKey)) {
			return Cache::get('getAll', $cacheKey);
		}
		$query = (new Db\Query())->from('vtiger_language');
		if ($active) {
			$query->where(['active' => 1]);
		}
		if ($allData) {
			$output = $query->indexBy('prefix')->all();
		} else {
			$output = $query->select(['prefix', 'label'])->createCommand()->queryAllByGroup();
		}
		Cache::save('getAll', $cacheKey, $output);
		return $output;
	}
}
