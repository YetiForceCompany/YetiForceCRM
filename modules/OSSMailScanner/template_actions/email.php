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

/**
 * Mail Scanner bind email action 
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
function bind_email($user_id, $mailDetail, $folder, $moduleName, $ossmailviewTab)
{
	$adb = PearDatabase::getInstance();

	if ($mailDetail['ossmailviewid'] == '') {
		$result_ossmailview = $adb->pquery('SELECT ossmailviewid FROM vtiger_ossmailview WHERE uid = ? AND rc_user = ?', [$mailDetail['message_id'], $user_id]);
		if ($adb->num_rows($result_ossmailview) == 0) {
			return FALSE;
		}
		$mailViewId = $adb->query_result($result_ossmailview, 0, 'ossmailviewid');
	} else {
		$mailViewId = $mailDetail['ossmailviewid'];
	}

	$crmIds = OSSMailScanner_Record_Model::findEmail($mailDetail['fromaddress'], $moduleName, TRUE);
	$crmidsToaddress = OSSMailScanner_Record_Model::findEmail($mailDetail['toaddress'], $moduleName, TRUE);
	$crmidsCcaddress = OSSMailScanner_Record_Model::findEmail($mailDetail['ccaddress'], $moduleName, TRUE);
	$crmidsBccaddress = OSSMailScanner_Record_Model::findEmail($mailDetail['bccaddress'], $moduleName, TRUE);
	$crmidsReplyToaddress = OSSMailScanner_Record_Model::findEmail($mailDetail['reply_toaddress'], $moduleName, TRUE);
	$crmIds = OSSMailScanner_Record_Model::_merge_array($crmIds, $crmidsToaddress);
	$crmIds = OSSMailScanner_Record_Model::_merge_array($crmIds, $crmidsCcaddress);
	$crmIds = OSSMailScanner_Record_Model::_merge_array($crmIds, $crmidsBccaddress);
	$crmIds = OSSMailScanner_Record_Model::_merge_array($crmIds, $crmidsReplyToaddress);
	$returnIds = [];
	
	if (count($crmIds) != 0) {
		foreach ($crmIds as $crmRow) {
			$resultRelation = $adb->pquery('SELECT * FROM vtiger_ossmailview_relation WHERE ossmailviewid=? AND crmid=?', [$mailViewId, $crmRow]);
			if ($resultRelation->rowCount() > 0) {
				continue;
			}

			$adb->pquery('INSERT INTO vtiger_ossmailview_relation SET ossmailviewid=?, crmid=?, date=?;', [$mailViewId, $crmRow, $mailDetail['udate_formated']]);
			$returnIds[] = $crmRow;
		}
	}
	return $returnIds;
}
