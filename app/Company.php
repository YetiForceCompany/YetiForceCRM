<?php
/**
 * Company basic file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 6.5 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Company basic class.
 */
class Company extends Base
{
	/** @var string Edit view URL */
	public const EDIT_VIEW_URL = 'index.php?parent=Settings&module=Companies&view=Edit';

	/**
	 * Classification of enterprises due to their size.
	 *
	 * @var int[]
	 */
	public static array $sizes = [
		'Micro' => 20,
		'Small' => 50,
		'Medium' => 250,
		'Large' => 1000,
		'Corporation' => 0,
	];

	/**
	 * Function to get the instance of the Company model.
	 *
	 * @throws \App\Exceptions\DbException
	 *
	 * @return array|bool
	 */
	public static function getCompany()
	{
		if (Cache::staticHas('CompanyGet', '')) {
			return Cache::staticGet('CompanyGet', '');
		}
		$row = (new Db\Query())->from('s_#__companies')->one(Db::getInstance('admin'));
		if (!$row) {
			throw new Exceptions\DbException('LBL_RECORD_NOT_FOUND');
		}
		Cache::staticSave('CompanyGet', '', $row, Cache::LONG);

		return $row;
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
