<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************************************************************** */
require_once 'include/events/VTEntityDelta.php';
require_once( 'modules/Users/Users.php' );
require_once( 'modules/Users/models/Record.php' );

class SECURE extends VTEventHandler
{

	public function handleEvent($eventName, $entityData)
	{
		global $log, $adb, $current;
		$moduleName = $entityData->getModuleName();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		if ($moduleName == 'OSSPasswords') {
			if ($eventName == 'vtiger.entity.aftersave.final') {
				$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
				$conf = $recordModel->getConfiguration();

				$sql = "SELECT basic.id FROM `vtiger_modtracker_basic` basic LEFT JOIN `vtiger_modtracker_detail` detail ON detail.`id` = basic.`id` 
                    WHERE basic.module = 'OSSPasswords' && basic.`whodid` = '{$currentUserModel->id}'  
                    && basic.changedon > CURDATE() && detail.fieldname = 'password' ORDER BY basic.id DESC LIMIT 1;";
				$result = $adb->query($sql, true);

				$num = $adb->num_rows($result);
				if ($num > 0) {
					$toUpdate = [];
					for ($i = 0; $i < $num; $i++)
						$toUpdate[] = (int) $adb->query_result($result, $i, 'id');
					// register changes: show prevalue, hide postvalue
					$where = sprintf("`id` IN (%s) && `fieldname` = 'password'", generateQuestionMarks($toUpdate));
					if ($conf['register_changes'] == 1)
						$adb->update('vtiger_modtracker_detail', ['postvalue' => '**********'], $where, [implode(',', $toUpdate)]);
					else
						$adb->update('vtiger_modtracker_detail', ['postvalue' => '**********', 'prevalue' => '**********'], $where, [implode(',', $toUpdate)]);
				}
			}
		}
	}
}
