{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce Sp. z o.o.
********************************************************************************/
-->*}
{strip}
{assign var="MODULE_NAME" value=$MODULE_MODEL->get('name')}
<input id="recordId" type="hidden" value="{$RECORD->getId()}"/>
<div class="tpl-PreferenceDetailViewHeader detailViewContainer">
	<div class="detailViewTitle marginTop5" id="prefPageHeader">
		<div class="row">
			<div class="ml-0 pr-1 col-md-5 row">
				<div class="logo pl-0 col-2 col-md-2 mt-3">
					{assign var=IMAGE value=$RECORD->getImage()}
					{if $IMAGE}
						<img src="{$IMAGE.url}" class="pushDown" alt="{$RECORD->getName()}" title="{$RECORD->getName()}"
							 height="80" align="left">
						<br/>
					{else}
						<span class="detailViewIcon userIcon-{$MODULE}"></span>
					{/if}
				</div>
				<div class="col-10 col-md-10 p-0 d-flex flex-column">
					<div id="myPrefHeading">
						<h3>{\App\Language::translate('LBL_MY_PREFERENCES', $MODULE_NAME)} </h3>
					</div>
					<div>
						{\App\Language::translate('LBL_USERDETAIL_INFO', $MODULE_NAME)}
						&nbsp;&nbsp;"<strong>{$RECORD->getName()}</strong>"
					</div>
				</div>
			</div>
			<div class="mr-0 ml-2 pl-1 col-md-7 py-3 mt-2">
				<div class="btn-group btn-toolbar mr-md-2 flex-md-nowrap float-right u-w-sm-down-100">
					{foreach item=LINK from=$DETAILVIEW_LINKS['DETAILVIEWPREFERENCE']}
						{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE) BUTTON_VIEW='detailViewBasic' CLASS='c-btn-link--responsive'}
					{/foreach}
					{if $DETAILVIEW_LINKS['DETAIL_VIEW_BASIC']|@count gt 0}
						{include file=\App\Layout::getTemplatePath('ButtonViewLinks.tpl') TEXT_HOLDER='LBL_MORE' LINKS=$DETAILVIEW_LINKS['DETAIL_VIEW_BASIC'] CLASS='c-btn-link--responsive'}
					{/if}
				</div>
			</div>
		</div>
		<div class="detailViewInfo userPreferences row">
			<div class="details col-md-12">
				<form id="detailView" data-name-fields='{\App\Json::encode($MODULE_MODEL->getNameFields())}'
					  method="POST">
					<div class="contents">
						{/strip}
