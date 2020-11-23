<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Campaigns_RelatedList_View extends Vtiger_RelatedList_View
{
	public function process(App\Request $request)
	{
		$relatedModuleName = $request->getByType('relatedModule', 2);
		$viewer = $this->getViewer($request);
		if (\in_array($relatedModuleName, ['Accounts', 'Leads', 'Vendors', 'Contacts', 'Partners', 'Competition'])) {
			$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($relatedModuleName));
			$viewer->assign('SELECTED_IDS', 'all' === $request->getRaw('selectedIds') ? 'all' : $request->getArray('selectedIds', 'Integer'));
			$viewer->assign('EXCLUDED_IDS', $request->getArray('excludedIds', 'Integer'));
		}
		return parent::process($request);
	}
}
