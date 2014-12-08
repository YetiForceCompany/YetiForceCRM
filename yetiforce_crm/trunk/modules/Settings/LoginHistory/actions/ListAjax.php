<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_LoginHistory_ListAjax_Action extends Settings_Vtiger_ListAjax_Action{
	
	
	public function getListViewCount(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);

		$listViewModel = Settings_Vtiger_ListView_Model::getInstance($qualifiedModuleName);
		
		$searchField = $request->get('search_key');
		$value = $request->get('search_value');
		
		if(!empty($searchField) && !empty($value)) {
			$listViewModel->set('search_key', $searchField);
			$listViewModel->set('search_value', $value);
		}

		return $listViewModel->getListViewCount();
    }
}