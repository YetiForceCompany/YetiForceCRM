<?php

/**
 * Record Class for OSSSoldServices
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSSoldServices_Record_Model extends Vtiger_Record_Model
{

	protected $privileges = ['editFieldByModal' => true];

	public function getFieldToEditByModal()
	{
		if($this->has('changeEditFieldByModal')){
			return $this->get('changeEditFieldByModal');
		}
		return 'ssservicesstatus';
	}
}
