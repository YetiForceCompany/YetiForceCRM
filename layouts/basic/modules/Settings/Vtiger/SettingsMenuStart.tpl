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
{include file=\App\Layout::getTemplatePath('Header.tpl', $MODULE_NAME)}
<div class="bodyContents">
	{if $USER_MODEL->isAdminUser() && !\App\YetiForce\Register::verify(true)}
		<div class="bg-danger text-white u-font-weight-700 w-100 pb-1 pt-1 text-center">
			<span class="fas fa-exclamation-triangle mr-2"
				  title="{\App\Language::translate('LBL_YETIFORCE_REGISTRATION', $MODULE_NAME)}"></span>
			{\App\Language::translate('LBL_YETIFORCE_REGISTRATION_ERROR',$MODULE_NAME)}
		</div>
	{/if}
	<div class="mainContainer">
		<div class="contentsDiv">
			{/strip}
