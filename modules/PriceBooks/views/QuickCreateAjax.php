<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PriceBooks_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('IS_RELATION', $request->getBoolean('relationOperation'));
		parent::process($request);
	}
}
