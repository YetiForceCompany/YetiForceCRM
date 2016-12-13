<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_LangManagement_Module_Model extends Settings_Vtiger_Module_Model
{

	const url_separator = '^';

	public function getLang($data = false)
	{
		$query = (new \App\Db\Query())->from('vtiger_language');
		if ($data && $data['prefix'] != '') {
			$query->where(['prefix' => $data['prefix']]);
		}
		$output = false;
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$output[$row['prefix']] = $row;
		}
		return $output;
	}

	public function DeleteTranslation($params)
	{
		foreach ($params['lang'] as $lang) {
			$mod = str_replace(self::url_separator, "/", $params['mod']);
			$fileName = "languages/$lang/$mod.php";
			$langkey = $params['langkey'];
			if (file_exists($fileName)) {
				$fileContent = file($fileName);
				foreach ($fileContent as $key => $file_row) {
					if (self::parse_data("'$langkey'", $file_row)) {
						unset($fileContent[$key]);
					}
				}
				$fileContent = implode("", $fileContent);
				$filePointer = fopen($fileName, 'w+');
				fwrite($filePointer, $fileContent);
				fclose($filePointer);
			}
		}
		return array('success' => true, 'data' => 'LBL_DeleteTranslationOK');
	}

	public function SaveTranslation($params)
	{
		if ($params['is_new'] == 'true') { //Add translation 
			$result = self::AddTranslation($params);
		} else { //Edit translation
			$result = self::UpdateTranslation($params);
		}
		return $result;
	}

	public function AddTranslation($params)
	{
		$lang = $params['lang'];
		$mod = $params['mod'];
		$langkey = addslashes($params['langkey']);
		$val = addslashes($params['val']);
		$mod = str_replace(self::url_separator, "/", $mod);
		$fileName = "languages/$lang/$mod.php";
		$fileExists = file_exists($fileName);
		if ($fileExists) {
			require_once($fileName);
			if ($params['type'] == 'php') {
				vglobal('languageStrings');
				$langTab = $languageStrings;
			} else {
				vglobal('jsLanguageStrings');
				$langTab = $jsLanguageStrings;
			}
			if (is_array($langTab) && array_key_exists($langkey, $langTab)) {
				return array('success' => false, 'data' => 'LBL_KeyExists');
			}
			$fileContent = file_get_contents($fileName);
			if ($params['type'] == 'php') {
				$to_replase = '$languageStrings = [';
			} else {
				$to_replase = '$jsLanguageStrings = [';
			}
			$new_translation = "'$langkey' => '$val',";
			if (self::parse_data($to_replase, $fileContent)) {
				$fileContent = str_ireplace($to_replase, $to_replase . PHP_EOL . '	' . $new_translation, $fileContent);
			} else {
				if (self::parse_data('?>', $fileContent)) {
					$fileContent = str_replace('?>', '', $fileContent);
				}
				$fileContent = $fileContent . PHP_EOL . $to_replase . PHP_EOL . '	' . $new_translation . PHP_EOL . '];';
			}
		} else {
			$fileContent = '<?php' . PHP_EOL;
		}
		$filePointer = fopen($fileName, 'w');
		fwrite($filePointer, $fileContent);
		fclose($filePointer);
		if (!$fileExists) {
			self::AddTranslation($params);
		}
		return array('success' => true, 'data' => 'LBL_AddTranslationOK');
	}

	public function UpdateTranslation($params)
	{
		$lang = $params['lang'];
		$mod = $params['mod'];
		$langkey = $params['langkey'];
		$val = addslashes($params['val']);
		$mod = str_replace(self::url_separator, "/", $mod);
		$languageStrings = array();
		$jsLanguageStrings = array();
		$fileName = "languages/$lang/$mod.php";
		$fileExists = file_exists($fileName);
		if ($fileExists) {
			require($fileName);
			vglobal('languageStrings');
			vglobal('jsLanguageStrings');
			if ($params['type'] == 'php') {
				vglobal('languageStrings');
				$langTab = $languageStrings;
			} else {
				vglobal('jsLanguageStrings');
				$langTab = $jsLanguageStrings;
			}
			if (!is_array($langTab) || !array_key_exists($langkey, $langTab)) {
				return array('success' => false, 'data' => 'LBL_DO_NOT_POSSIBLE_TO_MAKE_CHANGES');
			}
			$countLangEl = count(explode("\n", $langTab[$langkey]));
			$i = 1;
			$start = false;
			$fileContentEdit = file($fileName);
			foreach ($fileContentEdit as $k => $row) {
				if ($start && $i < $countLangEl) {
					unset($fileContentEdit[$k]);
					$i++;
				}
				if (strstr($row, "'$langkey'") !== false || strstr($row, '"' . $langkey . '"') !== false) {
					$fileContentEdit[$k] = "	'$langkey' => '$val'," . PHP_EOL;
					$start = true;
				}
			}
			$fileContent = implode("", $fileContentEdit);
		} else {
			$fileContent = '<?php' . PHP_EOL;
		}
		/*
		  $fileContent = file_get_contents($fileName);
		  if($params['type'] == 'php'){
		  $pattern = '/(\''.$langkey.'\')[\s]+=>([^,]+),/';
		  $patternString = "'%s' => '%s',";
		  }else{
		  $pattern = '/(\''.$langkey.'\')[\s]+=>([^,]+),/';
		  $patternString = "'%s' => '%s',";
		  }
		  $replacement = sprintf($patternString, $langkey, $val);
		  $fileContent = preg_replace($pattern, $replacement, $fileContent);
		 */
		$filePointer = fopen($fileName, 'w+');
		fwrite($filePointer, $fileContent);
		fclose($filePointer);
		if (!$fileExists) {
			self::UpdateTranslation($params);
		}
		return array('success' => true, 'data' => 'LBL_UpdateTranslationOK');
	}

	public function loadLangTranslation($lang, $mod, $ShowDifferences = 0)
	{
		$keysPhp = array();
		$keysJs = array();
		$langs = array();
		$langTab = array();
		$respPhp = array();
		$respJs = array();
		$mod = str_replace(self::url_separator, '/', $mod);
		if (self::parse_data(',', $lang)) {
			$langs = explode(",", $lang);
		} else {
			$langs[] = $lang;
		}
		foreach ($langs as $lang) {
			$dir = "languages/$lang/$mod.php";
			if (file_exists($dir)) {
				$languageStrings = array();
				$jsLanguageStrings = array();
				require($dir);
				vglobal('languageStrings');
				vglobal('jsLanguageStrings');
				$langTab[$lang]['php'] = $languageStrings;
				$langTab[$lang]['js'] = $jsLanguageStrings;
				$keysPhp = array_merge($keysPhp, array_keys($languageStrings));
				$keysJs = array_merge($keysJs, array_keys($jsLanguageStrings));
			}
		}
		$keysPhp = array_unique($keysPhp);
		$keysJs = array_unique($keysJs);
		foreach ($keysPhp as $key) {
			foreach ($langs as $language) {
				$respPhp[$key][$language] = htmlentities($langTab[$language]['php'][$key], ENT_QUOTES, "UTF-8");
			}
		}
		foreach ($keysJs as $key) {
			foreach ($langs as $language) {
				$respJs[$key][$language] = htmlentities($langTab[$language]['js'][$key], ENT_QUOTES, "UTF-8");
			}
		}
		return array('php' => $respPhp, 'js' => $respJs, 'langs' => $langs, 'keys' => $keys);
	}

	public function loadAllFieldsFromModule($lang, $mod, $showDifferences = 0)
	{
		$variablesFromFile = $this->loadLangTranslation($lang, 'HelpInfo', $showDifferences);
		$output = [];
		if (self::parse_data(',', $lang)) {
			$langs = explode(",", $lang);
		} else {
			$langs[] = $lang;
		}
		$output['langs'] = $langs;
		$dataReader = (new \App\Db\Query())
				->from('vtiger_field')
				->where(['tabid' => \App\Module::getModuleId($mod), 'presence' => [0, 2]])
				->createCommand()->query();
		while ($row = $dataReader->read()) {
			$output['php'][$mod . '|' . $row['fieldlabel']]['label'] = vtranslate($row['fieldlabel'], $mod);
			$output['php'][$mod . '|' . $row['fieldlabel']]['info'] = array('view' => explode(',', $row['helpinfo']), 'fieldid' => $row['fieldid']);
			foreach ($langs AS $lang) {
				$output['php'][$mod . '|' . $row['fieldlabel']][$lang] = stripslashes($variablesFromFile['php'][$mod . '|' . $row['fieldlabel']][$lang]);
			}
		}
		return $output;
	}

	public function getModFromLang($lang)
	{
		if ($lang == '' || $lang === null) {
			$lang = 'en_us';
		} else {
			if (self::parse_data(',', $lang)) {
				$langA = explode(",", $lang);
				$lang = $langA[0];
			}
		}
		$dir = "languages/$lang";
		if (!file_exists($dir)) {
			return false;
		}
		$files = array();
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST);
		foreach ($objects as $name => $object) {
			if (strpos($object->getFilename(), '.php') !== false) {
				$name = str_replace('.php', "", $name);
				$val = str_replace($dir . DIRECTORY_SEPARATOR, "", $name);
				$key = str_replace($dir . DIRECTORY_SEPARATOR, "", $name);
				$key = str_replace("/", self::url_separator, $key);
				$key = str_replace("\\", self::url_separator, $key);
				$val = str_replace(DIRECTORY_SEPARATOR, "|", $val);
				$files[$key] = $val;
			}
		}
		return self::SettingsTranslate($files);
	}

	public function SettingsTranslate($langs)
	{
		$settings = array();
		foreach ($langs as $key => $lang) {
			if (self::parse_data('|', $lang)) {
				$langArray = explode("|", $lang);
				unset($langs[$key]);
				$settings[$key] = vtranslate($langArray[1], 'Settings:' . $langArray[1]);
			} else {
				$langs[$key] = vtranslate($key, $key);
			}
		}
		return array('mods' => $langs, 'settings' => $settings);
	}

	public function add($params)
	{
		if (self::getLang($params)) {
			return ['success' => false, 'data' => 'LBL_LangExist'];
		}
		self::CopyDir('languages/en_us/', 'languages/' . $params['prefix'] . '/');
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('vtiger_language', [
			'id' => $db->getUniqueId('vtiger_language'),
			'name' => $params['name'],
			'prefix' => $params['prefix'],
			'label' => $params['label'],
		])->execute();
		return ['success' => true, 'data' => 'LBL_AddDataOK'];
	}

	public function save($params)
	{
		if ($params['type'] == 'Checkbox') {
			$val = $params['val'] == 'true' ? 1 : 0;
			\App\Db::getInstance()->createCommand()
				->update('vtiger_language', [$params['name'] => $val], ['prefix' => $params['prefix']])
				->execute();
			return true;
		}
		return false;
	}

	public function saveView($params)
	{
		if (!is_array($params['value'])) {
			$params['value'] = [$params['value']];
		}
		$value = implode(',', $params['value']);
		\App\Db::getInstance()->createCommand()
			->update('vtiger_field', ['helpinfo' => $value], ['fieldid' => $params['fieldid']])
			->execute();
		return array('success' => true, 'data' => 'LBL_SUCCESSFULLY_UPDATED');
	}

	public static function delete($params)
	{
		$dir = 'languages/' . $params['prefix'];
		if (file_exists($dir)) {
			self::DeleteDir($dir);
		}
		\App\Db::getInstance()->createCommand()
			->delete('vtiger_language', ['prefix' => $params['prefix']])
			->execute();
		return true;
	}

	// Dodatkowe funkcje
	public function parse_data($a, $b)
	{
		$resp = false;
		if ($b != '' && stristr($b, $a) !== false) {
			$resp = true;
		}
		return $resp;
	}

	public function DeleteDir($dir)
	{
		$fd = opendir($dir);
		if (!$fd)
			return false;
		while (($file = readdir($fd)) !== false) {
			if ($file == "." || $file == "..")
				continue;
			if (is_dir($dir . "/" . $file)) {
				self::DeleteDir($dir . "/" . $file);
			} else {
				unlink("$dir/$file");
			}
		}
		closedir($fd);
		rmdir($dir);
	}

	public function CopyDir($src, $dst)
	{
		$dir = opendir($src);
		@mkdir($dst);
		while (false !== ( $file = readdir($dir))) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if (is_dir($src . '/' . $file)) {
					self::CopyDir($src . '/' . $file, $dst . '/' . $file);
				} else {
					copy($src . '/' . $file, $dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}

	public function setAsDefault($lang)
	{

		\App\Log::trace("Entering Settings_LangManagement_Module_Model::setAsDefault(" . $lang . ") method ...");
		$db = \App\Db::getInstance();
		$prefix = $lang['prefix'];
		$fileName = 'config/config.inc.php';
		$completeData = file_get_contents($fileName);
		$updatedFields = "default_language";
		$patternString = "\$%s = '%s';";
		$pattern = '/\$' . $updatedFields . '[\s]+=([^;]+);/';
		$replacement = sprintf($patternString, $updatedFields, ltrim($prefix, '0'));
		$fileContent = preg_replace($pattern, $replacement, $completeData);
		$filePointer = fopen($fileName, 'w');
		fwrite($filePointer, $fileContent);
		fclose($filePointer);
		$dataReader = (new \App\Db\Query)->select('prefix')
			->from('vtiger_language')
			->where(['isdefault' => 1])
			->createCommand()->query();
		if ($dataReader->count() == 1) {
			$prefixOld = $dataReader->readColumn(0);
			$db->createCommand()->update('vtiger_language', ['isdefault' => 0], ['isdefault' => 1])->execute();
		}
		$status = $db->createCommand()->update('vtiger_language', ['isdefault' => 1], ['prefix' => $prefix])->execute();
		if ($status)
			$status = true;
		else
			$status = false;
		\App\Log::trace("Exiting Settings_LangManagement_Module_Model::setAsDefault() method ...");
		return array('success' => $status, 'prefixOld' => $prefixOld);
	}

	public function getStatsData($langBase, $langs, $byModule = false)
	{
		$filesName = $this->getModFromLang($langBase);
		if (strpos($langs, $langBase) === false) {
			$langs .= ',' . $langBase;
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
			if (!in_array($id, ['php', 'js']))
				continue;
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
