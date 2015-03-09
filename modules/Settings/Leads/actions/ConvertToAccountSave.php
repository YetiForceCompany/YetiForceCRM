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
class Settings_Leads_ConvertToAccountSave_Action extends Settings_Vtiger_Index_Action {

	public function __construct() {
		$this->exposeMethod('save');
	}

	/**
	 * Save date
	 * @param <String> state
	 * @return true if saved, false otherwise
	 */
	public function save(Vtiger_Request $request) {

		global $log;
		$result = Settings_Leads_ConvertToAccount_Model::save($request->get('state'));
		$log->debug('Settings_Leads_ConvertToAccountSave_Action::save - started');
		if($result)
			$result = array('success' => TRUE);
		else
			$result = array('success' => FALSE);

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

}