<?php

/**
 * Settings Password save model class.
 *
 * @package Settings
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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

	/**
	 * Get conditions for password modification.
	 *
	 * @return array
	 */
	public static function getPasswordChangeDateCondition(): array
	{
		$conditions = [['force_password_change', 'e', '1']];
		$date = null;
		$passConfig = static::getUserPassConfig();
		$delayDays = $passConfig['change_time'];
		if (!empty($delayDays)) {
			$date = date('Y-m-d', strtotime("-{$delayDays} day"));
			$date = \App\Fields\Date::formatToDisplay($date);
			$conditions[] = ['date_password_change', 'b', $date];
		}

		return [[[]], $conditions];
	}

	/**
	 * Gets encryption modules.
	 *
	 * @return array
	 */
	public static function getEncryptionModules(): array
	{
		$modules = (new \App\Db\Query())->select(['vtiger_tab.tabid', 'vtiger_tab.name'])->from('vtiger_field')
			->innerJoin('vtiger_tab', 'vtiger_field.tabid=vtiger_tab.tabid')
			->where(['vtiger_tab.presence' => 0, 'vtiger_tab.isentitytype' => 1, 'vtiger_field.uitype' => 99, 'vtiger_field.presence' => [0, 2]])->createCommand()->queryAllByGroup(0);
		foreach ($modules as $key => $moduleName) {
			if (null === \App\Config::module($moduleName, 'encryptionMethod', null) || null === \App\Config::module($moduleName, 'encryptionPass', null)) {
				unset($modules[$key]);
			}
		}
		return $modules;
	}
}
