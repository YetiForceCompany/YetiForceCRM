<?php

namespace App;

/**
 * Company basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Company extends Base
{
	/**
	 * Function to get the instance of the Company model.
	 *
	 * @param int $id
	 *
	 * @return \self
	 */
	public static function getAll()
	{
		if (Cache::has('CompanyGetAll', '')) {
			return Cache::get('CompanyGetAll', '');
		}
		$rows = (new \App\Db\Query())->from('s_#__companies')->all();
		Cache::save('CompanyGetAll', '', $rows, Cache::LONG);
		return $rows;
	}

	/**
	 * Register serial provided by User
	 * @param string $key
	 * @return bool
	 * @throws \Exception
	 */
	public static function registerSerial($key): bool
	{
		// set serial
		return random_int(0, 1) ? true : false;
	}

	/**
	 * Send registration data to YetiForce API server
	 * @param array $companiesNew
	 * @return bool
	 * @throws \App\Exceptions\IllegalValue
	 * @throws \yii\db\Exception
	 */
	public static function registerOnline($companiesNew): bool
	{
		if (!\is_array($companiesNew)) {
			return false;
		}
		foreach (static::getAll() as $companyCurrent) {
			if (!isset($companiesNew[$companyCurrent['id']])) {
				continue;
			}
			\App\Db::getInstance()->createCommand()
				->update('s_#__companies', [
					'name' => $companiesNew[$companyCurrent['id']]['name'],
					'industry' => $companiesNew[$companyCurrent['id']]['industry'],
					'city' => $companiesNew[$companyCurrent['id']]['city'],
					'country' => $companiesNew[$companyCurrent['id']]['country'],
					'website' => $companiesNew[$companyCurrent['id']]['website'],
					'email' => $companiesNew[$companyCurrent['id']]['email'],
				], ['id' => $companyCurrent['id']])
				->execute();
		}
		Cache::delete('CompanyGetAll', '');
		// Do registration magic
		return random_int(0, 1) ? true : false;
	}
}
