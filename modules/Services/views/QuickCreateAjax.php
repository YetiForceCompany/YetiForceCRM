<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Services_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge($this->checkAndConvertJsScripts(['modules.Products.resources.Edit']), parent::getFooterScripts($request));
	}
}
