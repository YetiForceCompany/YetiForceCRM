{strip}
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
{assign var="MODULE_NAME" value=$MODULE_MODEL->get('name')}
<input id="recordId" type="hidden" value="{$RECORD->getId()}"/>
<div class="detailViewContainer">
	<div class="row detailViewTitle p-0">
		{if $SHOW_BREAD_CRUMBS}
			<div class="o-breadcrumb js-breadcrumb widget_header mb-2 d-flex justify-content-between px-2 w-100">
				<div class="o-breadcrumb__container">
					{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
				</div>
				<a class="btn btn-outline-dark d-md-none my-auto o-breadcrumb__actions-btn js-breadcrumb__actions-btn"
				   href="#" data-js="click" role="button"
				   aria-expanded="false" aria-controls="o-view-actions__container">
							<span class="fas fa-ellipsis-h fa-fw"
								  title="{\App\Language::translate('LBL_ACTION_MENU')}"></span>
				</a>
				<div class="detailViewToolbar my-auto o-breadcrumb__actions js-breadcrumb__actions d-flex float-right flex-column flex-md-row"
					 id="o-view-actions__container">
					{if $DETAILVIEW_LINKS['DETAIL_VIEW_ADDITIONAL']}
						<div class="btn-group btn-toolbar mr-md-2 flex-md-nowrap">
							{foreach item=LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_ADDITIONAL']}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='detailViewAdditional' BREAKPOINT='md' CLASS='c-btn-link--responsive'}
							{/foreach}
						</div>
					{/if}
					{if $DETAILVIEW_LINKS['DETAIL_VIEW_BASIC']}
						<div class="btn-group btn-toolbar mr-md-2 flex-md-nowrap">
							{foreach item=LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_BASIC']}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='detailViewBasic' BREAKPOINT='md' CLASS='c-btn-link--responsive'}
							{/foreach}
						</div>
					{/if}
					{if $DETAILVIEW_LINKS['DETAIL_VIEW_EXTENDED']}
						<div class="btn-group btn-toolbar mr-md-2 flex-md-nowrap">
							{foreach item=LINK from=$DETAILVIEW_LINKS['DETAIL_VIEW_EXTENDED']}
								{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='detailViewExtended' BREAKPOINT='md' CLASS='c-btn-link--responsive'}
							{/foreach}
						</div>
					{/if}
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
			<form id="detailView" data-name-fields='{\App\Json::encode($MODULE_MODEL->getNameFields())}' method="POST">
				{if !empty($PICKLIST_DEPENDENCY_DATASOURCE)}
					<input type="hidden" name="picklistDependency"
						   value="{\App\Purifier::encodeHtml($PICKLIST_DEPENDENCY_DATASOURCE)}">
				{/if}
				<div class="contents">
					{/strip}

