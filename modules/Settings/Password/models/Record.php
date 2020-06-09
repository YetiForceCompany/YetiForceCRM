<?php

/**
 * Settings Password save model class.
 *
 * @package Settings
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_Password_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Get user password configuration.
	 *
	 * @param string|bool $type
	 *
	 * @return array|string
	 */
	public static function getUserPassConfig($type = false)
	{
		if (\App\Cache::has('PasswordConfig', '')) {
			$detail = \App\Cache::get('PasswordConfig', '');
		} else {
			$dataReader = (new \App\Db\Query())->from('vtiger_password')->createCommand()->query();
			$detail = [];
			while ($row = $dataReader->read()) {
				$detail[$row['type']] = $row['val'];
			}
			$dataReader->close();
			\App\Cache::save('PasswordConfig', '', $detail);
		}
		return $type ? $detail[$type] : $detail;
	}

	public static function setPassDetail($type, $vale)
	{
		App\Db::getInstance()->createCommand()
			->update('vtiger_password', ['val' => $vale], ['type' => $type])
			->execute();
		\App\Cache::delete('PasswordConfig', '');
	}

	/**
	 * Validation.
	 *
	 * @param string $type
	 * @param string $vale
	 *
	 * @return bool
	 */
	public static function validation(string $type, string $vale): bool
	{
		$returnVal = false;
		if ('min_length' == $type) {
			$returnVal = is_numeric($vale) && (int) $vale <= (int) static::getUserPassConfig('max_length');
		} elseif ('max_length' == $type) {
			$returnVal = is_numeric($vale) && (int) $vale >= (int) static::getUserPassConfig('min_length');
		} elseif ('change_time' == $type || 'lock_time' == $type || 'pwned_time' == $type) {
			$returnVal = is_numeric($vale);
		} elseif ('big_letters' == $type || 'small_letters' == $type || 'numbers' == $type || 'special' == $type || 'pwned' == $type) {
			$returnVal = 'false' === $vale || 'true' === $vale;
		}
		return $returnVal;
	}

	public static function checkPassword($pass)
	{
		$conf = self::getUserPassConfig();
		$moduleName = 'Settings:Password';
		if (\strlen($pass) > $conf['max_length']) {
			return \App\Language::translate('Maximum password length', $moduleName) . ' ' . $conf['max_length'] . ' ' . \App\Language::translate('characters', $moduleName);
		}
		if (\strlen($pass) < $conf['min_length']) {
			return \App\Language::translate('Minimum password length', $moduleName) . ' ' . $conf['min_length'] . ' ' . \App\Language::translate('characters', $moduleName);
		}
		if ('true' == $conf['numbers'] && !preg_match('#[0-9]+#', $pass)) {
			return \App\Language::translate('Password should contain numbers', $moduleName);
		}
		if ('true' == $conf['big_letters'] && !preg_match('#[A-Z]+#', $pass)) {
			return \App\Language::translate('Uppercase letters from A to Z', $moduleName);
		}
		if ('true' == $conf['small_letters'] && !preg_match('#[a-z]+#', $pass)) {
			return \App\Language::translate('Lowercase letters a to z', $moduleName);
		}
		if ('true' == $conf['special'] && !preg_match('~[!"#$%&\'()*+,-./:;<=>?@[\]^_{|}]~', $pass)) {
			return \App\Language::translate('Password should contain special characters', $moduleName);
		}
		if ('true' == $conf['pwned'] && ($passStatus = App\Extension\PwnedPassword::check($pass)) && !$passStatus['status']) {
			return $passStatus['message'];
		}
		return false;
	}

	public static function getPasswordChangeDate()
	{
		$passConfig = static::getUserPassConfig();

		return date('Y-m-d', strtotime("-{$passConfig['change_time']} day"));
	}

	/**
	 * Checks if encrypt is active.
	 *
	 * @return array
	 */
	public static function isRunEncrypt()
	{
		return (new \App\Db\Query())->select(['status'])->from('s_#__batchmethod')->where(['method' => '\App\Encryption::recalculatePasswords'])->scalar();
	}
}
