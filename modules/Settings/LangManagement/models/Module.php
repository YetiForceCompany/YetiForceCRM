<?php

/**
 * LangManagement Module Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author YetiForce.com
 */
class Settings_LangManagement_Module_Model extends Settings_Vtiger_Module_Model
{
	const URL_SEPARATOR = '__';

	/**
	 * Remove translation.
	 *
	 * @param array $params
	 *
	 * @return (string|bool)[]
	 */
	public static function deleteTranslation($params)
	{
		$allLangs = App\Language::getAll();
		$change = false;
		$langkey = $params['langkey'];
		foreach ($params['lang'] as $lang) {
			if (!isset($allLangs[$lang])) {
				throw new \App\Exceptions\Security('LBL_LANGUAGE_DOES_NOT_EXIST');
			}
			$edit = false;
			$mod = str_replace(self::URL_SEPARATOR, '.', $params['mod']);
			if (\AppConfig::performance('LOAD_CUSTOM_FILES')) {
				$qualifiedName = "custom.languages.$lang.$mod";
			} else {
				$qualifiedName = "languages.$lang.$mod";
			}
			$fileName = Vtiger_Loader::resolveNameToPath($qualifiedName);
			if (file_exists($fileName)) {
				$fileContent = file($fileName);
				foreach ($fileContent as $key => $file_row) {
					if (self::parseData("'$langkey'", $file_row)) {
						unset($fileContent[$key]);
						$edit = $change = true;
					}
				}
				if ($edit) {
					file_put_contents($fileName, implode('', $fileContent));
				}
			}
		}

		return $change ? ['success' => true, 'data' => 'LBL_DeleteTranslationOK'] : ['success' => false, 'data' => 'LBL_DELETE_TRANSLATION_FAILED'];
	}

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
		if (count($mod) > 1) {
			$folders[] = 'Settings';
		}
		$loc = '';
		foreach ($folders as $key => $name) {
			$loc .= DIRECTORY_SEPARATOR . $name;
			if (!file_exists(ROOT_DIRECTORY . $loc)) {
				if (!mkdir(ROOT_DIRECTORY . $loc)) {
					\App\Log::warning("No permissions to create directories: $loc");
					throw new \App\Exceptions\AppException('No permissions to create directories');
				}
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
		if (!is_array($langs)) {
			$langs = [$langs];
		}
		foreach ($langs as $lang) {
			$langData = \App\Language::getFromFile($moduleName, $lang);
			if ($langData) {
				$langTab[$lang] = $langData;
				$keysPhp += isset($langData['php']) ? array_keys($langData['php']) : [];
				$keysJs += isset($langData['js']) ? array_keys($langData['js']) : [];
			}
		}
		foreach ($keysPhp as $key) {
			foreach ($langs as $language) {
				$respPhp[$key][$language] = isset($langTab[$language]['php'][$key]) ? \App\Purifier::encodeHtml($langTab[$language]['php'][$key]) : null;
			}
		}
		foreach ($keysJs as $key) {
			foreach ($langs as $language) {
				$respJs[$key][$language] = isset($langTab[$language]['js'][$key]) ? \App\Purifier::encodeHtml($langTab[$language]['js'][$key]) : null;
			}
		}

		return ['php' => $respPhp, 'js' => $respJs, 'langs' => $langs];
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
		$prefix = \App\Purifier::purifyByType($params['prefix'], 1);
		$destiny = 'languages/' . $prefix . '/';
		mkdir($destiny);
		vtlib\Functions::recurseCopy('languages/en_us/', $destiny);
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('vtiger_language', [
			'id' => $db->getUniqueId('vtiger_language'),
			'name' => $params['name'],
			'prefix' => $params['prefix'],
			'label' => $params['label'],
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
		if ($b != '' && stristr($b, $a) !== false) {
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
		while (($file = readdir($fd)) !== false) {
			if ($file === '.' || $file === '..') {
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
		\App\Log::trace('Entering Settings_LangManagement_Module_Model::setAsDefault(' . $lang . ') method ...');
		$db = \App\Db::getInstance();
		$fileName = 'config/config.inc.php';
		$completeData = file_get_contents($fileName);
		$updatedFields = 'default_language';
		$patternString = '$%s = %s;';
		$pattern = '/\$' . $updatedFields . '[\s]+=([^\n]+);/';
		$replacement = sprintf($patternString, $updatedFields, App\Utils::varExport(ltrim($prefix, '0')));
		$fileContent = preg_replace($pattern, $replacement, $completeData);
		$filePointer = fopen($fileName, 'w');
		fwrite($filePointer, $fileContent);
		fclose($filePointer);
		$dataReader = (new \App\Db\Query())->select(['prefix'])
			->from('vtiger_language')
			->where(['isdefault' => 1])
			->createCommand()->query();
		if ($dataReader->count() == 1) {
			$prefixOld = $dataReader->readColumn(0);
			$db->createCommand()->update('vtiger_language', ['isdefault' => 0], ['isdefault' => 1])->execute();
		}
		$status = $db->createCommand()->update('vtiger_language', ['isdefault' => 1], ['prefix' => $prefix])->execute();
		if ($status) {
			$status = true;
		} else {
			$status = false;
		}
		\App\Cache::clear();
		\App\Log::trace('Exiting Settings_LangManagement_Module_Model::setAsDefault() method ...');

		return ['success' => $status, 'prefixOld' => $prefixOld];
	}

	public function getStatsData($langBase, $langs, $byModule = false)
	{
		$filesName = $this->getModFromLang($langBase);
		settype($langs, 'array');
		if (!in_array($langBase, $langs)) {
			$langs[] = $langBase;
		}
		$data = [];
		foreach ($filesName as $gropu) {
			foreach ($gropu as $mode => $name) {
				if ($byModule === false || $byModule === $mode) {
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
			if (!in_array($id, ['php', 'js'])) {
				continue;
			}
			foreach ($dataLang as $key => $langs) {
				foreach ($langs as $lang => $value) {
					if ($lang == $langBase) {
						++$i;
						continue;
					}
					if (!empty($langs[$langBase]) && ($value == $langs[$langBase] || empty($value))) {
						if ($byModule !== false) {
							$differences[$id][$key][$langBase] = $langs[$langBase];
							$differences[$id][$key][$lang] = $value;
						} else {
							$differences[$lang][] = $key;
						}
					}
				}
			}
		}
		if ($byModule === false) {
			array_unshift($differences, $i);
		}

		return $differences;
	}
}
