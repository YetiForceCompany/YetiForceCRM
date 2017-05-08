<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class CustomView_DeleteAjax_Action extends Vtiger_Action_Controller
{

	public function preProcess(\App\Request $request)
	{
		return true;
	}

	public function postProcess(\App\Request $request)
	{
		return true;
	}

	public function process(\App\Request $request)
	{
		$customViewModel = CustomView_Record_Model::getInstanceById($request->get('record'));

		$customViewModel->delete();
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
