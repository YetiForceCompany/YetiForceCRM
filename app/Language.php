<?php

namespace App;

/**
 * Language basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Adrian Koń <a.kon@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Language
{
	/**
	 * Default language code.
	 */
	public const DEFAULT_LANG = 'en-US';

	/**
	 * Allowed types of language variables.
	 */
	const LANG_TYPE = ['php', 'js'];

	/**
	 * Language files format.
	 */
	const FORMAT = 'json';
	/**
	 * Custom language directory.
	 *
	 * @var string
	 */
	public static $customDirectory = 'custom';
	/**
	 * Current language.
	 *
	 * @var string|bool
	 */
	private static $language = false;

	/**
	 * Temporary language.
	 *
	 * @var string|bool
	 */
	private static $temporaryLanguage = false;

	/**
	 * Short current language.
	 *
	 * @var string|bool
	 */
	private static $shortLanguage = false;

	/**
	 * Pluralize cache.
	 *
	 * @var array
	 */
	private static $pluralizeCache = [];

	/**
	 * Contains module language translations.
	 *
	 * @var array
	 */
	protected static $languageContainer;

	/**
	 * Function that returns current language.
	 *
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
		return static::$language = empty($language) ? \AppConfig::main('default_language') : $language;
	}

	/**
	 * Get IETF language tag.
	 *
	 * @see https://en.wikipedia.org/wiki/IETF_language_tag
	 *
	 * @return string
	 */
	public static function getLanguageTag($separator = '_')
	{
		return str_replace('-', $separator, static::getLanguage());
	}

	/**
	 * Set temporary language.
	 *
	 * @param string $language
	 */
	public static function setTemporaryLanguage($language)
	{
		static::$temporaryLanguage = $language;
	}

	/**
	 * Clear temporary language.
	 *
	 * @return string
	 */
	public static function clearTemporaryLanguage()
	{
		static::$temporaryLanguage = false;
	}

	/**
	 * Function that returns current language short name.
	 *
	 * @return string
	 */
	public static function getShortLanguageName()
	{
		if (static::$shortLanguage) {
			return static::$shortLanguage;
		}
		preg_match('/^[a-z]+/i', static::getLanguage(), $match);
		return static::$shortLanguage = (empty($match[0])) ? \Locale::getPrimaryLanguage(self::DEFAULT_LANG) : $match[0];
	}

	/**
	 * Functions that gets translated string.
	 *
	 * @param string      $key        - string which need to be translated
	 * @param string      $moduleName - module scope in which the translation need to be check
	 * @param bool|string $language   - language of translation
	 *
	 * @return string - translated string
	 */
	public static function translate($key, $moduleName = '_Base', $language = false, $encode = true)
	{
		if (empty($key)) { // nothing to translate
			return $key;
		}
		if (!$language || ($language && strlen($language) !== 5)) {
			$language = static::getLanguage();
		}
		if (is_array($moduleName)) {
			Log::warning('Invalid module name - module: ' . var_export($moduleName, true));
			return $key;
		}
		if (is_numeric($moduleName)) { // ok, we have a tab id, lets turn it into name
			$moduleName = Module::getModuleName($moduleName);
		} else {
			$moduleName = str_replace([':', '.'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $moduleName);
		}
		static::loadLanguageFile($language, $moduleName);
		if (isset(static::$languageContainer[$language][$moduleName]['php'][$key])) {
			if ($encode) {
				return \nl2br(Purifier::encodeHtml(static::$languageContainer[$language][$moduleName]['php'][$key]));
			}
			return \nl2br(static::$languageContainer[$language][$moduleName]['php'][$key]);
		}
		// Lookup for the translation in base module, in case of sub modules, before ending up with common strings
		if (strpos($moduleName, 'Settings') === 0) {
			$base = 'Settings' . DIRECTORY_SEPARATOR . '_Base';
			static::loadLanguageFile($language, $base);
			if (isset(static::$languageContainer[$language][$base]['php'][$key])) {
				if ($encode) {
					return \nl2br(Purifier::encodeHtml(static::$languageContainer[$language][$base]['php'][$key]));
				}
				return \nl2br(static::$languageContainer[$language][$base]['php'][$key]);
			}
		}
		static::loadLanguageFile($language);
		if (isset(static::$languageContainer[$language]['_Base']['php'][$key])) {
			if ($encode) {
				return \nl2br(Purifier::encodeHtml(static::$languageContainer[$language]['_Base']['php'][$key]));
			}
			return \nl2br(static::$languageContainer[$language]['_Base']['php'][$key]);
		}
		if (\App\Config::performance('recursiveTranslate') && static::DEFAULT_LANG !== $language) {
			return static::translate($key, $moduleName, static::DEFAULT_LANG, $encode);
		}
		\App\Log::info("Cannot translate this: '$key' for module '$moduleName', lang: $language");
		return $key;
	}

	/**
	 * Functions that gets translated string with encoding html.
	 *
	 * @param string $key        - string which need to be translated
	 * @param string $moduleName - module scope in which the translation need to be check
	 *
	 * @return string - translated string with encoding html
	 */
	public static function translateEncodeHtml($key, $moduleName = '_Base', $currentLanguage = false)
	{
		return \App\Purifier::encodeHtml(static::translate($key, $moduleName, $currentLanguage));
	}

	/**
	 * Functions that gets translated string by $args.
	 *
	 * @param string $key        - string which need to be translated
	 * @param string $moduleName - module scope in which the translation need to be check
	 *
	 * @return string - translated string
	 */
	public static function translateArgs($key, $moduleName = '_Base')
	{
		$formattedString = static::translate($key, $moduleName);
		$args = array_slice(func_get_args(), 2);
		if (is_array($args) && !empty($args)) {
			$formattedString = call_user_func_array('vsprintf', [$formattedString, $args]);
		}
		return $formattedString;
	}

	/**
	 * Functions that gets pluralized translated string.
	 *
	 * @param string $key        String which need to be translated
	 * @param string $moduleName Module scope in which the translation need to be check
	 * @param int    $count      Quantityu for plural determination
	 *
	 * @link https://www.i18next.com/plurals.html
	 * @link http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html?id=l10n/pluralforms#pluralforms-list
	 *
	 * @return string
	 */
	public static function translatePluralized($key, $moduleName, $count)
	{
		if (isset(static::$pluralizeCache[$count])) {
			$postfix = static::$pluralizeCache[$count];
		} else {
			$postfix = static::getPluralized((int) $count);
		}
		return vsprintf(static::translate($key . $postfix, $moduleName), [$count]);
	}

	/**
	 * Translation function based on only one file.
	 *
	 * @param string      $key
	 * @param string      $moduleName
	 * @param string|bool $language
	 *
	 * @return string
	 */
	public static function translateSingleMod($key, $moduleName = '_Base', $language = false)
	{
		if (!$language) {
			$language = static::getLanguage();
		}
		$moduleName = str_replace([':', '.'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $moduleName);
		static::loadLanguageFile($language, $moduleName);
		if (isset(static::$languageContainer[$language][$moduleName]['php'][$key])) {
			return Purifier::encodeHtml(static::$languageContainer[$language][$moduleName]['php'][$key]);
		}
		return $key;
	}

	/**
	 * Get singular module name.
	 *
	 * @param string $moduleName
	 *
	 * @return string
	 */
	public static function getSingularModuleName($moduleName)
	{
		return "SINGLE_$moduleName";
	}

	/**
	 * Translate singular module name.
	 *
	 * @param string $moduleName
	 *
	 * @return string
	 */
	public static function translateSingularModuleName($moduleName)
	{
		return static::translate("SINGLE_$moduleName", $moduleName);
	}

	/**
	 * Load language file.
	 *
	 * @param string $language
	 * @param string $moduleName
	 */
	public static function loadLanguageFile($language, $moduleName = '_Base')
	{
		if (!isset(static::$languageContainer[$language][$moduleName])) {
			if (Cache::has('LanguageFiles', $language . $moduleName)) {
				static::$languageContainer[$language][$moduleName] = Cache::get('LanguageFiles', $language . $moduleName);
			} else {
				static::$languageContainer[$language][$moduleName] = [];
				$file = DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $moduleName . '.' . static::FORMAT;
				$langFile = ROOT_DIRECTORY . $file;
				if (file_exists($langFile)) {
					static::$languageContainer[$language][$moduleName] = Json::decode(file_get_contents($langFile), true) ?? [];
				}
				$langCustomFile = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . static::$customDirectory . $file;
				if (file_exists($langCustomFile)) {
					$translation = Json::decode(file_get_contents($langCustomFile), true) ?? [];
					foreach ($translation as $type => $rows) {
						foreach ($rows as $key => $val) {
							static::$languageContainer[$language][$moduleName][$type][$key] = $val;
						}
					}
				}
				if (!file_exists($langFile) && !file_exists($langCustomFile)) {
					\App\Log::info("Language file does not exist, module: $moduleName ,language: $language");
				}
				Cache::save('LanguageFiles', $language . $moduleName, static::$languageContainer[$language][$moduleName], Cache::LONG);
			}
		}
	}

	/**
	 * Get language from file.
	 *
	 * @param string $moduleName
	 * @param string $language
	 *
	 * @return array
	 */
	public static function getFromFile($moduleName, $language)
	{
		static::loadLanguageFile($language, $moduleName);
		if (isset(static::$languageContainer[$language][$moduleName])) {
			return static::$languageContainer[$language][$moduleName];
		}
	}

	/**
	 * Functions that gets translated string.
	 *
	 * @param string $moduleName
	 *
	 * @return string[]
	 */
	public static function getJsStrings($moduleName)
	{
		$language = static::getLanguage();
		$moduleName = str_replace([':', '.'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $moduleName);
		static::loadLanguageFile($language, $moduleName);
		$return = [];
		if (isset(static::$languageContainer[$language][$moduleName]['js'])) {
			$return = static::$languageContainer[$language][$moduleName]['js'];
		}
		if (strpos($moduleName, 'Settings') === 0) {
			$base = 'Settings' . DIRECTORY_SEPARATOR . '_Base';
			static::loadLanguageFile($language, $base);
			if (isset(static::$languageContainer[$language][$base]['js'])) {
				$return = array_merge(static::$languageContainer[$language][$base]['js'], $return);
			}
		}
		static::loadLanguageFile($language);
		if (isset(static::$languageContainer[$language]['_Base']['js'])) {
			$return = array_merge(static::$languageContainer[$language]['_Base']['js'], $return);
		}
		return $return;
	}

	/**
	 * This function returns the modified keycode to match the plural form(s) of a given language and a given count with the same pattern used by i18next JS library
	 * Global patterns for keycode are as below :
	 * - No plural form : only one non modified key is needed :)
	 * - 2 forms : unmodified key for singular values and 'key_PLURAL' for plural values
	 * - 3 or more forms : key_X with X indented for each plural form.
	 *
	 * @see https://www.i18next.com/plurals.html for some examples
	 * @see http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html?id=l10n/pluralforms for whole plural rules used by getText
	 *
	 * @param float $count Quantityu for plural determination
	 *
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
			'ta', 'te', 'tk', 'ur', 'yo',
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
				if ($count === 1) {
					return '_0';
				}
				if ($count === 0 || ($j > 0 && $j < 20)) {
					return '_1';
				}

				return '_2';
			case 'cy':
				if ($count === 1) {
					return '_0';
				}
				if ($count === 2) {
					return '_1';
				}
				if ($count !== 8 && $count !== 11) {
					return '_2';
				}

				return '_3';
			case 'gd':
				if ($count === 1 || $count === 11) {
					return '_0';
				}
				if ($count === 2 || $count === 12) {
					return '_1';
				}
				if ($count > 2 && $count < 20) {
					return '_2';
				}

				return '_3';
			case 'kw':
				if ($count === 1) {
					return '_0';
				}
				if ($count === 2) {
					return '_1';
				}
				if ($count === 3) {
					return '_2';
				}

				return '_3';
			case 'mt':
				$j = $count % 100;
				if ($count === 1) {
					return '_0';
				}
				if ($count === 0 || ($j > 1 && $j < 11)) {
					return '_1';
				}
				if ($j > 10 && $j < 20) {
					return '_2';
				}

				return '_3';
			case 'sl':
				$j = $count % 100;
				if ($j === 1) {
					return '_0';
				}
				if ($j === 2) {
					return '_1';
				}
				if ($j === 3 || $j === 4) {
					return '_2';
				}

				return '_3';
			case 'ga':
				if ($count === 1) {
					return '_0';
				}
				if ($count === 2) {
					return '_1';
				}
				if ($count > 2 && $count < 7) {
					return '_2';
				}
				if ($count > 6 && $count < 11) {
					return '_3';
				}

				return '_4';
			case 'ar':
				if ($count === 0) {
					return '_0';
				}
				if ($count === 1) {
					return '_1';
				}
				if ($count === 2) {
					return '_2';
				}
				if ($count % 100 >= 3 && $count % 100 <= 10) {
					return '_3';
				}
				if ($count * 100 >= 11) {
					return '_4';
				}

				return '_5';
			default:
				return '';
		}
	}

	/**
	 * Function to get the label name of the Langauge package.
	 *
	 * @param string $prefix
	 *
	 * @return string|bool
	 */
	public static function getLanguageLabel(string $prefix)
	{
		return static::getLangInfo($prefix)['name'] ?? null;
	}

	/**
	 * Function return languages data.
	 *
	 * @param bool $active
	 * @param bool $allData
	 *
	 * @return array
	 */
	public static function getAll(bool $active = true, bool $allData = false)
	{
		$cacheKey = $active ? 'Active' : 'All';
		if (Cache::has('getAllLanguages', $cacheKey)) {
			if (!$allData) {
				return array_column(Cache::get('getAllLanguages', $cacheKey), 'name', 'prefix');
			}
			return Cache::get('getAllLanguages', $cacheKey);
		}
		$all = [];
		$actives = [];
		$dataReader = (new Db\Query())->from('vtiger_language')->createCommand()->query();
		while ($row = $dataReader->read()) {
			$all[$row['prefix']] = $row;
			if ((int) $row['active'] === 1) {
				$actives[$row['prefix']] = $row;
			}
			Cache::save('getLangInfo', $row['prefix'], $row);
		}
		$dataReader->close();
		Cache::save('getAllLanguages', 'All', $all);
		Cache::save('getAllLanguages', 'Active', $actives);
		if (!$allData) {
			return array_column(Cache::get('getAllLanguages', $cacheKey), 'name', 'prefix');
		}
		return Cache::get('getAllLanguages', $cacheKey);
	}

	/**
	 * Function return languange data.
	 *
	 * @param string $prefix
	 *
	 * @return array
	 */
	public static function getLangInfo(string $prefix)
	{
		if (Cache::has('getLangInfo', $prefix)) {
			return Cache::get('getLangInfo', $prefix);
		}
		return Cache::save('getLangInfo', $prefix, (new Db\Query())->from('vtiger_language')->where(['prefix' => $prefix])->one());
	}

	/**
	 * Translation modification.
	 *
	 * @param string $language
	 * @param string $fileName
	 * @param string $type
	 * @param string $label
	 * @param string $translation
	 * @param bool   $remove
	 *
	 * @throws Exceptions\AppException
	 */
	public static function translationModify(string $language, string $fileName, string $type, string $label, string $translation, bool $remove = false)
	{
		$fileLocation = explode('__', $fileName, 2);
		array_unshift($fileLocation, 'custom', 'languages', $language);
		$fileDirectory = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $fileLocation) . '.' . static::FORMAT;
		if (file_exists($fileDirectory)) {
			$translations = Json::decode(file_get_contents($fileDirectory), true);
		} else {
			$loc = '';
			array_pop($fileLocation);
			foreach ($fileLocation as $name) {
				$loc .= DIRECTORY_SEPARATOR . $name;
				if (!file_exists(ROOT_DIRECTORY . $loc) && !mkdir(ROOT_DIRECTORY . $loc, 0755)) {
					throw new Exceptions\AppException('ERR_NO_PERMISSIONS_TO_CREATE_DIRECTORIES');
				}
			}
		}
		$translations[$type][$label] = $translation;
		if ($remove) {
			unset($translations[$type][$label]);
		}
		if (file_put_contents($fileDirectory, Json::encode($translations, JSON_PRETTY_PRINT)) === false) {
			throw new Exceptions\AppException('ERR_CREATE_FILE_FAILURE');
		}
		Cache::delete('LanguageFiles', $language . str_replace('__', DIRECTORY_SEPARATOR, $fileName));
	}

	/**
	 * Set locale information.
	 */
	public static function initLocale()
	{
		$original = explode(';', setlocale(LC_ALL, 0));
		$defaultCharset = strtolower(\AppConfig::main('default_charset'));
		setlocale(LC_ALL, \Locale::acceptFromHttp(self::getLanguage()) . '.' . $defaultCharset,
			\Locale::acceptFromHttp(\AppConfig::main('default_language')) . '.' . $defaultCharset, \Locale::acceptFromHttp(self::DEFAULT_LANG) . ".$defaultCharset",
			\Locale::acceptFromHttp(self::DEFAULT_LANG) . '.utf8');
		foreach ($original as $localeSetting) {
			if (strpos($localeSetting, '=') !== false) {
				list($category, $locale) = explode('=', $localeSetting);
			} else {
				$category = 'LC_ALL';
				$locale = $localeSetting;
			}
			if ($category !== 'LC_COLLATE' && $category !== 'LC_CTYPE' && defined($category)) {
				setlocale(constant($category), $locale);
			}
		}
	}

	/**
	 * Get display language name.
	 *
	 * @param string $prefix
	 *
	 * @return string
	 */
	public static function getDisplayName(string $prefix)
	{
		return \ucfirst(locale_get_region($prefix) === strtoupper(locale_get_primary_language($prefix)) ? locale_get_display_language($prefix, $prefix) : locale_get_display_name($prefix, $prefix));
	}

	/**
	 * Get region from language prefix.
	 *
	 * @param string $prefix
	 *
	 * @return mixed
	 */
	public static function getRegion(string $prefix)
	{
		return locale_parse($prefix)['region'];
	}
}
