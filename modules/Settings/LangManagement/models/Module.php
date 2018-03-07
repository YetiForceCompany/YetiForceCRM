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
	 * Save.
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public static function saveTranslation($params)
	{
		if ($params['is_new']) {
			$result = self::addTranslation($params);
		} else {
			$result = self::updateTranslation($params);
		}

		return $result;
	}

	/**
	 * Add translation.
	 *
	 * @param array $params
	 *
	 * @return (string|bool)[]
	 */
	public static function addTranslation($params)
	{
		$lang = $params['lang'];
		$mod = $params['mod'];
		$langkey = addslashes($params['langkey']);
		$val = addslashes($params['val']);
		$mod = str_replace(self::URL_SEPARATOR, '.', $mod);

		if (\AppConfig::performance('LOAD_CUSTOM_FILES')) {
			$qualifiedName = "custom.languages.$lang.$mod";
		} else {
			$qualifiedName = "languages.$lang.$mod";
		}
		$fileName = Vtiger_Loader::resolveNameToPath($qualifiedName);
		$fileExists = file_exists($fileName);
		if ($fileExists) {
			require $fileName;
			if ($params['type'] === 'php') {
				$langTab = $languageStrings ?? null;
			} else {
				$langTab = $jsLanguageStrings ?? null;
			}
			if (is_array($langTab) && array_key_exists($langkey, $langTab)) {
				return ['success' => false, 'data' => 'LBL_KeyExists'];
			}
			$fileContent = file_get_contents($fileName);
			if ($params['type'] == 'php') {
				$toReplace = '$languageStrings = [';
			} else {
				$toReplace = '$jsLanguageStrings = [';
			}
			$newTranslation = "'$langkey' => '$val',";
			if (self::parseData($toReplace, $fileContent)) {
				$fileContent = str_ireplace($toReplace, $toReplace . PHP_EOL . '	' . $newTranslation, $fileContent);
			} else {
				if (self::parseData('?>', $fileContent)) {
					$fileContent = str_replace('?>', '', $fileContent);
				}
				$fileContent = $fileContent . PHP_EOL . $toReplace . PHP_EOL . '	' . $newTranslation . PHP_EOL . '];';
			}
			file_put_contents($fileName, $fileContent);
		} else {
			if (\AppConfig::performance('LOAD_CUSTOM_FILES')) {
				static::createCustomLangDirectory($params);
			}
			if (!file_put_contents($fileName, '<?php' . PHP_EOL) === false) {
				throw new \App\Exceptions\AppException('ERR_CREATE_FILE_FAILURE');
			}
		}
		if (!$fileExists) {
			return static::addTranslation($params);
		}

		return ['success' => true, 'data' => 'LBL_AddTranslationOK'];
	}

	/**
	 * Function to update translation.
	 *
	 * @param array $params
	 *
	 * @return (string|bool)[]
	 */
	public static function updateTranslation($params)
	{
		$lang = $params['lang'];
		$mod = $params['mod'];
		$langkey = $params['langkey'];
		$val = addslashes($params['val']);
		$mod = str_replace(self::URL_SEPARATOR, '.', $mod);
		$languageStrings = $jsLanguageStrings = [];
		$customType = \AppConfig::performance('LOAD_CUSTOM_FILES');
		if ($customType) {
			$qualifiedName = "custom.languages.$lang.$mod";
		} else {
			$qualifiedName = "languages.$lang.$mod";
		}
		$fileName = Vtiger_Loader::resolveNameToPath($qualifiedName);
		if (strstr($fileName, 'languages') === false) {
			throw new \App\Exceptions\Security('ERR_MODULE_DOES_NOT_EXIST');
		}
		$fileExists = file_exists($fileName);
		if ($fileExists) {
			require $fileName;
			if ($params['type'] === 'php') {
				$langTab = $languageStrings ?? null;
			} else {
				$langTab = $jsLanguageStrings ?? null;
			}
			if (!is_array($langTab) || !array_key_exists($langkey, $langTab)) {
				if ($customType) {
					return self::addTranslation($params);
				}

				return ['success' => false, 'data' => 'LBL_DO_NOT_POSSIBLE_TO_MAKE_CHANGES'];
			}
			$countLangEl = count(explode("\n", $langTab[$langkey]));
			$i = 1;
			$start = false;
			$fileContentEdit = file($fileName);
			foreach ($fileContentEdit as $k => $row) {
				if ($start && $i < $countLangEl) {
					unset($fileContentEdit[$k]);
					++$i;
				}
				if (strstr($row, "'$langkey'") !== false || strstr($row, '"' . $langkey . '"') !== false) {
					$fileContentEdit[$k] = "	'$langkey' => '$val'," . PHP_EOL;
					$start = true;
				}
			}
			file_put_contents($fileName, implode('', $fileContentEdit));
		} else {
			if ($customType) {
				static::createCustomLangDirectory($params);
			}
			if (file_put_contents($fileName, '<?php' . PHP_EOL) === false) {
				throw new \App\Exceptions\AppException('ERR_CREATE_FILE_FAILURE');
			}
		}
		if (!$fileExists) {
			return static::updateTranslation($params);
		}

		return ['success' => true, 'data' => 'LBL_UpdateTranslationOK'];
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
	 * @param string   $mod
	 * @param type     $ShowDifferences
	 *
	 * @return type
	 */
	public function loadLangTranslation($langs, $mod, $ShowDifferences = 0)
	{
		$keysPhp = $keysJs = $langTab = $respPhp = $respJs = [];
		$mod = str_replace(self::URL_SEPARATOR, '/', $mod);
		if (!is_array($langs)) {
			$langs = [$langs];
		}
		foreach ($langs as $lang) {
			$langData = Vtiger_Language_Handler::getModuleStringsFromFile($lang, $mod);
			if ($langData) {
				$langTab[$lang]['php'] = $langData['languageStrings'];
				$langTab[$lang]['js'] = $langData['jsLanguageStrings'];
				$keysPhp = array_merge($keysPhp, array_keys($langData['languageStrings']));
				$keysJs = array_merge($keysJs, array_keys($langData['jsLanguageStrings']));
			}
		}
		$keysPhp = array_unique($keysPhp);
		$keysJs = array_unique($keysJs);
		foreach ($keysPhp as $key) {
			foreach ($langs as $language) {
				$respPhp[$key][$language] = htmlentities($langTab[$language]['php'][$key], ENT_QUOTES, 'UTF-8');
			}
		}
		foreach ($keysJs as $key) {
			foreach ($langs as $language) {
				$respJs[$key][$language] = htmlentities($langTab[$language]['js'][$key], ENT_QUOTES, 'UTF-8');
			}
		}

		return ['php' => $respPhp, 'js' => $respJs, 'langs' => $langs, 'keys' => $keys];
	}

	public function loadAllFieldsFromModule($langs, $mod, $showDifferences = 0)
	{
		$variablesFromFile = $this->loadLangTranslation($langs, 'HelpInfo', $showDifferences);
		$output = ['langs' => $langs];
		$dataReader = (new \App\Db\Query())
			->from('vtiger_field')
			->where(['tabid' => \App\Module::getModuleId($mod), 'presence' => [0, 2]])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$output['php'][$mod . '|' . $row['fieldlabel']]['label'] = \App\Language::translate($row['fieldlabel'], $mod);
			$output['php'][$mod . '|' . $row['fieldlabel']]['info'] = ['view' => explode(',', $row['helpinfo']), 'fieldid' => $row['fieldid']];
			foreach ($langs as $lang) {
				$output['php'][$mod . '|' . $row['fieldlabel']][$lang] = stripslashes($variablesFromFile['php'][$mod . '|' . $row['fieldlabel']][$lang]);
			}
		}
		$dataReader->close();

		return $output;
	}

	public function getModFromLang($lang)
	{
		if (empty($lang)) {
			$lang = 'en_us';
		} else {
			$lang = is_array($lang) ? reset($lang) : $lang;
		}
		$dir = "languages/$lang";
		if (!is_dir($dir)) {
			return false;
		}
		$files = [];
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
		foreach ($objects as $name => $object) {
			if (strpos($object->getFilename(), '.php') !== false) {
				$name = str_replace('.php', '', $name);
				$val = str_replace($dir . DIRECTORY_SEPARATOR, '', $name);
				$key = str_replace($dir . DIRECTORY_SEPARATOR, '', $name);
				$key = str_replace('/', self::URL_SEPARATOR, $key);
				$key = str_replace('\\', self::URL_SEPARATOR, $key);
				$val = str_replace(DIRECTORY_SEPARATOR, '|', $val);
				$files[$key] = $val;
			}
		}

		return self::settingsTranslate($files);
	}

	public function settingsTranslate($langs)
	{
		$settings = [];
		foreach ($langs as $key => $lang) {
			if (self::parseData('|', $lang)) {
				$langArray = explode('|', $lang);
				unset($langs[$key]);
				$settings[$key] = \App\Language::translate($langArray[1], 'Settings:' . $langArray[1]);
			} else {
				$langs[$key] = \App\Language::translate($key, $key);
			}
		}

		return ['mods' => $langs, 'settings' => $settings];
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

	public static function saveView($params)
	{
		$value = implode(',', $params['value']);
		\App\Db::getInstance()->createCommand()
			->update('vtiger_field', ['helpinfo' => $value], ['fieldid' => $params['fieldid']])
			->execute();

		return ['success' => true, 'data' => 'LBL_SUCCESSFULLY_UPDATED'];
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
