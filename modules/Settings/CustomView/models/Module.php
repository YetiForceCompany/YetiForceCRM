<?php

/**
 * CustomView module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_CustomView_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Gets members filter by type.
	 *
	 * @param int    $cvId
	 * @param string $action
	 *
	 * @return array
	 */
	public function getFilterPermissionsView(int $cvId, string $action): array
	{
		$query = new App\Db\Query();
		if ('default' == $action) {
			$query->select(['userid'])
				->from('vtiger_user_module_preferences')
				->where(['default_cvid' => $cvId])
				->orderBy(['userid' => SORT_ASC]);
		} elseif ('featured' == $action) {
			$query->select(['user'])
				->from('u_#__featured_filter')
				->where(['cvid' => $cvId])
				->orderBy(['user' => SORT_ASC]);
		} elseif ('permissions' == $action) {
			$query->select(['member'])
				->from('u_#__cv_privileges')
				->where(['cvid' => $cvId])
				->orderBy(['member' => SORT_ASC]);
		}
		$dataReader = $query->createCommand()->query();
		$users = [];
		while ($user = $dataReader->readColumn(0)) {
			$members = explode(':', $user);
			$users[$members[0]][] = $user;
		}
		$dataReader->close();

		return $users;
	}

	public static function upadteSequences($params)
	{
		$db = App\Db::getInstance();
		$caseSequence = 'CASE ';
		foreach ($params as $sequence => $cvId) {
			$caseSequence .= ' WHEN ' . $db->quoteColumnName('cvid') . ' = ' . $db->quoteValue($cvId) . ' THEN ' . $db->quoteValue($sequence);
		}
		$caseSequence .= ' END';
		$i = $db->createCommand()->update('vtiger_customview', ['sequence' => new yii\db\Expression($caseSequence)], ['cvid' => $params])->execute();
		foreach ($params as $cvId) {
			\App\CustomView::clearCacheById($cvId);
		}
		return $i;
	}

	public function getUrlToEdit($module, $record)
	{
		return "index.php?module=CustomView&view=EditAjax&source_module=$module&record=$record";
	}

	public function getCreateFilterUrl($module)
	{
		return 'index.php?module=CustomView&view=EditAjax&source_module=' . $module;
	}

	public function getUrlDefaultUsers($module, $cvid, $isDefault)
	{
		return 'index.php?module=CustomView&parent=Settings&view=FilterPermissions&type=default&sourceModule=' . $module . '&cvid=' . $cvid . '&isDefault=' . $isDefault;
	}

	public function getFeaturedFilterUrl($module, $cvid)
	{
		return 'index.php?module=CustomView&parent=Settings&view=FilterPermissions&type=featured&sourceModule=' . $module . '&cvid=' . $cvid;
	}

	public function getSortingFilterUrl($module, $cvid)
	{
		return 'index.php?module=CustomView&parent=Settings&view=SortOrderModal&type=featured&sourceModule=' . $module . '&cvid=' . $cvid;
	}

	/**
	 * Gets URL for modal window with permission settings.
	 *
	 * @param string $module
	 * @param int    $cvid
	 *
	 * @return string
	 */
	public function getPrivilegesUrl(string $module, int $cvid): string
	{
		return 'index.php?module=CustomView&parent=Settings&view=FilterPermissions&type=permissions&sourceModule=' . $module . '&cvid=' . $cvid;
	}

	public static function getSupportedModules()
	{
		$modulesList = [];
		$dataReader = (new App\Db\Query())
			->select(['vtiger_tab.tabid', 'vtiger_customview.entitytype'])
			->from('vtiger_customview')
			->leftJoin('vtiger_tab', 'vtiger_tab.name = vtiger_customview.entitytype')
			->where(['vtiger_tab.presence' => 0])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$modulesList[$row['tabid']] = $row['entitytype'];
		}
		$dataReader->close();

		return $modulesList;
	}
}
