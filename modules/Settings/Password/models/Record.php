<?php

/**
 * Settings Password save model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_Password_Record_Model extends Vtiger_Record_Model
{

	public static function getPassDetail($type = false)
	{
		$query = (new \App\Db\Query())->from('vtiger_password');
		if ($type) {
			$query->where(['type' => $type]);
		}
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$resp[$row['type']] = $row['val'];
		}
		return $resp;
	}

	public static function setPassDetail($type, $vale)
	{
		App\Db::getInstance()->createCommand()
			->update('vtiger_password', ['val' => $vale], ['type' => $type])
			->execute();
	}

	public static function validation($type, $vale)
	{
		if ($type == 'min_length' || $type == 'max_length') {
			return is_numeric($vale);
		}
		if ($type == 'big_letters' || $type == 'small_letters' || $type == 'numbers' || $type == 'special') {
			if ($vale === 'false' || $vale === 'true') {
				return true;
			} else {
				return false;
			}
		}
	}

	public static function checkPassword($pass)
	{
		$conf = self::getPassDetail();
		$moduleName = 'Settings:Password';
		if (strlen($pass) > $conf['max_length']) {
			return \App\Language::translate("Maximum password length", $moduleName) . ' ' . $conf['max_length'] . ' ' . \App\Language::translate("characters", $moduleName);
		}
		if (strlen($pass) < $conf['min_length']) {
			return \App\Language::translate("Minimum password length", $moduleName) . ' ' . $conf['min_length'] . ' ' . \App\Language::translate("characters", $moduleName);
		}
		if ($conf['numbers'] == 'true' && !preg_match("#[0-9]+#", $pass)) {
			return \App\Language::translate("Password should contain numbers", $moduleName);
		}
		if ($conf['big_letters'] == 'true' && !preg_match("#[A-Z]+#", $pass)) {
			return \App\Language::translate("Uppercase letters from A to Z", $moduleName);
		}
		if ($conf['small_letters'] == 'true' && !preg_match("#[a-z]+#", $pass)) {
			return \App\Language::translate("Lowercase letters a to z", $moduleName);
		}
		if ($conf['special'] == 'true' && !preg_match("/[!@#$%^&*()\-_=+{};:,<.>]/", $pass)) {
			return \App\Language::translate("Password should contain special characters", $moduleName);
		}
		return false;
	}
}
