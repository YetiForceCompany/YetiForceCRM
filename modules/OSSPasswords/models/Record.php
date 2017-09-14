<?php

/**
 * OSSPasswords record model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
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

		if ($db->numRows($result) == 1)
			return $db->queryResult($result, 0, 'password');
		else if ($db->numRows($result) == 0)
			return $db->queryResult($result, 0, '');

		return false;
	}
	/*
	 * Funkcja zapisująca plik konfiguracyjny ini
	 * @array - tablica z konfiguracją
	 * @file - ścieżka do pliku
	 * @return - true/false
	 */

	public function writePhpIni($array, $file)
	{
		$res = [];
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
	 * @return array|boolean
	 */

	public function getConfiguration()
	{
		return (new \App\Db\Query())->from('vtiger_passwords_config')->one();
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
			return array('error' => true, 'message' => \App\Language::translate('LBL_NULLPASS', 'OSSPasswords'));
		}

		$config = $this->getConfiguration();
		$min = $config['pass_length_min'];
		$max = $config['pass_length_max'];

		if ($passLength < $min)
			return array('error' => true, 'message' => \App\Language::translate('LBL_PASS_TOOSHORT', 'OSSPasswords'));
		else if ($passLength > $max)
			return array('error' => true, 'message' => \App\Language::translate('LBL_PASS_TOOLONG', 'OSSPasswords'));

		$onlyStars = true;
		for ($i = 0; $i < $passLength; $i++) {
			if ($password[$i] != '*') {
				$onlyStars = false;
				break;
			}
		}

		if ($onlyStars)
			return array('error' => true, 'message' => \App\Language::translate('LBL_ONLY_STARS', 'OSSPasswords'));

		return array('error' => false, 'message' => '');
	}
}
