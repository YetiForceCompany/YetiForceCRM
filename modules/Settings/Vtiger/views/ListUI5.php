<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Vtiger_ListUI5_View extends Settings_Vtiger_UI5Embed_View {

	protected function getUI5EmbedURL(Vtiger_Request $request) {
        $module = $request->getModule();
        if($module == 'EmailTemplate') {
            return 'index.php?module=Settings&action=listemailtemplates&parenttab=Settings';
        }
	}

}
