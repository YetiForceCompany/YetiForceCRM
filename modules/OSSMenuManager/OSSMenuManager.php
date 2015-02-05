<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 *************************************************************************************************************************************/
require_once('include/CRMEntity.php');
require_once('include/Tracker.php');

class OSSMenuManager extends CRMEntity {
    var $db, $log; // Used in class functions of CRMEntity
    var $column_fields = Array();
    
    /**	Constructor which will set the column_fields in this object
	 */
	function __construct() {
		global $log;
		$this->db = PearDatabase::getInstance();
		$this->log = $log;
	}
    
    function vtlib_handler($moduleName, $event_type) {
        //require_once('modules/OSSMenuManager/utils.php');
        
		global $adb;
		if($event_type == 'module.postinstall') {
		
			$blockid = $adb->query_result( 
				$adb->pquery("SELECT blockid FROM vtiger_settings_blocks WHERE label='LBL_STUDIO'",array()),
				0, 'blockid');
			$sequence = (int)$adb->query_result(
				$adb->pquery("SELECT max(sequence) as sequence FROM vtiger_settings_field WHERE blockid=?",array($blockid), true),
				0, 'sequence') + 1;
			$fieldid = $adb->getUniqueId('vtiger_settings_field');
			$adb->pquery("INSERT INTO vtiger_settings_field (fieldid,blockid,sequence,name,iconpath,description,linkto)
				VALUES (?,?,?,?,?,?,?)", array($fieldid, $blockid,$sequence,'Menu Manager','menueditor.png','LBL_MENU_DESC', 'index.php?module=OSSMenuManager&view=Configuration&parent=Settings'), true);
            
            $adb->query("UPDATE vtiger_tab SET customized = 0 WHERE name='$moduleName'");

		} else if($event_type == 'module.disabled') {
		} else if($event_type == 'module.enabled') {
		} else if($event_type == 'module.preuninstall') {  
            // TODO Handle actions when this module is about to be deleted.
		} else if($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($event_type == 'module.postupdate') {

		}
	}
}