{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
<!-- tpl-Base-DetailViewHeader -->
{assign var="MODULE_NAME" value=$MODULE_MODEL->get('name')}
<input id="recordId" type="hidden" value="{$RECORD->getId()}"/>
<div class="detailViewContainer">
	<div class="row detailViewTitle p-0">
		{if $SHOW_BREAD_CRUMBS}
			<div class="o-breadcrumb widget_header mb-2 d-flex justify-content-between px-2 w-100">
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
		<div class="col-md-12 {if !empty($DETAILVIEW_LINKS['DETAILVIEWTAB']) || !empty($DETAILVIEW_LINKS['DETAILVIEWRELATED']) } details {/if}">
			<form id="detailView" data-name-fields="{\App\Purifier::encodeHtml(\App\Json::encode($MODULE_MODEL->getNameFields()))}" method="POST">
				{if !empty($PICKLIST_DEPENDENCY_DATASOURCE)}
					<input type="hidden" name="picklistDependency"
						   value="{\App\Purifier::encodeHtml($PICKLIST_DEPENDENCY_DATASOURCE)}">
				{/if}
				<div class="contents">
					<!-- /tpl-Base-DetailViewHeader -->
					{/strip}

