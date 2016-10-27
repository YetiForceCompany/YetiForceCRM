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

class OSSPasswords_Record_Model extends Vtiger_Record_Model
{
	/*
	 * Funkcja odszyfrowywująca i przekazująca dalej hasło
	 * @recordId - numer id hasła
	 * @return - odszyfrowane hasło lub false
	 */

	public function getPassword($recordId)
	{
		$db = PearDatabase::getInstance();

		$sql = '';
		// check if passwords are encrypted
		if (file_exists('modules/OSSPasswords/config.ini.php')) {
			$config = parse_ini_file('modules/OSSPasswords/config.ini.php');
			$sql = "SELECT AES_DECRYPT(`password`, '{$config['key']}') AS `password` 
                FROM `vtiger_osspasswords` 
                WHERE `osspasswordsid` = ? LIMIT 1;";
		} else {
			$sql = "SELECT `password` 
                FROM `vtiger_osspasswords` 
                WHERE `osspasswordsid` = ? LIMIT 1;";
		}

		$params = array($recordId);
		$result = $db->pquery($sql, $params, true);

		if ($db->num_rows($result) == 1)
			return $db->query_result($result, 0, 'password');
		else if ($db->num_rows($result) == 0)
			return $db->query_result($result, 0, '');

		return false;
	}
	/*
	 * Funkcja zapisująca plik konfiguracyjny ini
	 * @array - tablica z konfiguracją
	 * @file - ścieżka do pliku
	 * @return - true/false
	 */

	public function write_php_ini($array, $file)
	{
		$res = array();
		$res[] = ';<?php exit;';
		foreach ($array as $key => $val) {
			if (is_array($val)) {
				$res[] = "[$key]";
				foreach ($val as $skey => $sval) {
					if (is_array($sval)) {
						foreach ($sval as $i => $v) {
							$res[] = "{$skey}[$i] = $v";
						}
					} else {
						$res[] = "$skey = $sval";
					}
				}
			} else
				$res[] = "$key = $val";
		}

		$res[] = ';?>';
		if (!file_put_contents($file, implode("\r\n", $res), LOCK_EX))
			return false;

		return true;
	}
	/*
	 * Zwraca dane konfiguracyjne haseł
	 * @return - tablica z konfiguracja lub false
	 */

	public function getConfiguration()
	{
		$db = PearDatabase::getInstance();

		$sql = 'SELECT * FROM vtiger_passwords_config;';

		$result = $db->query($sql, true);

		if ($db->num_rows($result) === 1)
			return $db->fetch_array($result);
		else if ($db->num_rows($result) === 0)
			return false;
	}
	/*
	 * Sprawdza poprawność hasła - długość, czy nie jest puste i czy nie zawiera samych gwiazdek
	 * @password - nowe hasło
	 * @return - error: true/false, message: komunikat błędu
	 *
	 */

	public function checkPassword($password)
	{
		$passLength = strlen($password);

		if ($passLength == 0) {
			return array('error' => true, 'message' => vtranslate('LBL_NULLPASS', 'OSSPasswords'));
		}

		$config = $this->getConfiguration();
		$min = $config['pass_length_min'];
		$max = $config['pass_length_max'];

		if ($passLength < $min)
			return array('error' => true, 'message' => vtranslate('LBL_PASS_TOOSHORT', 'OSSPasswords'));
		else if ($passLength > $max)
			return array('error' => true, 'message' => vtranslate('LBL_PASS_TOOLONG', 'OSSPasswords'));

		$onlyStars = true;
		for ($i = 0; $i < $passLength; $i++) {
			if ($password[$i] != '*') {
				$onlyStars = false;
				break;
			}
		}

		if ($onlyStars)
			return array('error' => true, 'message' => vtranslate('LBL_ONLY_STARS', 'OSSPasswords'));

		return array('error' => false, 'message' => '');
	}
}
