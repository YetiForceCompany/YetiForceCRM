<?php
/**
 * RecordPopover model class for Reservation.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Class Reservations_RecordPopover_Model.
 */
class Reservations_RecordPopover_Model extends Vtiger_RecordPopover_Model
{
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
			'reservations_no' => 'fas fa-bars', 'linkextend' => '', 'link' => '', 'process' => '', 'subprocess' => '', 'reservations_status' => 'far fa-star',
			'assigned_user_id' => 'fas fa-user'];
	}
}
