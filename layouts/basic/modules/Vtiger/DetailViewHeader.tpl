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
		<div class="row detailViewTitle">
			<div class="{if $NO_PAGINATION} col-md-12 {else} col-md-10 {/if}">
				<div class="row">
					<div class="col-md-12 marginBottom5px">
						<div class="row">
							{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
							{include file="DetailViewHeaderTitle.tpl"|vtemplate_path:$MODULE}
						</div>
					</div>
					<div class="col-md-12 detailViewToolbar paddingLRZero" style="text-align: right;">
						<div class="pull-left paddingLRZero col-md-10">
							<div class="btn-toolbar">
								{foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWBASIC']}
									<span class="btn-group {$DETAIL_VIEW_BASIC_LINK->getGrupClassName()}">
										<button {if $DETAIL_VIEW_BASIC_LINK->linkhint neq ''}data-content="{vtranslate($DETAIL_VIEW_BASIC_LINK->linkhint, $MODULE_NAME)}" {/if} class="btn btn-default {$MODULE_NAME}_detailView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_BASIC_LINK->getLabel())} {if $DETAIL_VIEW_BASIC_LINK->linkhint neq ''} popoverTooltip {/if} {$DETAIL_VIEW_BASIC_LINK->getClassName()}" 
																							 {if $DETAIL_VIEW_BASIC_LINK->linkdata && is_array($DETAIL_VIEW_BASIC_LINK->linkdata)}
																								 {foreach item=DATA_VALUE key=DATA_NAME from=$DETAIL_VIEW_BASIC_LINK->linkdata}
																									 data-{$DATA_NAME}="{$DATA_VALUE}" 
																								 {/foreach}
																							 {/if}
																							 data-placement="bottom"
																							 {assign var="LABEL" value=$DETAIL_VIEW_BASIC_LINK->getLabel()}
																							 {if $DETAIL_VIEW_BASIC_LINK->isPageLoadLink() || $DETAIL_VIEW_BASIC_LINK->linkPopup}
																								 onclick="window.open('{$DETAIL_VIEW_BASIC_LINK->getUrl()}', '{if $DETAIL_VIEW_BASIC_LINK->linktarget}{$DETAIL_VIEW_BASIC_LINK->linktarget}{else}_self{/if}'{if $DETAIL_VIEW_BASIC_LINK->linkPopup}, 'resizable=yes,location=no,scrollbars=yes,toolbar=no,menubar=no,status=no'{/if})" 
																							 {else}
																								 onclick="{$DETAIL_VIEW_BASIC_LINK->getUrl()}"
																							 {/if}
																							 >
											{if $DETAIL_VIEW_BASIC_LINK->linkimg neq ''}
												<img class="image-in-button" src="{$DETAIL_VIEW_BASIC_LINK->linkimg}">
										{elseif $DETAIL_VIEW_BASIC_LINK->linkicon neq ''}<span class="{$DETAIL_VIEW_BASIC_LINK->linkicon}"></span>{if $LABEL neq ''}&nbsp;&nbsp;{/if}{/if}
										{if $LABEL neq ''}
											<strong>{vtranslate($LABEL, $MODULE_NAME)}</strong>
										{/if}
									</button>
								</span>
							{/foreach}
							{if $DETAILVIEW_LINKS['DETAILVIEW']|@count gt 0}
								{foreach item=DETAIL_VIEW_LINK from=$DETAILVIEW_LINKS['DETAILVIEW']}
									{if $DETAIL_VIEW_LINK->getLabel() neq "" OR $DETAIL_VIEW_LINK->linkicon neq ""} 
										<span class="btn-group">
											<a class="btn btn-default {$DETAIL_VIEW_LINK->getClassName()}
											   {if $DETAIL_VIEW_LINK->linklabel neq ''} popoverTooltip{/if}"
											   href='{$DETAIL_VIEW_LINK->getUrl()}' 
											   {if $DETAIL_VIEW_LINK->linkdata && is_array($DETAIL_VIEW_LINK->linkdata)}
													{foreach item=DATA_VALUE key=DATA_NAME from=$DETAIL_VIEW_LINK->linkdata}
														data-{$DATA_NAME}="{$DATA_VALUE}" 
													{/foreach}
												{/if}
											   {if $DETAIL_VIEW_LINK->linklabel neq ''}data-content="{vtranslate($DETAIL_VIEW_LINK->linklabel, $MODULE_NAME)}" {/if}>
												{if $DETAIL_VIEW_LINK->linkicon neq ''}
													<span class="{$DETAIL_VIEW_LINK->linkicon} icon-in-button"></span> 
												{else}
													{vtranslate($DETAIL_VIEW_LINK->getLabel(), $MODULE_NAME)}
												{/if}
											</a>
										</span>
									{/if}	
								{/foreach}
							{/if}

											</div>
										</div>
									</div>
								</div>
							</div>
							{if !{$NO_PAGINATION}}
								<div class="col-md-2 detailViewPagingButton pull-right">
									<span class="btn-group pull-right">
										<button class="btn btn-default" id="detailViewPreviousRecordButton" {if empty($PREVIOUS_RECORD_URL)} disabled="disabled" {else} onclick="window.location.href = '{$PREVIOUS_RECORD_URL}'" {/if}><span class="glyphicon glyphicon-chevron-left"></span></button>
										<button class="btn btn-default" id="detailViewNextRecordButton" {if empty($NEXT_RECORD_URL)} disabled="disabled" {else} onclick="window.location.href = '{$NEXT_RECORD_URL}'" {/if}><span class="glyphicon glyphicon-chevron-right"></span></button>
									</span>
								</div>
							{/if}
						</div>
						<div class="detailViewInfo row">
							<div class="{if $NO_PAGINATION} col-md-12 {else} col-md-10 {/if} {if !empty($DETAILVIEW_LINKS['DETAILVIEWTAB']) || !empty($DETAILVIEW_LINKS['DETAILVIEWRELATED']) } details {/if}">
								<form id="detailView" data-name-fields='{ZEND_JSON::encode($MODULE_MODEL->getNameFields())}' method="POST">
									{if !empty($PICKLIST_DEPENDENCY_DATASOURCE)} 
										<input type="hidden" name="picklistDependency" value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_DEPENDENCY_DATASOURCE)}"> 
									{/if} 
									<div class="contents">
{/strip}

