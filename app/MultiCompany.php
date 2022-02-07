<?php
/**
 * Multi company file.
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
 * Multi company basic class.
 */
class MultiCompany
{
	/**
	 * Get user ids by multi company id.
	 *
	 * @param int $companyId
	 *
	 * @return array
	 */
	public static function getUsersByCompany(int $companyId): array
	{
		if (Cache::has('getUsersByCompany', $companyId)) {
			return Cache::get('getUsersByCompany', $companyId);
		}
		$rows = (new Db\Query())->select(['vtiger_user2role.userid'])->from('vtiger_user2role')
			->innerJoin('vtiger_role', 'vtiger_user2role.roleid = vtiger_role.roleid')
			->where(['vtiger_role.company' => $companyId])->column();
		Cache::save('getUsersByCompany', $companyId, $rows);
		return $rows;
	}

	/**
	 * Get all multi company records.
	 *
	 * @return array
	 */
	public static function getAll(): array
	{
		if (Cache::has('getUsersByCompany', '')) {
			return Cache::get('getUsersByCompany', '');
		}
		$rows = (new Db\Query())->select(['u_#__multicompany.*'])->from('u_#__multicompany')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = u_#__multicompany.multicompanyid')
			->where(['vtiger_crmentity.deleted' => 0])->all() ?: [];
		Cache::save('getUsersByCompany', '', $rows);
		return $rows;
	}

	/**
	 * Gets roles by company ID.
	 *
	 * @param int $companyId
	 *
	 * @return int[]
	 */
	public static function getRolesByCompany(int $companyId): array
	{
		if (Cache::has('getCompanyRoles', '')) {
			$rolesByCompany = Cache::get('getCompanyRoles', '');
		} else {
			$rolesByCompany = (new \App\Db\Query())->select(['company', 'roleid'])->from('vtiger_role')->createCommand()->queryAllByGroup(2);
			Cache::save('getCompanyRoles', '', $rolesByCompany);
		}
		return $rolesByCompany[$companyId] ?? [];
	}
}
