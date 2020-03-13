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
{include file=\App\Layout::getTemplatePath('PageHeader.tpl', $MODULE_NAME)}
<div class="bodyContents">
	{if $USER_MODEL->isAdminUser() && !\App\YetiForce\Register::verify(true)}
		<div class="o-register-error bg-danger text-white u-font-weight-700 w-100 pb-1 pt-1 justify-content-center d-flex js-popover-tooltip--ellipsis-icon"
				data-content="{\App\Language::translateArgs('LBL_YETIFORCE_REGISTRATION_ERROR',$MODULE_NAME,"<a href='index.php?module=Companies&parent=Settings&view=List&displayModal=online'>{\App\Language::translate('LBL_YETIFORCE_REGISTRATION_CHECK_STATUS', $MODULE_NAME)}</a>")}"
				data-toggle="popover"
				data-js="popover | mouseenter">
				<div class="text-truncate px-1">
					<span class="fas fa-exclamation-triangle mr-2" title="{\App\Language::translate('LBL_YETIFORCE_REGISTRATION', $MODULE_NAME)}"></span>
					{\App\Language::translateArgs('LBL_YETIFORCE_REGISTRATION_ERROR',$MODULE_NAME,"<a href=\"index.php?module=Companies&parent=Settings&view=List&displayModal=online\">{\App\Language::translate('LBL_YETIFORCE_REGISTRATION_CHECK_STATUS', $MODULE_NAME)}</a>")}
				</div>
				<span class="js-popover-icon d-none mr-1" data-js="class: d-none">
					<span class="fas fa-info-circle fa-sm"></span>
				</span>
		</div>
	{/if}
	{if $USER_MODEL->isAdminUser() && (!\App\YetiForce\Register::verify(true) || !\App\SystemWarnings\YetiForce\Newsletter::emailProvided())}
		<div class="o-register-warning bg-warning text-white u-font-weight-700 w-100 pb-1 pt-1 justify-content-center d-flex js-popover-tooltip--ellipsis-icon"
				data-content="{\App\Language::translateArgs('LBL_YETIFORCE_NEWSLETTER_ERROR',$MODULE_NAME,"<a href='index.php?module=Companies&parent=Settings&view=List&displayModal=online'>{\App\Language::translate('LBL_YETIFORCE_NEWSLETTER_FILL_DATA', $MODULE_NAME)}</a>")}"
				data-toggle="popover"
				data-js="popover | mouseenter">
			<div class="text-truncate px-1">
				<span class="fas fa-exclamation-triangle mr-2" title="{\App\Language::translate('LBL_YETIFORCE_NEWSLETTER', $MODULE_NAME)}"></span>
				{\App\Language::translateArgs('LBL_YETIFORCE_NEWSLETTER_ERROR',$MODULE_NAME,"<a href=\"index.php?module=Companies&parent=Settings&view=List&displayModal=online\">{\App\Language::translate('LBL_YETIFORCE_NEWSLETTER_FILL_DATA', $MODULE_NAME)}</a>")}
			</div>
			<span class="js-popover-icon d-none mr-1" data-js="class: d-none">
				<span class="fas fa-info-circle fa-sm"></span>
			</span>
		</div>
	{/if}
	<div class="mainContainer">
		<div class="contentsDiv">
			{/strip}
