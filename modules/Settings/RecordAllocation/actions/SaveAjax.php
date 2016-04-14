<?php

/**
 * @package YetiForce.Action
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_RecordAllocation_SaveAjax_Action extends Settings_Vtiger_Index_Action
{

	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
	}

	function save(Vtiger_Request $request)
	{
		$data = $request->get('param');
		Settings_RecordAllocation_Module_Model::saveRecordAllocation(array_filter($data));
		$responceToEmit = new Vtiger_Response();
		$responceToEmit->setResult($response);
		$responceToEmit->emit();
	}
}
