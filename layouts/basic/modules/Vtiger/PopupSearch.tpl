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
	<input type="hidden" id="parentModule" value="{$SOURCE_MODULE}"/>
	<input type="hidden" id="sourceRecord" value="{$SOURCE_RECORD}"/>
	<input type="hidden" id="sourceField" value="{$SOURCE_FIELD}"/>
	<input type="hidden" id="url" value="{$GETURL}" />
	<input type="hidden" id="multi_select" value="{$MULTI_SELECT}" />
	<input type="hidden" id="currencyId" value="{$CURRENCY_ID}" />
	<input type="hidden" id="relatedParentModule" value="{$RELATED_PARENT_MODULE}"/>
	<input type="hidden" id="relatedParentId" value="{$RELATED_PARENT_ID}"/>
	<div class="popupContainer row">
		<div class="paddingLeftMd form-group pull-left">
			<h3 class="popupModuleName">{vtranslate($MODULE_NAME, $MODULE_NAME)}</h3>
			<form class="popupSearchContainer form-inline" onsubmit="return false;" method="POST">
				<input class="col-md-2" type="hidden" id="searchfield"/>
				<input class="col-md-2" type="hidden" id="searchvalue"/>
			</form>
		</div>
		{include file='PopupSearchActions.tpl'|vtemplate_path:$MODULE_NAME}
		<div class="col-md-4 form-group pull-right">
			{if $SOURCE_MODULE neq 'PriceBooks' && $SOURCE_FIELD neq 'productsRelatedList'}
				<div class="popupPaging">
					<div class="row">
						<div class="col-md-12">
							<div class="pull-right">
								<div class="pageNumbers">
									<span class="pageNumbersText">{if !empty($LISTVIEW_ENTRIES)}{$PAGING_MODEL->getRecordStartRange()} {vtranslate('LBL_TO_LC', $MODULE)} {$PAGING_MODEL->getRecordEndRange()}{else}<span>&nbsp;</span>{/if}</span>
									<span class="alignBottom">
										<span class="glyphicon glyphicon-refresh totalNumberOfRecords cursorPointer{if empty($LISTVIEW_ENTRIES)} hide{/if}" style="margin-left:5px"></span>
									</span>
								</div>
								<div class="btn-group alignTop margin0px">
									<span class="pull-right">
										<span class="btn-group" role="group">
											<button class="btn btn-default" role="group" id="listViewPreviousPageButton" {if !$PAGING_MODEL->isPrevPageExists()} disabled {/if} type="button"><span class="glyphicon glyphicon-chevron-left"></span></button>
											<button class="btn btn-default dropdown-toggle" role="group" type="button" id="listViewPageJump" data-toggle="dropdown" {if $PAGE_COUNT eq 1} disabled {/if}>
												<span class="vtGlyph vticon-pageJump" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP',$moduleName)}"></span>
											</button>
											<ul class="listViewBasicAction dropdown-menu" id="listViewPageJumpDropDown">
												<li>
													<div>
														<div class="col-md-4 recentComments textAlignCenter pushUpandDown2per"><span>{vtranslate('LBL_PAGE',$moduleName)}</span></div>
														<div class="col-md-3 recentComments">
															<input type="text" id="pageToJump" class="listViewPagingInput textAlignCenter" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP')}" value="{$PAGE_NUMBER}"/>
														</div>
														<div class="col-md-2 recentComments textAlignCenter pushUpandDown2per">
															{vtranslate('LBL_OF',$moduleName)}
														</div>
														<div class="col-md-2 recentComments pushUpandDown2per textAlignCenter" id="totalPageCount">{$PAGE_COUNT}</div>
													</div>
												</li>
											</ul>
											<button class="btn btn-default" id="listViewNextPageButton" {if (!$PAGING_MODEL->isNextPageExists()) or ($PAGE_COUNT eq 1)} disabled {/if} type="button"><span class="glyphicon glyphicon-chevron-right"></span></button>
										</span>
									</span>	
								</div>
							</div>
						</div>
					</div>
				</div>
			{/if}
		</div>
	</div>
{/strip}
