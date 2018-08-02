<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_ShowWidget_View extends Vtiger_IndexAjax_View
{
	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$moduleName = $request->getModule();
		$componentName = $request->getByType('name', 1);
		$linkId = $request->getInteger('linkid');
		$id = $request->get('widgetid');
		if (!empty($componentName)) {
			$className = Vtiger_Loader::getComponentClassName('Dashboard', $componentName, $moduleName);
			if (!empty($className)) {
				$widget = null;
				if (!empty($linkId)) {
					$widget = new Vtiger_Widget_Model();
					$widget->set('linkid', (int) $linkId);
					$widget->set('userid', $currentUser->getId());
					$widget->set('widgetid', (int) $id);
					$widget->set('active', $request->get('active'));
					$widget->set('filterid', $request->get('filterid', null));
					if ($request->has('data')) {
						$widget->set('data', $request->get('data'));
					}
					$widget->show();
				}
				$classInstance = new $className();
				$classInstance->process($request, $widget);

				return;
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(['success' => false, 'message' => \App\Language::translate('NO_DATA')]);
		$response->emit();
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
