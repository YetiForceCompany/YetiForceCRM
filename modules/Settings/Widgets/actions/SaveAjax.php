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

class Settings_Widgets_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('saveWidget');
		$this->exposeMethod('removeWidget');
		$this->exposeMethod('updateSequence');
	}

	public function saveWidget(Vtiger_Request $request)
	{
		$params = $request->get('params');
		Settings_Widgets_Module_Model::saveWidget($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => 1,
			'message' => vtranslate('Saved changes', $request->getModule(false))
		));
		$response->emit();
	}

	public function removeWidget(Vtiger_Request $request)
	{
		$params = $request->get('params');
		Settings_Widgets_Module_Model::removeWidget($params['wid']);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => 1,
			'message' => vtranslate('Removed widget', $request->getModule(false))
		));
		$response->emit();
	}

	public function updateSequence(Vtiger_Request $request)
	{
		$params = $request->get('params');
		Settings_Widgets_Module_Model::updateSequence($params);
		$response = new Vtiger_Response();
		$response->setResult(array(
			'success' => 1,
			'message' => vtranslate('Update has been completed', $request->getModule(false))
		));
		$response->emit();
	}
}
