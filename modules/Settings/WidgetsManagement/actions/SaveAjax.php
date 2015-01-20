<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class Settings_WidgetsManagement_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
	}
	public function save(Vtiger_Request $request) {
		$data = $request->get('form');
		$moduleName = $request->get('sourceModule');
		if(!is_array($data) || !$data){
			$result = array('success'=>false,'message'=>vtranslate('LBL_INVALID_DATA',$moduleName));
		}else{
			if(!$data['action'])
				$data['action'] = 'saveDetails';
			$result = Settings_WidgetsManagement_Module_Model::$data['action']($data, $moduleName);
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
	public function delete(Vtiger_Request $request) {
		$data = $request->get('form');
		$moduleName = $request->get('sourceModule');
		if(!is_array($data) || !$data){
			$result = array('success'=>false,'message'=>vtranslate('LBL_INVALID_DATA',$moduleName));
		}else{
			if(!$data['action'])
				$data['action'] = 'removeWidget';
			$result = Settings_WidgetsManagement_Module_Model::$data['action']($data);
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}