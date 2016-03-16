<?php

/**
 * Record Class for IGIN
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IGIN_Record_Model extends Vtiger_Record_Model
{

	protected $privileges = ['editFieldByModal' => true];

	public function getFieldToEditByModal()
	{
		return [
			'addClass' => 'btn-default',
			'iconClass' => 'glyphicon-modal-window',
			'listViewClass' => '',
			'titleTag' => 'LBL_SET_RECORD_STATUS',
			'name' => 'igin_status',
		];
	}
}
