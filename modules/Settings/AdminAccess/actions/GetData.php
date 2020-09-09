<?php

/**
 * Settings admin access action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Settings admin access action class.
 */
class Settings_AdminAccess_GetData_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('access');
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Gets access configuration.
	 *
	 * @param App\Request $request
	 */
	public function access(App\Request $request)
	{
		$rows = $columns = $userData = [];
		foreach ($request->getArray('columns') as $key => $value) {
			$columns[$key] = $value['name'];
		}
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($request->getModule(false));
		$fields = $moduleModel->getListFields();

		$table = \App\Security\AdminAccess::MODULES_TABLE_NAME;
		$query = (new \App\Db\Query())->from($table);

		$conditions = [];
		foreach ($fields as $fieldModel) {
			if ($request->has($fieldModel->getName()) && '' !== $request->get($fieldModel->getName())) {
				$value = $moduleModel->getValueFromRequest($fieldModel->getName(), $request);
				if ($fieldModel->getTableName() !== $table) {
					$value = (new \App\Db\Query())->select(['module_id'])->from($fieldModel->getTableName())->where([$fieldModel->getColumnName() => $value]);
					$conditions[$moduleModel->getBaseIndex()] = $value;
				} else {
					$conditions[$fieldModel->getColumnName()] = $value;
				}
			}
		}
		$query->where($conditions);
		$filter = $query->count('id');

		$query->limit($request->getInteger('length'))->offset($request->getInteger('start'));
		$order = current($request->getArray('order', App\Purifier::ALNUM));
		if ($order && isset($columns[$order['column']], $fields[$columns[$order['column']]])) {
			$field = $fields[$columns[$order['column']]];
			$query->orderBy([$field->getColumnName() => \App\Db::ASC === strtoupper($order['dir']) ? \SORT_ASC : \SORT_DESC]);
		}
		$resultData = $query->indexBy('id')->all();

		if ($resultData) {
			$userData = (new \App\Db\Query())->from(\App\Security\AdminAccess::ACCESS_TABLE_NAME)
				->select(['module_id', 'user'])
				->where(['module_id' => array_keys($resultData)])->createCommand()->queryAllByGroup(2);
		}
		foreach ($resultData as $key => $row) {
			$row['user'] = $userData[$key] ?? [];
			$data = [];
			foreach ($fields as $fieldModel) {
				$data[] = $moduleModel->getDisplayValue($fieldModel->getName(), $row[$fieldModel->getColumnName()]);
			}
			$data[] = '<button type="button" class="btn btn-primary btn-sm js-show-modal" data-id="' . $row['id'] . '" title="' . \App\Language::translate('LBL_EDIT') . '" data-url="' . $moduleModel->getEditViewUrl($row['id']) . '"><span class="yfi yfi-full-editing-view"></span></button>';
			$rows[] = $data;
		}
		$result = [
			'draw' => $request->getInteger('draw'),
			'iTotalDisplayRecords' => $filter,
			'aaData' => $rows
		];

		header('content-type: text/json; charset=UTF-8');
		echo \App\Json::encode($result);
	}
}
