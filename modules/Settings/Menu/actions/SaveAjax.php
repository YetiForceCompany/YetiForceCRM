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

class Settings_Menu_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('createMenu');
		$this->exposeMethod('updateMenu');
		$this->exposeMethod('removeMenu');
		$this->exposeMethod('updateSequence');
		$this->exposeMethod('copyMenu');
	}

	public function createMenu(Vtiger_Request $request)
	{
		$data = $request->get('mdata');
		$recordModel = Settings_Menu_Record_Model::getCleanInstance();
		$recordModel->initialize($data);
		$recordModel->save();
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'message' => vtranslate('LBL_ITEM_ADDED_TO_MENU', $request->getModule(false))
		));
		$response->emit();
	}

	public function updateMenu(Vtiger_Request $request)
	{
		$data = $request->get('mdata');
		$recordModel = Settings_Menu_Record_Model::getInstanceById($data['id']);
		$recordModel->initialize($data);
		$recordModel->set('edit', true);
		$recordModel->save($data);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'message' => vtranslate('LBL_SAVED_MENU', $request->getModule(false))
		));
		$response->emit();
	}

	public function removeMenu(Vtiger_Request $request)
	{
		$data = $request->get('mdata');
		$settingsModel = Settings_Menu_Record_Model::getCleanInstance();
		$settingsModel->removeMenu($data);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'message' => vtranslate('LBL_REMOVED_MENU_ITEM', $request->getModule(false))
		));
		$response->emit();
	}

	public function updateSequence(Vtiger_Request $request)
	{
		$data = $request->get('mdata');
		$recordModel = Settings_Menu_Record_Model::getCleanInstance();
		$recordModel->saveSequence($data, true);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'message' => vtranslate('LBL_SAVED_MAP_MENU', $request->getModule(false))
		));
		$response->emit();
	}
	
	/**
	 * Function to trigger copying menu
	 * @param Vtiger_Request $request
	 */
	public function copyMenu(Vtiger_Request $request)
	{
		$fromRole = filter_var($request->get('fromRole'), FILTER_SANITIZE_NUMBER_INT);
		$toRole = filter_var($request->get('toRole'), FILTER_SANITIZE_NUMBER_INT);
		$recordModel = Settings_Menu_Record_Model::getCleanInstance();
		$recordModel->copyMenu($fromRole, $toRole);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'message' => vtranslate('LBL_SAVED_MAP_MENU', $request->getModule(false))
		));

		$response->emit();
	}
}
