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
 * Class Calendar_RecordPopover_View.
 */
class OSSTimeControl_RecordPopover_View extends Calendar_RecordPopover_View
{
	public function getFields()
	{

		$fields = ['date_start' => 'far fa-clock', 'time_start' => 'far fa-clock', 'time_end' => 'far fa-clock', 'due_date' => 'far fa-clock', 'sum_time' => 'far fa-clock',
			'osstimecontrol_no' => 'fas fa-bars', 'timecontrol_type' => 'fas fa-question-circle',
			'linkextend' => '', 'link' => '', 'process' => '', 'subprocess' => '', 'osstimecontrol_status' => 'far fa-star',
			'visibility' => 'fas fa-eye', 'assigned_user_id' => 'fas fa-user'];
		return $fields;
	}

	public function getModuleNameTpl($request)
	{
		return 'Calendar';
	}
}
