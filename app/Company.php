<?php

namespace App;

/**
 * Company basic class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
	 * @param int         $status
	 * @param string|null $name
	 *
	 * @throws \yii\db\Exception
	 */
	public static function statusUpdate(int $status, ?string $name = null)
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
	 * Send registration data to YetiForce API server.
	 *
	 * @param array $companiesNew
	 *
	 * @throws \App\Exceptions\Security
	 *
	 * @return bool
	 */
	public static function registerOnline(array $companiesNew): bool
	{
		if (empty($companiesNew)) {
			return false;
		}
		$companies = \array_column(static::getAll(), 'id', 'id');
		foreach ($companiesNew as $key => $company) {
			if (!isset($companies[$key])) {
				throw new Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $key);
			}
			$recordModel = \Settings_Companies_Record_Model::getInstance((int) $key);
			$field = $recordModel->getModule()->getFormFields();
			foreach (array_keys($field) as $fieldName) {
				if (isset($company[$fieldName])) {
					$uiTypeModel = $recordModel->getFieldInstanceByName($fieldName)->getUITypeModel();
					$uiTypeModel->validate($company[$fieldName], true);
					$recordModel->set($fieldName, $uiTypeModel->getDBValue($company[$fieldName]));
				}
			}
			$recordModel->save();
		}
		return (new \App\YetiForce\Register())->register();
	}
}
