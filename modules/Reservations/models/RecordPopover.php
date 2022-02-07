<?php
/**
 * RecordPopover model class for Reservation.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Class Reservations_RecordPopover_Model.
 */
class Reservations_RecordPopover_Model extends Vtiger_RecordPopover_Model
{
	/** {@inheritdoc} */
	public function getFieldsIcon(): array
	{
		return ['date_start' => 'far fa-clock', 'time_start' => 'far fa-clock', 'time_end' => 'far fa-clock', 'due_date' => 'far fa-clock', 'sum_time' => 'far fa-clock',
			'reservations_no' => 'fas fa-bars', 'linkextend' => '', 'link' => '', 'process' => '', 'subprocess' => '', 'reservations_status' => 'far fa-star',
			'assigned_user_id' => 'fas fa-user'];
	}
}
