<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_Fields_Action extends Vtiger_Action_Controller
{

	function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new NoPermittedException('LBL_PERMISSION_DENIED');
		}
	}

	function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getOwners');
	}

	function process(Vtiger_Request $request)
	{
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function getOwners(Vtiger_Request $request)
	{
		$value = $request->get('value');
		$type = $request->get('type');
		$moduleName = $request->getModule();
		$response = new Vtiger_Response();
		if (empty($value)) {
			$response->setError('NO');
		} else {
			$owner = includes\fields\Owner::getInstance($moduleName);
			$owner->find($value);

			$data = [];
			$users = $owner->getAccessibleUsers('', 'owner');
			if (!empty($users)) {
				$data[] = ['name' => vtranslate('LBL_USERS'), 'type' => 'optgroup'];
				foreach ($users as $key => &$value) {
					if ($type == 'List') {
						$key = $value;
					}
					$data[] = ['id' => $key, 'name' => $value];
				}
			}
			$grup = $owner->getAccessibleGroups('', 'owner', true);
			if (!empty($grup)) {
				$data[] = ['name' => vtranslate('LBL_GROUPS'), 'type' => 'optgroup'];
				foreach ($grup as $key => &$value) {
					if ($type == 'List') {
						$key = $value;
					}
					$data[] = ['id' => $key, 'name' => $value];
				}
			}
			$response->setResult(['items' => $data]);
		}
		/*
		  $permitted = Users_Privileges_Model::isPermitted($sourceModule, 'DetailView', $record);
		  if ($permitted) {
		  vglobal('showsAdditionalLabels', true);
		  $recordModel = Vtiger_Record_Model::getInstanceById($record, $sourceModule);
		  $data = $recordModel->getData();
		  $response->setResult(array('success' => true, 'data' => array_map('decode_html', $data)));
		  } else {
		  $response->setResult(array('success' => false, 'message' => vtranslate('LBL_PERMISSION_DENIED')));
		  }
		 */
		$response->emit();
	}
}
