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
class Settings_MenuEditor_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('UpdateColor');
	}
	public function UpdateColor(Vtiger_Request $request) {
		$params = $request->get('params');
		Settings_MenuEditor_Module_Model::updateColor($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => true,
			'message' => vtranslate('LBL_SAVE_COLOR',$request->getModule(false))
		));
		$response->emit();
	}
}