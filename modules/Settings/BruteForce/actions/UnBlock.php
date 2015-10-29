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

class Settings_BruteForce_Unblock_Action extends Settings_Vtiger_Basic_Action
{

	public function process(Vtiger_Request $request)
	{
		$db = PearDatabase::getInstance();
		$query = "SELECT timelock FROM `vtiger_bruteforce` LIMIT 1";
		$result = $db->pquery($query, array());
		$blockTime = $id = $db->getSingleValue($result);
		$now = date("Y-m-d H:i:s");
		$ip = $request->get('ip');

		$sql = "UPDATE vtiger_loginhistory SET unblock = 1 "
			. "WHERE user_ip = ? && "
			. "(UNIX_TIMESTAMP(login_time) - UNIX_TIMESTAMP(ADDDATE(?, INTERVAL -$blockTime MINUTE))) > 0;";
		$params = [$ip, $now];
		$result = $db->pquery($sql, $params, true);
		$moduleName = $request->getModule();

		if ($db->getAffectedRowCount($result) == 0) {
			$return = array('success' => false, 'message' => vtranslate('LBL_UNBLOCK_FAIL', $moduleName));
		} else {
			$return = array('success' => true, 'message' => vtranslate('LBL_UNBLOCK_SUCCESS', $moduleName));
		}
		$response = new Vtiger_Response();
		$response->setResult($return);
		$response->emit();
	}
}
