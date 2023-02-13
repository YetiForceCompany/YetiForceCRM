<?php

/**
 * Groups get data action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Kon <a.kon@yetiforce.com>
 */

/**
 * Groups get data action class.
 */
class Settings_Groups_GetData_Action extends \App\Controller\Action
{
	use \App\Controller\Traits\SettingsPermission;

	/** @var App\Request request */
	private $request;
	/** @var Settings_Vtiger_Module_Model */
	private $moduleModel;
	/** @var string Base index */
	private $baseIndex = '';

	/**
	 * Gets groups list.
	 *
	 * @param App\Request $request
	 */
	public function process(App\Request $request)
	{
		$this->request = $request;
		$rows = $columns = [];
		foreach ($this->request->getArray('columns') as $key => $value) {
			$columns[$key] = $value['name'];
		}

		$this->moduleModel = Settings_Vtiger_Module_Model::getInstance($this->request->getModule(false));
		$fields = $this->moduleModel->getListFields();

		$table = $this->moduleModel->baseTable;
		$this->baseIndex = $this->moduleModel->baseIndex;
		$query = (new \App\Db\Query())->select(["{$table}.{$this->baseIndex}"])->from($table);
		$query = $this->setConditions($query, $fields);
		$query->limit($this->request->getInteger('length'))->offset($this->request->getInteger('start'));
		$order = current($this->request->getArray('order', App\Purifier::ALNUM));
		if ($order && isset($columns[$order['column']], $fields[$columns[$order['column']]])) {
			$field = $fields[$columns[$order['column']]];
			$query->orderBy([$field->getColumnName() => \App\Db::ASC === strtoupper($order['dir']) ? \SORT_ASC : \SORT_DESC]);
		}
		$filter = $query->count("{$table}.{$this->baseIndex}");
		$resultData = $query->distinct()->all();

		foreach ($resultData as $key => $row) {
			$recordModel = Settings_Groups_Record_Model::getInstance($row['groupid']);
			$data = [];
			foreach ($fields as $fieldModel) {
				$data[] = $recordModel->getDisplayValue($fieldModel->getName());
			}
			$data[] = '<span class="js-detail-button d-none" data-recordurl="' . $recordModel->getDetailViewUrl() . '"></span>
			<a class="btn btn-primary btn-sm js-no-link" title="' . \App\Language::translate('LBL_EDIT') . '" href="' . $recordModel->getEditViewUrl() . '"><span class="yfi yfi-full-editing-view"></span></a><button type="button" class="btn btn-danger btn-sm ml-1 js-no-link js-show-modal" data-id="' . $row[$this->baseIndex] . '" title="' . \App\Language::translate('LBL_DELETE_RECORD') . '" data-url="' . $recordModel->getDeleteActionUrl() . '"><span class="fas fa-trash-alt js-no-link"></span></button>';

			$rows[] = $data;
		}
		$result = [
			'draw' => $this->request->getInteger('draw'),
			'iTotalDisplayRecords' => $filter,
			'aaData' => $rows
		];

		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($result);
	}

	private function setConditions(App\Db\Query $query, array $fields): App\Db\Query
	{
		$qualifiedModuleName = $this->request->getModule(false);
		$conditions = ['and'];
		$users = $groups = $roles = $rolesAndSubordinates = $accessibleGroups = [];
		foreach ($fields as $fieldModel) {
			$fieldModelName = $fieldModel->getName();
			if ($this->request->has($fieldModelName) && '' !== $this->request->get($fieldModelName)) {
				$value = $this->moduleModel->getValueFromRequest($fieldModelName, $this->request);
				switch ($fieldModelName) {
					case 'groupname':
						$allGroups = Settings_Groups_Record_Model::getAll();
						foreach ($allGroups as $groupId => $group) {
							$accessibleGroups[$groupId] = App\Language::translate($group->getName(), $qualifiedModuleName);
						}
						$groupIdsContainName = preg_grep("/{$value}/i", $accessibleGroups);
						$conditions[] = [$this->moduleModel->baseTable . '.' . $this->baseIndex => array_keys($groupIdsContainName)];
						break;
					case 'description':
						$conditions[] = ['like', $fieldModel->getColumnName(), $value];
						break;
					case 'modules':
						$query->innerJoin('vtiger_group2modules', 'vtiger_group2modules.groupid = vtiger_groups.groupid');
						$conditions[] = ['tabid' => $value];
						break;
					case 'members':
						foreach ($value as $memberTypeId) {
							[$type,  $memberId] = explode(':', $memberTypeId);
							switch ($type) {
								case 'Users':
									$users[] = (int) $memberId;
									break;
								case 'Groups':
									$groups[] = (int) $memberId;
									break;
								case 'Roles':
									$roles[] = $memberId;
									break;
								case 'RoleAndSubordinates':
									$rolesAndSubordinates[] = $memberId;
									break;
								default:
									break;
							}
						}
						if ($users) {
							$query->innerJoin('vtiger_users2group', 'vtiger_users2group.groupid = vtiger_groups.groupid');
							$conditions[] = ['userid' => $users];
						}
						if ($groups) {
							$query->innerJoin('vtiger_group2grouprel', 'vtiger_group2grouprel.groupid = vtiger_groups.groupid');
							$conditions[] = ['vtiger_group2grouprel.groupid' => $groups];
						}
						if ($roles) {
							$query->innerJoin('vtiger_group2role', 'vtiger_group2role.groupid = vtiger_groups.groupid');
							$conditions[] = ['vtiger_group2role.roleid' => $roles];
						}
						if ($rolesAndSubordinates) {
							$query->innerJoin('vtiger_group2rs', 'vtiger_group2rs.groupid = vtiger_groups.groupid');
							$conditions[] = ['vtiger_group2rs.roleandsubid' => $rolesAndSubordinates];
						}
						break;
					default:
					$conditions[] = [$fieldModel->getColumnName() => $value];
				}
			}
		}
		$query->where($conditions);
		return $query;
	}
}
