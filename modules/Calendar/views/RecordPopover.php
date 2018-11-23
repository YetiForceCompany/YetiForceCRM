<?php
/**
 * RecordPopover view Class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Calendar_RecordPopover_View.
 */
class Calendar_RecordPopover_View extends Vtiger_RecordPopover_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		$fieldsModel = $recordModel->getModule()->getFields();
		$summaryFields = [];
		$fields = $this->getFields();
		foreach ($fields as $fieldName => $icon) {
			$fieldModel = $fieldsModel[$fieldName] ?? '';
			if ($fieldModel && $fieldModel->isViewableInDetailView() && !$recordModel->isEmpty($fieldName)) {
				$summaryFields[$fieldName] = $fieldModel;
			}
		}
		if ($moduleName === 'Calendar' && AppConfig::module('Calendar', 'CALENDAR_VIEW') === 'Extended') {
			$detailUrl = "index.php?module={$moduleName}&view=ActivityState&record={$recordModel->getId()}";
			$editUrl = $recordModel->isEditable() ? "index.php?module={$moduleName}&view=EventForm&record={$recordModel->getId()}" : '';
		} else {
			$detailUrl = $recordModel->getFullDetailViewUrl();
			$editUrl = $recordModel->isEditable() ? $recordModel->getEditViewUrl() : '';
		}
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('FIELDS', $summaryFields);
		$viewer->assign('FIELDS_ICON', $fields);
		$viewer->assign('DETAIL_URL', $detailUrl);
		$viewer->assign('EDIT_URL', $editUrl);
		$viewer->view('RecordPopover.tpl', $this->getModuleNameTpl($request));
	}

	/**
	 * Get array of fields with icons.
	 *
	 * @return array
	 */
	public function getFields()
	{
		return ['date_start' => 'far fa-clock', 'due_date' => 'far fa-clock', 'location' => 'fas fa-globe',
			'taskpriority' => 'fas fa-exclamation-circle', 'activitystatus' => 'fas fa-question-circle',
			'linkextend' => '', 'link' => '', 'process' => '', 'subprocess' => '', 'state' => 'far fa-star',
			'visibility' => 'fas fa-eye', 'assigned_user_id' => 'fas fa-user'];
	}

	/**
	 * Get module for tpl file.
	 *
	 * @param $request
	 *
	 * @return mixed
	 */
	public function getModuleNameTpl($request)
	{
		return $request->getModule();
	}
}
