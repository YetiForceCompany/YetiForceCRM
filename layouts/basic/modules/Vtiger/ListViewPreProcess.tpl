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
<!-- tpl-Base-ListViewPreProcess -->
{include file=\App\Layout::getTemplatePath('PageHeader.tpl', $MODULE_NAME)}
<div class="bodyContents">
	<div class="mainContainer pt-md-0 pt-1">
	{assign var="BREADCRUMBS_ACTIVE" value=\App\Config::layout('breadcrumbs')}
		{if $BREADCRUMBS_ACTIVE || $HEADER_LINKS['LIST_VIEW_HEADER']}
		<div class="o-breadcrumb widget_header mb-2 d-flex justify-content-between px-2" data-js="container">
			<div class="o-breadcrumb__container">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
			<div class="my-auto o-header-toggle__actions js-header-toggle__actions" id="o-view-actions__container">
				<div class="btn-toolbar btn-group flex-md-nowrap">
					{foreach item=LINK from=$HEADER_LINKS['LIST_VIEW_HEADER']}
						{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='listViewHeader' BREAKPOINT='md' CLASS='c-btn-link--responsive'}
					{/foreach}
				</div>
			</div>
		</div>
		{/if}
		<div class="contentsDiv{if !$BREADCRUMBS_ACTIVE || !$HEADER_LINKS['LIST_VIEW_HEADER']} pt-2{/if}">
			<a class="btn btn-outline-dark d-md-none o-header-toggle__actions-btn js-header-toggle__actions-btn mb-1" href="#" data-js="click" role="button"
			   aria-expanded="false" aria-controls="o-view-actions__container">
				 <span class="fas fa-ellipsis-h fa-fw" title="{\App\Language::translate('LBL_ACTION_MENU')}"></span>
			</a>
			{include file=\App\Layout::getTemplatePath('ListViewHeader.tpl', $MODULE)}
			<!-- /tpl-Base-ListViewPreProcess -->
			{/strip}
