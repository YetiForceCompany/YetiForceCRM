<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************** */

class Calendar_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
	/**
	 * Function to get the record model based on the request parameters.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelFromRequest(\App\Request $request)
	{
		if (!$request->isEmpty('record') && !$request->has('field')) {
			$className = Vtiger_Loader::getComponentClassName('Action', 'Save', $request->getModule());
			$recordModel = (new $className())->getRecordModelFromRequest($request);
		} else {
			$recordModel = parent::getRecordModelFromRequest($request);
		}
		return $recordModel;
	}
}
