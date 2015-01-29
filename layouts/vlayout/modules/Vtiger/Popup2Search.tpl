{*<!--
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
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
    <input type="hidden" id="module" value="{$MODULE}"/>
    <input type="hidden" id="parent" value="{$PARENT_MODULE}"/>
    <input type="hidden" id="sourceRecord" value="{$SOURCE_RECORD}"/>
    <input type="hidden" id="sourceField" value="{$SOURCE_FIELD}"/>
    <input type="hidden" id="url" value="{$GETURL}" />
    <input type="hidden" id="multi_select" value="{$MULTI_SELECT}" />
    <input type="hidden" id="currencyId" value="{$CURRENCY_ID}" />
    <input type="hidden" id="relatedParentModule" value="{$RELATED_PARENT_MODULE}"/>
    <input type="hidden" id="relatedParentId" value="{$RELATED_PARENT_ID}"/>
    <input type="hidden" id="view" value="{$VIEW}"/>
    <input type="hidden" id="popupType" value="2"/>
	<br/>
    <div class="popupContainer row-fluid">
		<div class="span2">
			{if $MULTI_SELECT}
				{if !empty($LISTVIEW_ENTRIES)}<button class="select btn"><strong>{vtranslate('LBL_SELECT', $MODULE)}</strong></button>{/if}
			{else}
				&nbsp;
			{/if}
		</div>
        <div class="span6">
			<h3>{vtranslate($MODULE_NAME, $MODULE_NAME)}</h3>
            <form class="form-horizontal popupSearchContainer" onsubmit="return false;" method="POST">
				<input class="span2" type="hidden" id="searchfield"/>
				<input class="span2" type="hidden" id="searchvalue"/>
            </form>
        </div>
		<div class="span4">
			{if $SOURCE_MODULE neq 'PriceBooks' && $SOURCE_FIELD neq 'productsRelatedList'}
			<div class="popupPaging">
				<div class="row-fluid">
						<span class="span3" style="float:right !important;min-width:230px">
							<span class="pull-right">
								<span class="pageNumbers">
									<span class="pageNumbersText">{if !empty($LISTVIEW_ENTRIES)}{$PAGING_MODEL->getRecordStartRange()} {vtranslate('LBL_to', $MODULE)} {$PAGING_MODEL->getRecordEndRange()}{else}<span>&nbsp;</span>{/if}</span>
									<span class="alignBottom">
										<span class="icon-refresh totalNumberOfRecords cursorPointer{if empty($LISTVIEW_ENTRIES)} hide{/if}" style="margin-left:5px"></span>
									</span>
								</span>&nbsp;&nbsp;
								<span class="btn-group pull-right">
									<button class="btn" id="listViewPreviousPageButton" {if !$PAGING_MODEL->isPrevPageExists()} disabled {/if}><span class="icon-chevron-left"></span></button>
									<button class="btn dropdown-toggle" type="button" id="listViewPageJump" data-toggle="dropdown" {if $PAGE_COUNT eq 1} disabled {/if}>
										<i class="vtGlyph vticon-pageJump" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP',$moduleName)}"></i>
									</button>
									<ul class="listViewBasicAction dropdown-menu" id="listViewPageJumpDropDown">
										<li>
											<span class="row-fluid">
												<span class="span3 pushUpandDown2per"><span class="pull-right">{vtranslate('LBL_PAGE',$moduleName)}</span></span>
												<span class="span4">
													<input type="text" id="pageToJump" class="listViewPagingInput" value="{$PAGE_NUMBER}"/>
												</span>
												<span class="span2 textAlignCenter pushUpandDown2per">
													{vtranslate('LBL_OF',$moduleName)}&nbsp;
												</span>
												<span class="span3 pushUpandDown2per" id="totalPageCount">{$PAGE_COUNT}</span>
											</span>
										</li>
									</ul>
									<button class="btn" id="listViewNextPageButton" {if (!$PAGING_MODEL->isNextPageExists()) or ($PAGE_COUNT eq 1)} disabled {/if}><span class="icon-chevron-right"></span></button>
								</span>
							</span>
						</span>
					</div>
				</div>
			{/if}
		</div>
    </div>
{/strip}