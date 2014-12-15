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

class BackUp {

    /**
     * Invoked when special actions are performed on the module.
     * @param String Module name
     * @param String Event Type
     */
    function vtlib_handler($moduleName, $eventType) {
        global $adb;
        $registerLink = false;
        if ($eventType == 'module.postinstall') {
            $registerLink = true;
        } else if ($eventType == 'module.disabled') {
            // TODO Handle actions before this module is being uninstalled.
        } else if ($eventType == 'module.preuninstall') {
            // TODO Handle actions when this module is about to be deleted.
        } else if ($eventType == 'module.preupdate') {
            // TODO Handle actions before this module is updated.
        } else if ($eventType == 'module.postupdate') {
            // TODO Handle actions after this module is updated.
        }

        if ($registerLink) {
            $adb->pquery("INSERT  INTO `vtiger_settings_field`
                            (`fieldid`,`blockid`,`name`,`iconpath`,`description`,`linkto`,`sequence`,`active`,`pinned`) 
                            VALUES ((SELECT id FROM `vtiger_settings_field_seq` LIMIT 1)+1,7,'Backup','','LBL_BACKUP_DESCRIPTION','index.php?parent=Settings&module=BackUp&view=Index',20,0,0)", array());
            $fieldSeq = $adb->query('SELECT id FROM `vtiger_settings_field_seq` ');
            $fieldSeqAmount = (int) ($fieldSeq->fields[0]);
            $fieldSeqAmount++;
            $adb->pquery("UPDATE `vtiger_settings_field_seq` SET id = ? ", array($fieldSeqAmount));

        
        }
    }

}
