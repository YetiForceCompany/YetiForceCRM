<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_Search_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('Save');
		$this->exposeMethod('UpdateLabels');
		$this->exposeMethod('SaveSequenceNumber');
	}

	public function Save(Vtiger_Request $request)
	{
		$params = $request->get('params');
		Settings_Search_Module_Model::save($params);
		$message = 'LBL_SAVE_CHANGES_LABLE';
		if ($params['name'] == 'turn_off')
			$message = 'LBL_SAVE_CHANGES_SEARCHING';
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $saveResp['success'],
			'message' => vtranslate($message, $request->getModule(false))
		));
		$response->emit();
	}

	public function UpdateLabels(Vtiger_Request $request)
	{
		$params = $request->get('params');
		Settings_Search_Module_Model::updateLabels($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $saveResp['success'],
			'message' => vtranslate('Update has been completed', $request->getModule(false))
		));
		$response->emit();
	}

	public function SaveSequenceNumber(Vtiger_Request $request)
	{
		$updatedFieldsList = $request->get('updatedFields');

		//This will update the modules sequence 
		Settings_Search_Module_Model::updateSequenceNumber($updatedFieldsList);

		$response = new Vtiger_Response();
		$response->setResult(array('success' => true));
		$response->emit();
	}
}
