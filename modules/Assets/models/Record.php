<?php

/**
 * Record Class for Assets
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Assets_Record_Model extends Vtiger_Record_Model
{

	protected $privileges = ['editFieldByModal' => true];

	public function getFieldToEditByModal()
	{
		if($this->has('changeEditFieldByModal')){
			return $this->get('changeEditFieldByModal');
		}
		return 'assetstatus';
	}
}
