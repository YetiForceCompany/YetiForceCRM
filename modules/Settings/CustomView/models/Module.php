<?php

/**
 * CustomView module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_CustomView_Module_Model extends Settings_Vtiger_Module_Model
{
	public function getCustomViews($tabId)
	{
		$dataReader = (new App\Db\Query())->select(['vtiger_customview.*'])
			->from('vtiger_customview')
			->leftJoin('vtiger_tab', 'vtiger_tab.name = vtiger_customview.entitytype')
			->where(['vtiger_tab.tabid' => $tabId])
			->andWhere(['not', ['vtiger_customview.presence' => 2]])
			->orderBy(['vtiger_customview.sequence' => SORT_ASC])
			->createCommand()->query();
		$moduleEntity = [];
		while ($row = $dataReader->read()) {
			$moduleEntity[$row['cvid']] = $row;
		}
		$dataReader->close();

		return $moduleEntity;
	}

	public function getFilterPermissionsView($cvId, $action)
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

	public static function setDefaultUsersFilterView($tabid, $cvId, $user, $action)
	{
		if ('add' == $action) {
			$dataReader = (new App\Db\Query())->select(['vtiger_customview.viewname'])
				->from('vtiger_user_module_preferences')
				->leftJoin('vtiger_customview', 'vtiger_user_module_preferences.default_cvid = vtiger_customview.cvid')
				->where(['vtiger_user_module_preferences.tabid' => $tabid, 'vtiger_user_module_preferences.userid' => $user])
				->createCommand()->query();
			if ($dataReader->count()) {
				return $dataReader->readColumn(0);
			}
			\App\Db::getInstance()->createCommand()->insert('vtiger_user_module_preferences', [
				'userid' => $user,
				'tabid' => $tabid,
				'default_cvid' => $cvId,
			])->execute();
		} elseif ('remove' == $action) {
			\App\Db::getInstance()->createCommand()->delete('vtiger_user_module_preferences', ['userid' => $user, 'tabid' => $tabid, 'default_cvid' => $cvId])->execute();
		}
		return false;
	}

	/**
	 * Function to delete filter.
	 *
	 * @param int $cvId
	 */
	public static function delete($cvId)
	{
		$db = \App\Db::getInstance();
		if (is_numeric($cvId)) {
			$db->createCommand()->delete('vtiger_customview', ['cvid' => $cvId])->execute();
			$db->createCommand()->delete('vtiger_user_module_preferences', ['default_cvid' => $cvId])->execute();
			// To Delete the mini list widget associated with the filter
			$db->createCommand()->delete('vtiger_module_dashboard_widgets', ['filterid' => $cvId])->execute();
			\App\CustomView::clearCacheById($cvId);
		}
	}

	/**
	 * Function to update parameter.
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	public static function updateField($params)
	{
		$authorizedFields = ['setdefault', 'privileges', 'featured', 'sort'];
		$dbCommand = \App\Db::getInstance()->createCommand();
		$cvid = $params['cvid'];
		$name = $params['name'];
		if (is_numeric($cvid) && \in_array($name, $authorizedFields)) {
			if ('setdefault' == $name && 1 == $params['value']) {
				$dbCommand->update('vtiger_customview', ['setdefault' => 0], ['entitytype' => $params['mod']])->execute();
			}
			$dbCommand->update('vtiger_customview', [$name => $params['value']], ['cvid' => $cvid])->execute();
			\App\CustomView::clearCacheById($cvid);
			return true;
		}
		return false;
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

	public static function updateOrderAndSort($params)
	{
		$customViewModel = CustomView_Record_Model::getInstanceById($params['cvid']);
		$moduleName = $customViewModel->get('entitytype');
		$currentView = App\CustomView::getCurrentView($moduleName);
		if ($currentView === $params['cvid']) {
			App\CustomView::setSortBy($moduleName, $params['value'] ? \App\Json::decode($params['value']) : null);
		}
	}
}
