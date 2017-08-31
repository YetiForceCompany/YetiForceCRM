<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

Class Settings_Groups_EditAjax_Action extends Settings_Vtiger_Basic_Action
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('checkDuplicate');
		$this->exposeMethod('updateColor');
		$this->exposeMethod('removeColor');
	}

	public function process(\App\Request $request)
	{
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function checkDuplicate(\App\Request $request)
	{
		$groupName = $request->get('groupname');
		$recordId = $request->get('record');

		$recordModel = Settings_Groups_Record_Model::getInstanceByName(App\Purifier::decodeHtml($groupName), array($recordId));

		$response = new Vtiger_Response();
		if (!empty($recordModel)) {
			$response->setResult(array('success' => true, 'message' => \App\Language::translate('LBL_DUPLICATES_EXIST', $request->getModule(false))));
		} else {
			$response->setResult(array('success' => false));
		}
		$response->emit();
	}

	public function updateColor(\App\Request $request)
	{
		$color = $request->get('color');
		if (!$color) {
			$color = \App\Colors::getRandomColor();
		}
		\App\Colors::updateGroupColor($request->getInteger('id'), $color);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => true,
			'color' => $color,
			'message' => \App\Language::translate('LBL_SAVE_COLOR', $request->getModule(false))
		]);
		$response->emit();
	}

	public function removeColor(\App\Request $request)
	{
		\App\Colors::updateGroupColor($request->getInteger('id'), '');
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'color' => $color,
			'message' => \App\Language::translate('LBL_REMOVED_COLOR', $request->getModule(false))
		));
		$response->emit();
	}
}
