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
	<!-- tpl-Base-DetailViewHeader -->
	{assign var="MODULE_NAME" value=$MODULE_MODEL->get('name')}
	{assign var="BREADCRUMBS_ACTIVE" value=App\Config::layout('breadcrumbs')}
	<input id="recordId" type="hidden" value="{$RECORD->getId()}" />
	<div class="detailViewContainer">
		<div class="row detailViewTitle {if $BREADCRUMBS_ACTIVE}p-md-0 pt-1{else}pt-3{/if}">
			{if $SHOW_BREAD_CRUMBS && $BREADCRUMBS_ACTIVE}
				<div class="o-breadcrumb widget_header mb-2 d-flex justify-content-between px-3 px-sm-2 w-100">
					<div class="o-breadcrumb__container">
						{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
					</div>
				</div>
			{/if}
			{if !empty($DETAILVIEW_LINKS['DETAIL_VIEW_HEADER_WIDGET'])}
				{foreach item=WIDGET from=$DETAILVIEW_LINKS['DETAIL_VIEW_HEADER_WIDGET']}
					<div class="col-md-12 px-0">
						{Vtiger_Widget_Model::processWidget($WIDGET, $RECORD)}
					</div>
				{/foreach}
			{/if}
			{include file=\App\Layout::getTemplatePath('DetailViewHeaderTitle.tpl', $MODULE)}
		</div>
		<div class="detailViewInfo row">
			{include file=\App\Layout::getTemplatePath('RelatedListButtons.tpl', $MODULE)}
			<div class="col-md-12 pt-1 {if !empty($DETAILVIEW_LINKS['DETAILVIEWTAB']) || !empty($DETAILVIEW_LINKS['DETAILVIEWRELATED']) } details {/if}">
				<form id="detailView" data-name-fields="{\App\Purifier::encodeHtml(\App\Json::encode($MODULE_MODEL->getNameFields()))}" method="POST">
					<input type="hidden" id="preSaveValidation" value="{!empty(\App\EventHandler::getByType(\App\EventHandler::EDIT_VIEW_PRE_SAVE, $MODULE_NAME))}" />
					{if $RECORD->getId() && !empty($RECORD_ACTIVITY_NOTIFIER)}
						<input type="hidden" id="recordActivityNotifier" data-interval="{App\Config::performance('recordActivityNotifierInterval', 10)}" data-record="{$RECORD->getId()}" data-module="{$MODULE_NAME}" />
					{/if}
					<div class="contents">
						<!-- /tpl-Base-DetailViewHeader -->
{/strip}
