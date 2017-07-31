<?php

/**
 * Record Class for IGRN
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IGRN_Record_Model extends Vtiger_Record_Model
{

	protected $privileges = ['editFieldByModal' => true];

	public function getFieldToEditByModal()
	{
		return [
			'addClass' => 'btn-default',
			'iconClass' => 'glyphicon-modal-window',
			'listViewClass' => '',
			'titleTag' => 'LBL_SET_RECORD_STATUS',
			'name' => 'igrn_status',
		];
	}
}
