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

class Settings_DataAccess_DeleteAction_Action extends Settings_Vtiger_Index_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		return;
	}

	public function process(Vtiger_Request $request)
	{
		$id = $request->get('id');
		$aid = $request->get('a');
		$baseModule = $request->get('m');
		Settings_DataAccess_Module_Model::deleteAction($id, $aid);
		header("Location: index.php?module=DataAccess&parent=Settings&view=Step3&tpl_id=$id&base_module=$baseModule&s=false");
	}
}
