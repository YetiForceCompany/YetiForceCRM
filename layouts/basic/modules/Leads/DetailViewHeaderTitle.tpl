{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<input type="hidden" id="conversion_available_status" value="{if !empty($CONVERSION_AVAILABLE_STATUS)}{\App\Purifier::encodeHtml($CONVERSION_AVAILABLE_STATUS)}{/if}" />
	{include file=\App\Layout::getTemplatePath('DetailViewHeaderTitle.tpl', 'Vtiger')}
{/strip}
