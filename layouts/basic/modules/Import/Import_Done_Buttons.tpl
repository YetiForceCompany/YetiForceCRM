{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Import-Import_Done_Buttons -->
	<button class="btn btn-success btn-sm" name="ok"
			onclick="location.href = 'index.php?module={$FOR_MODULE}&view=Import'">
		<span class="font-weight-bold">{\App\Language::translate('LBL_OK_BUTTON_LABEL', $MODULE_NAME)}</span>
	</button>
	<!-- /tpl-Import-Import_Done_Buttons -->
{/strip}