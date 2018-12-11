<?php
/**
 * RecordPopover model class for OSSTimeControl.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Class OSSTimeControl_RecordPopover_Model.
 */
class OSSTimeControl_RecordPopover_Model extends Vtiger_RecordPopover_Model
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
		if ($this->recordModel->isEditable()) {
			$links[] = [
				'linktype' => 'RECORD_POPOVER_VIEW',
				'linklabel' => 'LBL_EDIT',
				'linkhref' => true,
				'linkurl' => $this->recordModel->getEditViewUrl(),
				'linkicon' => 'fas fa-edit',
				'linkclass' => 'btn-sm btn-outline-secondary',
			];
		}
		if ($this->recordModel->isViewable()) {
			$links[] = [
				'linktype' => 'RECORD_POPOVER_VIEW',
				'linklabel' => 'DetailView',
				'linkhref' => true,
				'linkurl' => $this->recordModel->getDetailViewUrl(),
				'linkicon' => 'fas fa-th-list',
				'linkclass' => 'btn-sm btn-outline-secondary',
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
		return ['date_start' => 'far fa-clock', 'time_start' => 'far fa-clock', 'time_end' => 'far fa-clock', 'due_date' => 'far fa-clock', 'sum_time' => 'far fa-clock',
			'osstimecontrol_no' => 'fas fa-bars', 'timecontrol_type' => 'fas fa-question-circle', 'linkextend' => '', 'link' => '', 'process' => '', 'subprocess' => '',
			'osstimecontrol_status' => 'far fa-star', 'assigned_user_id' => 'fas fa-user'];
	}
}
