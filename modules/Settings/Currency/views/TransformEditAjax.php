<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Currency_TransformEditAjax_View extends Settings_Vtiger_IndexAjax_View
{
	public function process(App\Request $request)
	{
		$record = $request->getInteger('record');

		$currencyList = Settings_Currency_Record_Model::getAll($record);

		$qualifiedName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('CURRENCY_LIST', $currencyList);
		$viewer->assign('RECORD_MODEL', Settings_Currency_Record_Model::getInstance($record));
		$viewer->view('TransformEdit.tpl', $qualifiedName);
	}
}
