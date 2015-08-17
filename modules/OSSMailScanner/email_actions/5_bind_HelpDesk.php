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

function _5_bind_HelpDesk($user_id, $mail_detail, $folder, $return)
{
	$ModuleName = 'HelpDesk';
	$table_name = 'vtiger_troubletickets';
	$table_col = 'ticket_no';
	$ossmailviewTab = 'vtiger_ossmailview_tickets';
	$answered_status = 'Answered';

	require_once("modules/OSSMailScanner/template_actions/prefix.php");
	$ids = bind_prefix($user_id, $mail_detail, $folder, $ModuleName, $table_name, $table_col, $ossmailviewTab);
	if ($ids) {
		$conf = OSSMailScanner_Record_Model::getConfig('emailsearch');
		$type = OSSMailScanner_Record_Model::getTypeEmail($mail_detail);
		if ($conf['change_ticket_status'] == 'true' && $type == 1) {
			foreach ($ids as $id) {
				$ModelInstance = Vtiger_Record_Model::getInstanceById($id, $ModuleName);
				$ticketstatus = $ModelInstance->get('ticketstatus');
				if ($ticketstatus == 'Wait For Response') {
					$record = new $ModuleName();
					$record->retrieve_entity_info($id, $ModuleName);
					$record->mode = 'edit';
					$record->column_fields['ticketstatus'] = $answered_status;
					$record->save($ModuleName, $id);
				}
			}
		}
	}
	return Array('bind_HelpDesk' => $ids);
}
