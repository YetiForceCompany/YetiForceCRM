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

class Contacts_Edit_View extends Vtiger_Edit_View
{
	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		if ($this->record->isNew()) {
			return parent::getPageTitle($request);
		}
		$moduleName = $request->getModule();
		return \App\Language::translate($moduleName, $moduleName) . ' ' .
				\App\Language::translate('LBL_EDIT') . ' ' . $this->record->getDisplayName();
	}
}
