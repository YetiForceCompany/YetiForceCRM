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

class Settings_OSSProjectTemplates_GetTplInfo_Action extends Settings_Vtiger_Index_Action
{

	public function process(Vtiger_Request $request)
	{
		$baseModuleName = $request->get('base_module');
		$db = PearDatabase::getInstance();
		$id = $request->get('tpl_id');
		$sql = sprintf("SELECT * FROM vtiger_oss_%s_templates WHERE id_tpl = ?", strtolower($baseModuleName));
		$result = $db->pquery($sql, [$id]);
		$output = [];
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$record = $db->raw_query_result_rowdata($result, $i);
			$key = $record['fld_name'];
			$output[$key] = str_replace('&oacute;', 'ó', $record['fld_val']);
		}
		$response = new Vtiger_Response();
		$response->setResult($output);
		$response->emit();
	}
}
