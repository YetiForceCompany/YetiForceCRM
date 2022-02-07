<?php
/**
 * Kanban view file.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
/**
 * Kanban view class.
 */
class Vtiger_Kanban_View extends Vtiger_Index_View
{
	/** @var array Active board details. */
	protected $board;

	/** @var int|string List view name or id. */
	protected $viewName;

	/** @var \Vtiger_Module_Model Module Model instance. */
	protected $moduleModel;

	/** @var \Vtiger_Field_Model Field Model instance. */
	protected $fieldModel;

	/** @var array Kanban columns details . */
	protected $columns = [];

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModuleActionPermission($request->getModule(), 'Kanban')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleName = 'Vtiger' === $moduleName ? 'YetiForce' : $moduleName;
		$title = App\Language::translate($moduleName, $moduleName);
		return $title . ' ' . App\Language::translate('LBL_VIEW_KANBAN', $moduleName);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$ajax = $request->isAjax();
		$moduleName = $request->getModule();
		$this->viewName = $request->has('viewName') ? $request->getByType('viewName', \App\Purifier::ALNUM) : App\CustomView::getInstance($moduleName)->getViewId();
		$boards = \App\Utils\Kanban::getBoards($moduleName, true);
		if ($request->has('board') && isset($boards[$request->getInteger('board')])) {
			$this->board = $boards[$request->getInteger('board')];
		} else {
			$this->board = reset($boards);
		}
		$this->moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$this->fieldModel = \Vtiger_Field_Model::getInstanceFromFieldId($this->board['fieldid']);
		$this->loadColumns();
		$viewer = $this->getViewer($request);
		$viewer->assign('COLUMNS', $this->columns);
		$viewer->assign('ACTIVE_FIELD', $this->fieldModel);
		$viewer->assign('ACTIVE_BOARD', $this->board);
		$viewer->assign('MODULE_MODEL', $this->moduleModel);
		$viewer->assign('DATA', $this->getRecords($request));
		if ($ajax) {
			$viewer->view('Kanban/Kanban.tpl', $moduleName);
		} else {
			$viewer->assign('BOARDS', $boards);
			$viewer->assign('VIEW', $request->getByType('view', 1));
			$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAll($moduleName));
			$viewer->assign('VIEWID', $this->viewName);
			$viewer->view('Kanban/Main.tpl', $moduleName);
		}
	}

	/**
	 * Get columns for kanban.
	 *
	 * @return void
	 */
	public function loadColumns(): void
	{
		$moduleName = $this->fieldModel->getModuleName();
		$fieldName = $this->fieldModel->getName();
		$fieldNameForColor = App\Colors::sanitizeValue($fieldName);
		switch ($this->fieldModel->getFieldDataType()) {
			case 'picklist':
				$allowedValues = $this->fieldModel->getPicklistValues();
				$picklistValues = App\Fields\Picklist::getValues($fieldName);
				foreach ($picklistValues as $value) {
					$color = "{$moduleName}_{$fieldNameForColor}_" . App\Colors::sanitizeValue($value['picklistValue']);
					$this->columns[$value['picklistValue']] = [
						'label' => \App\Language::translate($value['picklistValue'], $moduleName),
						'icon' => $value['icon'] ?? '',
						'class' => '',
						'colorBg' => 'picklistLb_' . $color,
						'colorBr' => 'picklistCBr_' . $color,
						'description' => $value['description'] ?? '',
						'isEditable' => isset($allowedValues[$value['picklistValue']]),
					];
				}
				break;
			case 'owner':
				$owner = App\Fields\Owner::getInstance($moduleName);
				$owner->showRoleName = false;
				if ($users = $owner->getAccessibleUsers('Public')) {
					$allowedValues = $owner->getAccessibleUsers('private', 'owner');
					foreach ($users as $key => $value) {
						$this->columns[$key] = [
							'label' => $value,
							'image' => \App\User::getImageById($key)['url'] ?? '',
							'icon' => 'fas fa-user',
							'class' => '',
							'colorBg' => 'ownerCBg_' . $key,
							'colorBr' => 'ownerCBr_' . $key,
							'isEditable' => isset($allowedValues[$key]),
						];
					}
				}
				if ($group = $owner->getAccessibleGroups('private', 'owner', true)) {
					$allowedValues = $owner->getAccessibleGroups('private', 'owner', true);
					foreach ($group as $key => $value) {
						$this->columns[$key] = [
							'label' => $value,
							'icon' => 'adminIcon-groups',
							'class' => '',
							'colorBg' => 'ownerCBg_' . $key,
							'colorBr' => 'ownerCBr_' . $key,
							'isEditable' => isset($allowedValues[$key]),
						];
					}
				}
				break;
			default:
				throw new \App\Exceptions\AppException('ERR_NOT_ALLOWED_FIELD_TYPE');
		}
	}

	/**
	 * Get kanban records.
	 *
	 * @param \App\Request $request
	 *
	 * @return array
	 */
	protected function getRecords(App\Request $request): array
	{
		$moduleName = $request->getModule();
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('viewid', $this->viewName);
		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $this->viewName);
		$orderBy = $request->getArray('orderBy', \App\Purifier::STANDARD, [], \App\Purifier::SQL);
		if (!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
		}
		$pagingModel->set('limit', Vtiger_Paging_Model::PAGE_MAX_LIMIT);
		$listViewModel->getQueryGenerator()->setFields(array_merge($this->getSummaryFields(), ['id', $this->fieldModel->getName()], $this->board['sum_fields']));
		return $this->convert($listViewModel->getListViewEntries($pagingModel));
	}

	/**
	 * Get summary fields for kanban item.
	 *
	 * @return string[]
	 */
	protected function getSummaryFields(): array
	{
		if (empty($this->board['detail_fields'])) {
			$summaryFieldsList = $this->moduleModel->getSummaryViewFieldsList();
			$fields = [];
			if ($summaryFieldsList) {
				foreach ($summaryFieldsList as $fieldName => $fieldModel) {
					if ($fieldModel->isViewableInDetailView()) {
						$fields[] = $fieldName;
					}
				}
			}
			return $fields;
		}
		return $this->board['detail_fields'];
	}

	/**
	 * Convert the data.
	 *
	 * @param Vtiger_Record_Model[] $entries
	 *
	 * @return array
	 */
	protected function convert(array $entries): array
	{
		$columns = array_keys($this->columns);
		$fieldName = $this->fieldModel->getName();
		$columnCounter = $records = $sum = [];
		if ($sumFields = $this->board['sum_fields']) {
			foreach ($columns as $column) {
				foreach ($sumFields as $sumFieldName) {
					$sum[$column][$sumFieldName] = 0;
				}
			}
		}
		foreach ($entries as $id => $recordModel) {
			$column = $recordModel->get($fieldName);
			if (\in_array($column, $columns)) {
				$records[$column][$id] = $recordModel;
				if ($sumFields) {
					foreach ($sumFields as $sumFieldName) {
						if ($val = $recordModel->get($sumFieldName)) {
							$sum[$column][$sumFieldName] = ((float) $val) + $sum[$column][$sumFieldName];
						}
					}
				}
			}
		}
		foreach ($columns as $column) {
			$columnCounter[$column] = isset($records[$column]) ? \count($records[$column]) : 0;
		}
		return [
			'columnCounter' => $columnCounter,
			'sum' => $sum,
			'records' => $records,
		];
	}
}
