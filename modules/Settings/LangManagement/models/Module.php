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
		$adb = PearDatabase::getInstance();
		$sql = "SELECT * FROM vtiger_language ";
		$where = array();
		$output = false;
		if ($data && $data['prefix'] != '') {
			$sql .= "WHERE prefix = ?";
			$where[] = $data['prefix'];
		}
		$result = $adb->pquery($sql, $where, true);
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$output[$adb->query_result($result, $i, 'prefix')] = $adb->query_result_rowdata($result, $i);
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
				$lang_tab = $languageStrings;
			} else {
				vglobal('jsLanguageStrings');
				$lang_tab = $jsLanguageStrings;
			}
			if (is_array($lang_tab) && array_key_exists($langkey, $lang_tab)) {
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
				$lang_tab = $languageStrings;
			} else {
				vglobal('jsLanguageStrings');
				$lang_tab = $jsLanguageStrings;
			}
			if (!is_array($lang_tab) || !array_key_exists($langkey, $lang_tab)) {
				return array('success' => false, 'data' => 'LBL_DO_NOT_POSSIBLE_TO_MAKE_CHANGES');
			}
			$countLangEl = count(explode("\n", $lang_tab[$langkey]));
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
		$adb = PearDatabase::getInstance();
		$keys_php = array();
		$keys_js = array();
		$langs = array();
		$lang_tab = array();
		$resp_php = array();
		$resp_js = array();
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
				$lang_tab[$lang]['php'] = $languageStrings;
				$lang_tab[$lang]['js'] = $jsLanguageStrings;
				$keys_php = array_merge($keys_php, array_keys($languageStrings));
				$keys_js = array_merge($keys_js, array_keys($jsLanguageStrings));
			}
		}
		$keys_php = array_unique($keys_php);
		$keys_js = array_unique($keys_js);
		foreach ($keys_php as $key) {
			foreach ($langs as $language) {
				$resp_php[$key][$language] = htmlentities($lang_tab[$language]['php'][$key], ENT_QUOTES, "UTF-8");
			}
		}
		foreach ($keys_js as $key) {
			foreach ($langs as $language) {
				$resp_js[$key][$language] = htmlentities($lang_tab[$language]['js'][$key], ENT_QUOTES, "UTF-8");
			}
		}
		return array('php' => $resp_php, 'js' => $resp_js, 'langs' => $langs, 'keys' => $keys);
	}

	public function loadAllFieldsFromModule($lang, $mod, $ShowDifferences = 0)
	{
		$adb = PearDatabase::getInstance();
		$result = $adb->pquery("SELECT * FROM vtiger_field WHERE tabid = ? && `presence` IN (0,2)", array(\includes\Modules::getModuleId($mod)));
		$variablesFromFile = $this->loadLangTranslation($lang, 'HelpInfo', $ShowDifferences);
		$output = array();
		if (self::parse_data(',', $lang)) {
			$langs = explode(",", $lang);
		} else {
			$langs[] = $lang;
		}
		$output['langs'] = $langs;
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$row = $adb->query_result_rowdata($result, $i);
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
		$adb = PearDatabase::getInstance();
		if ($lang == '' || $lang == NULL) {
			$lang = 'en_us';
		} else {
			if (self::parse_data(',', $lang)) {
				$lang_a = explode(",", $lang);
				$lang = $lang_a[0];
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
				$lang_array = explode("|", $lang);
				unset($langs[$key]);
				$settings[$key] = vtranslate($lang_array[1], 'Settings:' . $lang_array[1]);
			} else {
				$langs[$key] = vtranslate($key, $key);
			}
		}
		return array('mods' => $langs, 'settings' => $settings);
	}

	public function add($params)
	{
		$db = PearDatabase::getInstance();
		if (self::getLang($params)) {
			return ['success' => false, 'data' => 'LBL_LangExist'];
		}
		self::CopyDir('languages/en_us/', 'languages/' . $params['prefix'] . '/');
		$db->insert('vtiger_language', [
			'id' => $db->getUniqueId('vtiger_language'),
			'name' => $params['name'],
			'prefix' => $params['prefix'],
			'label' => $params['label'],
		]);
		return ['success' => true, 'data' => 'LBL_AddDataOK'];
	}

	public function save($params)
	{
		$adb = PearDatabase::getInstance();
		if ($params['type'] == 'Checkbox') {
			$val = $params['val'] == 'true' ? 1 : 0;
			$adb->pquery("UPDATE vtiger_language SET ? = ? WHERE prefix = ?;", array($params['name'], $val, $params['prefix']), true);
			return true;
		}
		return false;
	}

	public function saveView($params)
	{
		$adb = PearDatabase::getInstance();
		if (!is_array($params['value'])) {
			$params['value'] = array($params['value']);
		}
		$value = implode(',', $params['value']);
		$adb->pquery("UPDATE `vtiger_field` SET `helpinfo` = ? WHERE `fieldid` = ?;", array($value, $params['fieldid']), true);
		return array('success' => true, 'data' => 'LBL_SUCCESSFULLY_UPDATED');
	}

	public function delete($params)
	{
		$adb = PearDatabase::getInstance();
		$dir = "languages/" . $params['prefix'];
		if (file_exists($dir)) {
			self::DeleteDir($dir);
		}
		$adb->pquery("DELETE FROM vtiger_language WHERE prefix = ?;", array($params['prefix']), true);
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
		$log = vglobal('log');
		$log->debug("Entering Settings_LangManagement_Module_Model::setAsDefault(" . $lang . ") method ...");
		$db = PearDatabase::getInstance();
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
		$result = $db->pquery('SELECT * FROM `vtiger_language` WHERE `isdefault` = 1');
		if ($db->num_rows($result) == 1) {
			$prefixOld = $db->query_result($result, 0, 'prefix');
			$db->query('UPDATE `vtiger_language` SET `isdefault` = 0 where `isdefault` = 1');
		}
		$query = 'UPDATE `vtiger_language` SET `isdefault` = ? WHERE `prefix` = ?';
		$params = array(1, $prefix);
		$status = $db->pquery($query, $params);
		if ($status)
			$status = true;
		else
			$status = false;
		$log->debug("Exiting Settings_LangManagement_Module_Model::setAsDefault() method ...");
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
