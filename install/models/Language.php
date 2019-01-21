<?php

/**
 * Language basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Install;

/**
 * Class Language.
 */
class Language extends \App\Language
{
	/**
	 * Language directory.
	 *
	 * @var string
	 */
	public static $languageDirectoryForInstall = 'install/languages';

	/**
	 * Contains module language translations.
	 *
	 * @var array
	 */
	protected static $languageContainerForInstall = [];

	/**
	 * Load language file.
	 *
	 * @param string $language
	 * @param string $moduleName
	 */
	public static function loadLanguageFileForInstall($language, $moduleName = '_Base')
	{
		if (!isset(static::$languageContainerForInstall[$language][$moduleName])) {
			if (\App\Cache::has('LanguageFilesForInstall', $language . $moduleName)) {
				static::$languageContainerForInstall[$language][$moduleName] = \App\Cache::get('LanguageFilesForInstall', $language . $moduleName);
			} else {
				static::$languageContainerForInstall[$language][$moduleName] = [];
				$file = DIRECTORY_SEPARATOR . static::$languageDirectoryForInstall . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $moduleName . '.' . static::FORMAT;
				$langFile = ROOT_DIRECTORY . $file;
				if (file_exists($langFile)) {
					static::$languageContainerForInstall[$language][$moduleName] = \App\Json::decode(file_get_contents($langFile), true) ?? [];
				}
				$langCustomFile = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'custom' . $file;
				if (file_exists($langCustomFile)) {
					$translation = \App\Json::decode(file_get_contents($langCustomFile), true) ?? [];
					foreach ($translation as $type => $rows) {
						foreach ($rows as $key => $val) {
							static::$languageContainerForInstall[$language][$moduleName][$type][$key] = $val;
						}
					}
				}
				if (!file_exists($langFile) && !file_exists($langCustomFile)) {
					\App\Log::info("Language file does not exist, module: $moduleName ,language: $language");
				}
				\App\Cache::save(
					'LanguageFilesForInstall', $language . $moduleName,
					static::$languageContainerForInstall[$language][$moduleName], \App\Cache::LONG
				);
			}
		}
	}

	/**
	 * Functions that gets translated string.
	 *
	 * @param string      $key        - string which need to be translated
	 * @param string      $moduleName - module scope in which the translation need to be check
	 * @param bool|string $language   - language of translation
	 * @param bool|string $encode
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
		$moduleName = str_replace([':', '.'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $moduleName);
		static::loadLanguageFileForInstall($language, $moduleName);
		if (isset(static::$languageContainerForInstall[$language][$moduleName]['php'][$key])) {
			if ($encode) {
				return \nl2br(\App\Purifier::encodeHtml(static::$languageContainerForInstall[$language][$moduleName]['php'][$key]));
			}
			return \nl2br(static::$languageContainerForInstall[$language][$moduleName]['php'][$key]);
		}
		// Lookup for the translation in base module, in case of sub modules, before ending up with common strings
		if (strpos($moduleName, 'Settings') === 0) {
			$base = 'Settings' . DIRECTORY_SEPARATOR . '_Base';
			static::loadLanguageFileForInstall($language, $base);
			if (isset(static::$languageContainerForInstall[$language][$base]['php'][$key])) {
				if ($encode) {
					return \nl2br(Purifier::encodeHtml(static::$languageContainerForInstall[$language][$base]['php'][$key]));
				}
				return \nl2br(static::$languageContainerForInstall[$language][$base]['php'][$key]);
			}
		}
		static::loadLanguageFileForInstall($language);
		if (isset(static::$languageContainerForInstall[$language]['_Base']['php'][$key])) {
			if ($encode) {
				return \nl2br(Purifier::encodeHtml(static::$languageContainerForInstall[$language]['_Base']['php'][$key]));
			}
			return \nl2br(static::$languageContainerForInstall[$language]['_Base']['php'][$key]);
		}
		return parent::translate($key, $moduleName, $language, $encode);
	}
}
