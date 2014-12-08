<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Currency_ListView_Model extends Settings_Vtiger_ListView_Model {
    
    public function getBasicListQuery() {
        $query = parent::getBasicListQuery();
        $query .= ' WHERE deleted=0 ';
        return $query;
    }
}