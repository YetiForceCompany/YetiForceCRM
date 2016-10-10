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

class Settings_DataAccess_SaveActionConfig_Action extends Settings_Vtiger_Index_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		return;
	}

	public function process(Vtiger_Request $request)
	{
		$tpl_id = $request->get('tpl_id');
		$base_module = $request->get('base_module');
		Settings_DataAccess_Module_Model::saveActionConfig($tpl_id, $request->get('an'), $request->get('data'), $request->get('aid'));
		header("Location: index.php?module=DataAccess&parent=Settings&view=Step3&tpl_id=$tpl_id&base_module=$base_module&s=false");
	}
}
