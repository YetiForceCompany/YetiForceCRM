<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_LangManagement_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('addTranslation');
		$this->exposeMethod('saveTranslation');
		$this->exposeMethod('deleteTranslation');
		$this->exposeMethod('add');
		$this->exposeMethod('save');
		$this->exposeMethod('saveView');
		$this->exposeMethod('delete');
		$this->exposeMethod('setAsDefault');
	}

	public function addTranslation(\App\Request $request)
	{
		$params = $request->get('params');
		$form_data = $params['form_data'];
		$langs = json_decode($form_data['langs'], true);
		$params['type'] = $form_data['type'];
		$params['langkey'] = $form_data['variable'];
		foreach ($langs as $lang) {
			$params['lang'] = $lang;
			$params['val'] = $form_data[$lang];
			$saveResp = Settings_LangManagement_Module_Model::addTranslation($params);
			if ($saveResp['success'] === false) {
				break;
			}
		}
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $saveResp['success'],
			'message' => vtranslate($saveResp['data'], $request->getModule(false))
		));
		$response->emit();
	}

	/**
	 * Save translations
	 * @param \App\Request $request
	 */
	public function saveTranslation(\App\Request $request)
	{
		$params = $request->get('params');
		$saveResp = Settings_LangManagement_Module_Model::saveTranslation($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $saveResp['success'],
			'message' => vtranslate($saveResp['data'], $request->getModule(false))
		]);
		$response->emit();
	}

	public function saveView(\App\Request $request)
	{
		$params = $request->get('params');
		$saveResp = Settings_LangManagement_Module_Model::saveView($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $saveResp['success'],
			'message' => vtranslate($saveResp['data'], $request->getModule(false))
		));
		$response->emit();
	}

	/**
	 * Remove translation
	 * @param \App\Request $request
	 */
	public function deleteTranslation(\App\Request $request)
	{
		$params = $request->get('params');
		$saveResp = Settings_LangManagement_Module_Model::deleteTranslation($params);
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $saveResp['success'],
			'message' => vtranslate($saveResp['data'], $request->getModule(false))
		]);
		$response->emit();
	}

	public function add(\App\Request $request)
	{
		$params = $request->get('params');
		$saveResp = Settings_LangManagement_Module_Model::add($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => $saveResp['success'],
			'message' => vtranslate($saveResp['data'], $request->getModule(false))
		));
		$response->emit();
	}

	public function save(\App\Request $request)
	{
		$params = $request->get('params');
		$saveResp = Settings_LangManagement_Module_Model::save($params);
		$response = new Vtiger_Response();
		if ($saveResp) {
			$response->setResult(array('success' => true, 'message' => vtranslate('LBL_SaveDataOK', $request->getModule(false))));
		} else {
			$response->setResult(array('success' => false));
		}
		$response->emit();
	}

	public function delete(\App\Request $request)
	{
		$params = $request->get('params');
		$saveResp = Settings_LangManagement_Module_Model::delete($params);
		$response = new Vtiger_Response();
		if ($saveResp) {
			$response->setResult(['success' => true, 'message' => vtranslate('LBL_DeleteDataOK', $request->getModule(false))]);
		} else {
			$response->setResult(['success' => false]);
		}
		$response->emit();
	}

	public function setAsDefault(\App\Request $request)
	{
		$params = $request->get('params');
		$saveResp = Settings_LangManagement_Module_Model::setAsDefault($params);
		$response = new Vtiger_Response();
		if ($saveResp['success']) {
			$response->setResult(array('success' => true, 'message' => vtranslate('LBL_SaveDataOK', $request->getModule(false)), 'prefixOld' => $saveResp['prefixOld']));
		} else {
			$response->setResult(array('success' => false));
		}
		$response->emit();
	}
}
