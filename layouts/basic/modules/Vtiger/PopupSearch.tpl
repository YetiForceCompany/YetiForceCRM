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
	<input type="hidden" id="parentModule" value="{$SOURCE_MODULE}" />
	<input type="hidden" id="sourceRecord" value="{$SOURCE_RECORD}" />
	<input type="hidden" id="sourceField" value="{$SOURCE_FIELD}" />
	<input type="hidden" id="url" value="{$GETURL}" />
	<input type="hidden" id="multi_select" value="{$MULTI_SELECT}" />
	<input type="hidden" id="currencyId" value="{$CURRENCY_ID}" />
	<input type="hidden" id="relatedParentModule" value="{$RELATED_PARENT_MODULE}" />
	<input type="hidden" id="relatedParentId" value="{$RELATED_PARENT_ID}" />
	<div class="popupContainer d-flex mt-2">
		<div class="form-group">
			<h3 class="popupModuleName">{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}</h3>
			<form class="popupSearchContainer form-inline" onsubmit="return false;" method="POST">
				<input class="col-md-2" type="hidden" id="searchfield" />
				<input class="col-md-2" type="hidden" id="searchvalue" />
			</form>
		</div>
		{include file=\App\Layout::getTemplatePath('PopupSearchActions.tpl', $MODULE_NAME)}
		<div class="form-group ml-auto">
			{if $SOURCE_MODULE neq 'PriceBooks' && $SOURCE_FIELD neq 'productsRelatedList'}
				<div class="popupPaging">
					<div class="row">
						<div class="col-md-12">
							<div class="float-right">
								<div class="pageNumbers">
									<span class="pageNumbersText">{if !empty($LISTVIEW_ENTRIES)}{$PAGING_MODEL->getRecordStartRange()} {\App\Language::translate('LBL_TO_LC', $MODULE)} {$PAGING_MODEL->getRecordEndRange()}{else}<span>&nbsp;</span>{/if}</span>
									<span class="alignBottom">
										<span class="fas fa-sync-alt totalNumberOfRecords cursorPointer{if empty($LISTVIEW_ENTRIES)} d-none{/if}" style="margin-left:5px"></span>
									</span>
								</div>
								<div class="btn-group alignTop m-0">
									<span class="float-right">
										<span class="btn-group" role="group">
											<button class="btn btn-light" role="group" id="listViewPreviousPageButton" {if !$PAGING_MODEL->isPrevPageExists()} disabled {/if} type="button"><span class="fas fa-chevron-left"></span></button>
											<button class="btn btn-light dropdown-toggle" role="group" type="button" id="listViewPageJump" data-toggle="dropdown" {if $PAGE_COUNT eq 1} disabled {/if}>
												<span class="fas fa-arrows-alt-h" title="{\App\Language::translate('LBL_LISTVIEW_PAGE_JUMP',$moduleName)}"></span>
											</button>
											<ul class="listViewBasicAction dropdown-menu" id="listViewPageJumpDropDown">
												<li class="dropdown-item">
													<div class="row">
														<div class="col-md-4 recentComments textAlignCenter pushUpandDown2per"><span>{\App\Language::translate('LBL_PAGE',$moduleName)}</span></div>
														<div class="col-md-3 recentComments">
															<input type="text" id="pageToJump" class="listViewPagingInput textAlignCenter" title="{\App\Language::translate('LBL_LISTVIEW_PAGE_JUMP')}" value="{$PAGE_NUMBER}" />
														</div>
														<div class="col-md-2 recentComments textAlignCenter pushUpandDown2per">
															{\App\Language::translate('LBL_OF',$moduleName)}
														</div>
														<div class="col-md-2 recentComments pushUpandDown2per textAlignCenter" id="totalPageCount">{$PAGE_COUNT}</div>
													</div>
												</li>
											</ul>
											<button class="btn btn-light" id="listViewNextPageButton" {if (!$PAGING_MODEL->isNextPageExists()) or ($PAGE_COUNT eq 1)} disabled {/if} type="button"><span class="fas fa-chevron-right"></span></button>
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
