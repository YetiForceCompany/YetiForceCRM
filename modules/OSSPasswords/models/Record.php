<?php

/**
 * OSSPasswords record model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSPasswords_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function to decrypt password.
	 *
	 * @param int $recordId
	 *
	 * @return bool|string
	 */
	public function getPassword($recordId)
	{
		$query = new App\Db\Query();
		// check if passwords are encrypted
		if (file_exists('modules/OSSPasswords/config.ini.php')) {
			$config = parse_ini_file('modules/OSSPasswords/config.ini.php');
			$query->select(['password' => new \yii\db\Expression("AES_DECRYPT(`password`, '{$config['key']}')")]);
		} else {
			$query->select(['password']);
		}
		$query->from('vtiger_osspasswords')
			->where(['osspasswordsid' => $recordId])
			->limit(1);

		if ($password = $query->scalar()) {
			return $password;
		}
		return false;
	}

	/**
	 * Function to save config ini.
	 *
	 * @param array  $array
	 * @param string $file
	 *
	 * @return bool
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
			} else {
				$res[] = "$key = $val";
			}
		}

		$res[] = ';?>';
		if (!file_put_contents($file, implode("\r\n", $res), LOCK_EX)) {
			return false;
		}
		return true;
	}

	/**
	 * Get configuration data.
	 *
	 * @return array|bool
	 */
	public function getConfiguration()
	{
		return (new \App\Db\Query())->from('vtiger_passwords_config')->one();
	}

	/**
	 * Check password length empty and chars.
	 *
	 * @param string $password
	 *
	 * @return array
	 */
	public function checkPassword($password)
	{
		$passLength = strlen($password);

		if (0 == $passLength) {
			return ['error' => true, 'message' => \App\Language::translate('LBL_NULLPASS', 'OSSPasswords')];
		}

		$config = $this->getConfiguration();
		$min = $config['pass_length_min'];
		$max = $config['pass_length_max'];

		if ($passLength < $min) {
			return ['error' => true, 'message' => \App\Language::translate('LBL_PASS_TOOSHORT', 'OSSPasswords')];
		}
		if ($passLength > $max) {
			return ['error' => true, 'message' => \App\Language::translate('LBL_PASS_TOOLONG', 'OSSPasswords')];
		}

		$onlyStars = true;
		for ($i = 0; $i < $passLength; ++$i) {
			if ('*' != $password[$i]) {
				$onlyStars = false;
				break;
			}
		}

		if ($onlyStars) {
			return ['error' => true, 'message' => \App\Language::translate('LBL_ONLY_STARS', 'OSSPasswords')];
		}
		return ['error' => false, 'message' => ''];
	}
}
