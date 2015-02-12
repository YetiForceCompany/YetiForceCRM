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
	{assign var="MODULE_NAME" value=$MODULE_MODEL->get('name')}
	<input id="recordId" type="hidden" value="{$RECORD->getId()}" />
	<div class="detailViewContainer">
		<div class="row-fluid detailViewTitle">
			<div class="{if $NO_PAGINATION} span12 {else} span10 {/if}">
				<div class="row-fluid">
					<div class="span6">
						<div class="row-fluid">
							{include file="DetailViewHeaderTitle.tpl"|vtemplate_path:$MODULE}
						</div>
					</div>
					<div class="span6 detailViewToolbar" style="text-align: right;">
						<div>
							<div class="btn-toolbar">
							{foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWBASIC']}
							<span class="btn-group {$DETAIL_VIEW_BASIC_LINK->getGrupClassName()}">
								<button {if $DETAIL_VIEW_BASIC_LINK->linktitle neq ''}title="{vtranslate($DETAIL_VIEW_BASIC_LINK->linktitle, $MODULE_NAME)}" {/if} class="btn {$DETAIL_VIEW_BASIC_LINK->getClassName()}" id="{$MODULE_NAME}_detailView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_BASIC_LINK->getLabel())}"
									{assign var="LABEL" value=$DETAIL_VIEW_BASIC_LINK->getLabel()}
                                                                        {if $DETAIL_VIEW_BASIC_LINK->isPageLoadLink()}
										onclick="window.open('{$DETAIL_VIEW_BASIC_LINK->getUrl()}','{if $DETAIL_VIEW_BASIC_LINK->linktarget}{$DETAIL_VIEW_BASIC_LINK->linktarget}{else}_self{/if}')"
									{else}
										onclick={$DETAIL_VIEW_BASIC_LINK->getUrl()}
									{/if}>
									{if $DETAIL_VIEW_BASIC_LINK->linkicon neq ''}<i class="{$DETAIL_VIEW_BASIC_LINK->linkicon}"></i>{if $LABEL neq ''}&nbsp;&nbsp;{/if}{/if}
                                                                        {if $LABEL neq ''}
                                                                            <strong>{vtranslate($LABEL, $MODULE_NAME)}</strong>
                                                                        {/if}
								</button>
							</span>
							{/foreach}
							{if $DETAILVIEW_LINKS['DETAILVIEW']|@count gt 0}
							<span class="btn-group">
								<button class="btn dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
									<strong>{vtranslate('LBL_MORE', $MODULE_NAME)}</strong>&nbsp;&nbsp;<i class="caret"></i>
								</button>
								<ul class="dropdown-menu pull-right">
									{foreach item=DETAIL_VIEW_LINK from=$DETAILVIEW_LINKS['DETAILVIEW']}
									{if $DETAIL_VIEW_LINK->getLabel() eq ""} 
										<li class="divider"></li>	
									{else}
									<li id="{$MODULE_NAME}_detailView_moreAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_LINK->getLabel())}">
										<a href={$DETAIL_VIEW_LINK->getUrl()} {if $DETAIL_VIEW_LINK->linktarget}target="{$DETAIL_VIEW_LINK->linktarget}"{/if} >{vtranslate($DETAIL_VIEW_LINK->getLabel(), $MODULE_NAME)}</a>
									</li>
									{/if}
									{/foreach}
								</ul>
							</span>
							{/if}
							{if $DETAILVIEW_LINKS['DETAILVIEWSETTING']|@count gt 0}
								<span class="btn-group">
									<button class="btn dropdown-toggle" href="#" data-toggle="dropdown"><i class="icon-wrench" alt="{vtranslate('LBL_SETTINGS', $MODULE_NAME)}" title="{vtranslate('LBL_SETTINGS', $MODULE_NAME)}"></i>&nbsp;&nbsp;<i class="caret"></i></button>
									<ul class="listViewSetting dropdown-menu">
										{foreach item=DETAILVIEW_SETTING from=$DETAILVIEW_LINKS['DETAILVIEWSETTING']}
											<li><a href={$DETAILVIEW_SETTING->getUrl()} {if $DETAILVIEW_SETTING->linktarget}target="{$DETAILVIEW_SETTING->linktarget}"{/if}>{vtranslate($DETAILVIEW_SETTING->getLabel(), $MODULE_NAME)}</a></li>
										{/foreach}
									</ul>
								</span>
							{/if}
							</div>
						</div>
					</div>
				</div>
			</div>
			{if !{$NO_PAGINATION}}
				<div class="span2 detailViewPagingButton">
					<span class="btn-group pull-right">
						<button class="btn" id="detailViewPreviousRecordButton" {if empty($PREVIOUS_RECORD_URL)} disabled="disabled" {else} onclick="window.location.href='{$PREVIOUS_RECORD_URL}'" {/if}><i class="icon-chevron-left"></i></button>
						<button class="btn" id="detailViewNextRecordButton" {if empty($NEXT_RECORD_URL)} disabled="disabled" {else} onclick="window.location.href='{$NEXT_RECORD_URL}'" {/if}><i class="icon-chevron-right"></i></button>
					</span>
				</div>
			{/if}
		</div>
		<div class="detailViewInfo row-fluid">
			<div class="{if $NO_PAGINATION} span12 {else} span10 {/if} {if !empty($DETAILVIEW_LINKS['DETAILVIEWTAB']) || !empty($DETAILVIEW_LINKS['DETAILVIEWRELATED']) } details {/if}">
				<form id="detailView" data-name-fields='{ZEND_JSON::encode($MODULE_MODEL->getNameFields())}' method="POST">
                    {if !empty($PICKLIST_DEPENDENCY_DATASOURCE)} 
                        <input type="hidden" name="picklistDependency" value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_DEPENDENCY_DATASOURCE)}"> 
                    {/if} 
					<div class="contents">
{/strip}
