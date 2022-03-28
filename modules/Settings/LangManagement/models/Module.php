<?php

/**
 * LangManagement Module Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    YetiForce S.A.
 */
class Settings_LangManagement_Module_Model extends Settings_Vtiger_Module_Model
{
	const URL_SEPARATOR = '__';

	/**
	 * Function creates directory structure.
	 *
	 * @param array $params
	 *
	 * @throws \App\Exceptions\AppException
	 */
	public static function createCustomLangDirectory($params)
	{
		$mod = explode(self::URL_SEPARATOR, $params['mod']);
		$folders = ['custom', 'languages', $params['lang']];
		if (\count($mod) > 1) {
			$folders[] = 'Settings';
		}
		$loc = '';
		foreach ($folders as $name) {
			$loc .= DIRECTORY_SEPARATOR . $name;
			if (!file_exists(ROOT_DIRECTORY . $loc) && !mkdir(ROOT_DIRECTORY . $loc)) {
				\App\Log::warning("No permissions to create directories: $loc");
				throw new \App\Exceptions\AppException('No permissions to create directories');
			}
		}
	}

	/**
	 * Function gets translations.
	 *
	 * @param string[] $langs
	 * @param string   $moduleName
	 *
	 * @return array
	 */
	public function loadLangTranslation($langs, $moduleName)
	{
		$keysPhp = $keysJs = $langTab = $respPhp = $respJs = [];
		$moduleName = str_replace(self::URL_SEPARATOR, DIRECTORY_SEPARATOR, $moduleName);
		if (!\is_array($langs)) {
			$langs = [$langs];
		}
		foreach ($langs as $lang) {
			$langData = \App\Language::getFromFile($moduleName, $lang);
			if ($langData) {
				$langTab[$lang] = $langData;
				$keysPhp += $langData['php'] ?? [];
				$keysJs += $langData['js'] ?? [];
			}
		}
		foreach (array_keys($keysPhp) as $key) {
			foreach ($langs as $language) {
				$respPhp[$key][$language] = isset($langTab[$language]['php'][$key]) ? \App\Purifier::encodeHtml($langTab[$language]['php'][$key]) : null;
			}
		}
		foreach (array_keys($keysJs) as $key) {
			foreach ($langs as $language) {
				$respJs[$key][$language] = isset($langTab[$language]['js'][$key]) ? \App\Purifier::encodeHtml($langTab[$language]['js'][$key]) : null;
			}
		}
		return ['php' => $respPhp, 'js' => $respJs, 'langs' => $langs];
	}

	/**
	 * Load custom languages data.
	 *
	 * @param array  $languages
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public function loadCustomLanguageFile(array $languages, string $moduleName)
	{
		$result = [];
		$moduleName = str_replace(self::URL_SEPARATOR, DIRECTORY_SEPARATOR, $moduleName);
		foreach ($languages as $language) {
			$custom = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $moduleName . '.' . \App\Language::FORMAT;
			if (file_exists($custom)) {
				$response = \App\Json::decode(file_get_contents($custom), true);
				if ($response) {
					$result = array_replace_recursive($result, $response);
				}
			}
		}
		return $result;
	}

	/**
	 * Get modules from language.
	 *
	 * @param string $lang
	 *
	 * @return array
	 */
	public function getModFromLang($lang)
	{
		$modules = [];
		$settings = [];
		$format = \App\Language::FORMAT;
		$lang = empty($lang) ? \App\Language::getLanguage() : $lang;
		$dirs = [
			'languages' . DIRECTORY_SEPARATOR . $lang,
			'custom' . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . $lang
		];
		foreach ($dirs as $dir) {
			if (!is_dir($dir)) {
				continue;
			}
			foreach ($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST) as $object) {
				if ($object->getExtension() === $format) {
					$name = $object->getBasename(".$format");
					if ($iterator->getSubPath()) {
						$settings["{$iterator->getSubPath()}__{$name}"] = \App\Language::translate($name, "{$iterator->getSubPath()}.$name");
					} else {
						$modules[$name] = \App\Language::translate($name, $name);
					}
				}
			}
		}
		return ['mods' => $modules, 'settings' => $settings];
	}

	/**
	 * Function added new language.
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public static function add($params)
	{
		if (isset(App\Language::getAll(false)[$params['prefix']])) {
			return ['success' => false, 'data' => 'LBL_LangExist'];
		}
		if (!\App\Validator::languageTag($params['prefix'])) {
			return ['success' => false, 'data' => 'LBL_NOT_CORRECT_LANGUAGE_TAG'];
		}
		$destiny = 'languages/' . $params['prefix'] . '/';
		if (!is_dir($destiny)) {
			mkdir($destiny);
		}
		vtlib\Functions::recurseCopy('languages/' . \App\Language::DEFAULT_LANG, $destiny);
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('vtiger_language', [
			'name' => $params['name'],
			'prefix' => $params['prefix'],
			'name' => $params['label'],
			'lastupdated' => date('Y-m-d H:i:s')
		])->execute();
		\App\Cache::clear();
		return ['success' => true, 'data' => 'LBL_AddDataOK'];
	}

	public static function delete($prefix)
	{
		$dir = 'languages/' . $prefix;
		if (file_exists($dir)) {
			self::deleteDir($dir);
		}
		\App\Db::getInstance()->createCommand()
			->delete('vtiger_language', ['prefix' => $prefix])
			->execute();
		\App\Cache::clear();

		return true;
	}

	/**
	 * Parse data.
	 *
	 * @param string $a
	 * @param string $b
	 *
	 * @return bool
	 */
	public static function parseData($a, $b)
	{
		$resp = false;
		if ('' != $b && false !== stristr($b, $a)) {
			$resp = true;
		}
		return $resp;
	}

	/**
	 * Dedlete dir.
	 *
	 * @param string $dir
	 *
	 * @return bool
	 */
	public static function deleteDir($dir)
	{
		$fd = opendir($dir);
		if (!$fd) {
			return false;
		}
		while (false !== ($file = readdir($fd))) {
			if ('.' === $file || '..' === $file) {
				continue;
			}
			if (is_dir($dir . '/' . $file)) {
				self::deleteDir($dir . '/' . $file);
			} else {
				unlink("$dir/$file");
			}
		}
		closedir($fd);
		rmdir($dir);
	}

	/**
	 * Function to set language as default.
	 *
	 * @param array $prefix
	 *
	 * @return array
	 */
	public static function setAsDefault($prefix)
	{
		\App\Log::trace('Entering Settings_LangManagement_Module_Model::setAsDefault(' . $prefix . ') method ...');
		$db = \App\Db::getInstance();
		$status = false;
		if (\App\Language::getLangInfo($prefix)) {
			$configFile = new \App\ConfigFile('main');
			$configFile->set('default_language', $prefix);
			$configFile->create();
			$dataReader = (new \App\Db\Query())->select(['prefix'])
				->from('vtiger_language')
				->where(['isdefault' => 1])
				->createCommand()->query();
			if (1 === $dataReader->count()) {
				$prefixOld = $dataReader->readColumn(0);
				$db->createCommand()->update('vtiger_language', ['isdefault' => 0], ['isdefault' => 1])->execute();
			}
			$status = (bool) $db->createCommand()->update('vtiger_language', ['isdefault' => 1], ['prefix' => $prefix])->execute();
		}
		\App\Cache::clear();
		\App\Log::trace('Exiting Settings_LangManagement_Module_Model::setAsDefault() method ...');
		return ['success' => $status, 'prefixOld' => $prefixOld ?? ''];
	}

	public function getStatsData($langBase, $langs, $byModule = false)
	{
		$filesName = $this->getModFromLang($langBase);
		$langs = (array) $langs;
		if (!\in_array($langBase, $langs)) {
			$langs[] = $langBase;
		}
		$data = [];
		foreach ($filesName as $gropu) {
			foreach ($gropu as $mode => $name) {
				if (false === $byModule || $byModule === $mode) {
					$data[$mode] = $this->getStats($this->loadLangTranslation($langs, $mode), $langBase, $byModule);
				}
			}
		}
		return $data;
	}

	public function getStats($data, $langBase, $byModule)
	{
		$differences = [];
		$i = 0;
		foreach ($data as $id => $dataLang) {
			if (!\in_array($id, ['php', 'js'])) {
				continue;
			}
			foreach ($dataLang as $key => $langs) {
				foreach ($langs as $lang => $value) {
					if ($lang == $langBase) {
						empty($langs[$langBase]) ?: ++$i;
						continue;
					}
					if (!empty($langs[$langBase]) && ($value == $langs[$langBase] || empty($value))) {
						if (false !== $byModule) {
							$differences[$id][$key][$langBase] = $langs[$langBase];
							$differences[$id][$key][$lang] = $value;
						} else {
							$differences[$lang][] = $key;
						}
					}
				}
			}
		}
		if (false === $byModule) {
			array_unshift($differences, $i);
		}
		return $differences;
	}
}
