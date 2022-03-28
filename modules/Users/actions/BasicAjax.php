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

class Users_BasicAjax_Action extends Vtiger_BasicAjax_Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!$currentUser->isAdminUser()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$result = [];
		$moduleName = $request->getByType('search_module', \App\Purifier::ALNUM);
		$limit = \App\Config::search('GLOBAL_SEARCH_AUTOCOMPLETE_LIMIT');
		$searchValue = \App\RecordSearch::getSearchField()->getUITypeModel()->getDbConditionBuilderValue($request->getByType('search_value', \App\Purifier::TEXT), '');
		$srcRecord = $request->has('src_record') ? $request->getInteger('src_record') : null;

		$moduleModel = \Vtiger_Module_Model::getInstance($moduleName);
		$dataReader = $moduleModel->getQueryForRecords($searchValue, $limit, $srcRecord)->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$result[] = [
				'label' => App\Purifier::decodeHtml($row['search_label']),
				'value' => App\Purifier::decodeHtml($row['search_label']),
				'id' => $row['id'],
			];
		}
		$response = new \Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
