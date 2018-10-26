<?php
/**
 * RecordPopover view Class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

/**
 * Class Reservations_RecordPopover_View.
 */
class Reservations_RecordPopover_View extends OSSTimeControl_RecordPopover_View
{
	public function getFields()
	{
		return ['date_start' => 'far fa-clock', 'time_start' => 'far fa-clock', 'time_end' => 'far fa-clock', 'due_date' => 'far fa-clock', 'sum_time' => 'far fa-clock',
			'reservations_no' => 'fas fa-bars', 'linkextend' => '', 'link' => '', 'process' => '', 'subprocess' => '', 'reservations_status' => 'far fa-star',
			'assigned_user_id' => 'fas fa-user'];
	}
}
