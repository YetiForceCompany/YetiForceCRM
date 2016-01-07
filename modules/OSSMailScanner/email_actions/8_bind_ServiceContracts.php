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

function _8_bind_ServiceContracts($user_id, $mail_detail, $folder, $return)
{
	$adb = PearDatabase::getInstance();
	$ModuleName = 'ServiceContracts';
	$result_ossmailview = $adb->pquery("SELECT ossmailviewid FROM vtiger_ossmailview where uid = ? ", array($mail_detail['message_id']), true);
	if ($adb->num_rows($result_ossmailview) == 0) {
		return false;
	}
	$ossmailviewid = $adb->query_result($result_ossmailview, 0, 'ossmailviewid');
	$SC_list = [];
	foreach ($return as $row) {
		if (count($row['bind_Accounts']) > 0) {
			foreach ($row['bind_Accounts'] as $row) {
				$result = $adb->pquery("SELECT servicecontractsid FROM vtiger_servicecontracts inner join vtiger_crmentity on vtiger_crmentity.crmid=
				vtiger_servicecontracts.servicecontractsid where vtiger_crmentity.deleted = 0 AND sc_related_to = ? AND contract_status = 'In Progress'; ", array($row), true);
				for ($i = 0; $i < $adb->num_rows($result); $i++) {
					$SC_list[] = Array('scid' => $adb->query_result($result, $i, 'servicecontractsid'), 'acid' => $row);
					$ids[] = $adb->query_result($result, $i, 'servicecontractsid');
				}
			}
		}
	}
	if (count($SC_list) == 1) {
		$result_exist = $adb->pquery("SELECT * FROM vtiger_crmentityrel WHERE (crmid = ? AND relmodule = 'Accounts') OR ( relcrmid = ? AND module = 'Accounts' )", array($SC_list[0]['scid'], $SC_list[0]['scid']), true);
		if ($adb->num_rows($result_exist) > 0) {
			return false;
		}
		$adb->pquery("INSERT INTO vtiger_crmentityrel SET crmid=?, module=?, relcrmid=?, relmodule=?", Array($ossmailviewid, 'OSSMailView', $SC_list[0]['scid'], 'ServiceContracts'));
	}
	return Array('bind_ServiceContracts' => $ids);
}
