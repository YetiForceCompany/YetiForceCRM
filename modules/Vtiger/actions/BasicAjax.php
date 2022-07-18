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

class Vtiger_BasicAjax_Action extends \App\Controller\Action
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->hasModulePermission($request->getByType('search_module', 'Alnum')) || !$currentUserPrivilegesModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param App\Request $request
	 */
	public function process(App\Request $request)
	{
		$result = [];
		if (!$request->isEmpty('search_value')) {
			$searchValue = \App\RecordSearch::getSearchField()->getUITypeModel()->getDbConditionBuilderValue($request->getByType('search_value', \App\Purifier::TEXT), '');
			$srcRecord = $request->has('src_record') ? $request->getInteger('src_record') : null;
			$dataReader = \Vtiger_Module_Model::getInstance($request->getByType('search_module', \App\Purifier::ALNUM))
				->getQueryForRecords($searchValue, \App\Config::search('GLOBAL_SEARCH_AUTOCOMPLETE_LIMIT'), $srcRecord)
				->createQuery()->createCommand()->query();
			while ($row = $dataReader->read()) {
				$result[] = [
					'label' => App\Purifier::decodeHtml($row['search_label']),
					'value' => App\Purifier::decodeHtml(\App\Record::getLabel($row['id'])),
					'id' => $row['id'],
				];
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
