<?php
/**
 * RecordPopover model class for Calendar.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Class Calendar_RecordPopover_Model.
 */
class Calendar_RecordPopover_Model extends Vtiger_RecordPopover_Model
{
	/**
	 * {@inheritdoc}
	 */
	public function getFields(): array
	{
		$summaryFields = [];
		$fieldsModel = $this->recordModel->getModule()->getFields();
		foreach ($this->getFieldsIcon() as $fieldName => $icon) {
			$fieldModel = $fieldsModel[$fieldName] ?? '';
			if ($fieldModel && $fieldModel->isViewableInDetailView() && !$this->recordModel->isEmpty($fieldName)) {
				$summaryFields[$fieldName] = $fieldModel;
			}
		}
		return $summaryFields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderLinks(): array
	{
		$links = [];
		if (AppConfig::module('Calendar', 'CALENDAR_VIEW') === 'Extended') {
			$detailUrl = "index.php?module={$this->moduleName}&view=ActivityState&record={$this->recordModel->getId()}";
			$editUrl = $this->recordModel->isEditable() ? "index.php?module={$this->moduleName}&view=EventForm&record={$this->recordModel->getId()}" : '';
		} else {
			$detailUrl = $this->recordModel->getFullDetailViewUrl();
			$editUrl = $this->recordModel->isEditable() ? $this->recordModel->getEditViewUrl() : '';
		}
		if ($this->recordModel->isEditable()) {
			$links[] = [
				'linktype' => 'RECORD_POPOVER_VIEW',
				'linklabel' => 'LBL_EDIT',
				'linkhref' => true,
				'linkurl' => $editUrl,
				'linkicon' => 'fas fa-edit',
				'linkclass' => 'btn-sm btn-outline-secondary js-calendar-popover__button',
			];
		}
		if ($this->recordModel->isViewable()) {
			$links[] = [
				'linktype' => 'RECORD_POPOVER_VIEW',
				'linklabel' => 'DetailView',
				'linkhref' => true,
				'linkurl' => $detailUrl,
				'linkicon' => 'fas fa-th-list',
				'linkclass' => 'btn-sm btn-outline-secondary js-calendar-popover__button',
			];
		}
		$linksModels = parent::getHeaderLinks();
		foreach ($links as $link) {
			$linksModels[] = Vtiger_Link_Model::getInstanceFromValues($link);
		}
		return $linksModels;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFieldsIcon(): array
	{
		return ['date_start' => 'far fa-clock', 'due_date' => 'far fa-clock', 'location' => 'fas fa-globe',
			'taskpriority' => 'fas fa-exclamation-circle', 'activitystatus' => 'fas fa-question-circle',
			'linkextend' => '', 'link' => '', 'process' => '', 'subprocess' => '', 'state' => 'far fa-star',
			'visibility' => 'fas fa-eye', 'assigned_user_id' => 'fas fa-user'];
	}
}
