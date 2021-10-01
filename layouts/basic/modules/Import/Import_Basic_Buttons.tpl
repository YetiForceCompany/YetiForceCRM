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
	<!-- tpl-Import-Import_Basic_Buttons -->
	<button type="submit" name="next"  class="btn btn-success" onclick="return ImportJs.uploadAndParse();">
		<span class="fas fa-angle-double-right mr-2"></span>
		<strong>{\App\Language::translate('LBL_NEXT_BUTTON_LABEL', $MODULE)}</strong>
	</button>
	&nbsp;&nbsp;
	<button class="btn btn-danger" type="reset" onclick="location.href = '{$MODULE_MODEL->getUrl()}'" >
		<span class="fas fa-times mr-2"></span>
		{\App\Language::translate('LBL_CANCEL', $MODULE)}
	</button>
	<!-- /tpl-Import-Import_Basic_Buttons -->
{/strip}
