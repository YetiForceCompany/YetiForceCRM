<?php
/**
 * Company basic file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Company basic class.
 */
class Company extends Base
{
	/**
	 * Classification of enterprises due to their size.
	 *
	 * @var int[]
	 */
	public static $sizes = [
		'Micro' => 20,
		'Small' => 50,
		'Medium' => 250,
		'Large' => 1000,
		'Corporation' => 0,
	];

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
		$rows = (new Db\Query())->from('s_#__companies')->all(Db::getInstance('admin'));
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
			Db::getInstance('admin')->createCommand()
				->update('s_#__companies', [
					'status' => $status,
				], ['name' => $name])->execute();
		}
		Db::getInstance('admin')->createCommand()
			->update('s_#__companies', [
				'status' => $status,
			])->execute();
	}

	/**
	 * Send registration data to YetiForce API server.
	 *
	 * @param array $companiesNew
	 *
	 * @throws Exceptions\Security
	 *
	 * @return bool
	 */
	public static function registerOnline(array $companiesNew): bool
	{
		if (empty($companiesNew)) {
			return false;
		}
		$companies = \array_column(static::getAll(), 'id', 'id');
		foreach ($companiesNew as $company) {
			if (!isset($companies[$company['id']])) {
				throw new Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $company['id']);
			}
			$recordModel = \Settings_Companies_Record_Model::getInstance((int) $company['id']);
			$field = $recordModel->getModule()->getFormFields();
			foreach (array_keys($field) as $fieldName) {
				if (isset($company[$fieldName])) {
					$uiTypeModel = $recordModel->getFieldInstanceByName($fieldName)->getUITypeModel();
					$uiTypeModel->validate($company[$fieldName], true);
					$recordModel->set($fieldName, $uiTypeModel->getDBValue($company[$fieldName]));
				}
			}
			$recordModel->saveCompanyLogos();
			$recordModel->save();
		}
		return (new YetiForce\Register())->register();
	}

	/**
	 * Get company size.
	 *
	 * @return string
	 */
	public static function getSize(): string
	{
		$count = User::getNumberOfUsers();
		$return = 'Micro';
		$last = 0;
		foreach (self::$sizes as $size => $value) {
			if (0 !== $value && $count <= $value && $count > $last) {
				return $size;
			}
			if (0 === $value && $count > 1000) {
				$return = $size;
			}
			$last = $value;
		}
		return $return;
	}

	/**
	 * Compare company size.
	 *
	 * @param string $package
	 *
	 * @return bool
	 */
	public static function compareSize(string $package): bool
	{
		$size = self::$sizes[$package] ?? '';
		if (0 === $size) {
			return true;
		}
		return $size >= User::getNumberOfUsers();
	}
}
