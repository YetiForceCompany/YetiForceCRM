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
	 * @return array
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
	 * Update company status.
	 *
	 * @param string      $status
	 * @param string|null $name
	 *
	 * @throws \yii\db\Exception
	 */
	public static function statusUpdate(string $status, ?string $name = null)
	{
		if ($name) {
			\App\Db::getInstance('admin')->createCommand()
				->update('s_#__companies', [
					'status' => $status
				], ['name' => $name])->execute();
		} else {
			\App\Db::getInstance('admin')->createCommand()
				->update('s_#__companies', [
					'status' => $status
				])->execute();
		}
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
		return (new \App\YetiForce\Register())->send();
	}
}
