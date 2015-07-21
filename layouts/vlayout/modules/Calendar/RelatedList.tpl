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
    <div class="relatedContainer">
        {assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
        <input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
        <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE->get('name')}" />
        <input type="hidden" value="{$ORDER_BY}" id="orderBy">
        <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
        <input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
        <input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
        <input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
        <div class="relatedHeader ">
            <div class="btn-toolbar row">
                <div class="col-md-6">
                    {foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
						{if {Users_Privileges_Model::isPermitted($RELATED_MODULE_NAME, 'EditView')} }
                        <div class="btn-group">
                            {assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
                            <button type="button" class="btn btn-default addButton
                            {if $IS_SELECT_BUTTON eq true} selectRelation {/if} moduleColor_{$RELATED_MODULE_NAME} {if $RELATED_LINK->linkqcs eq true}quickCreateSupported{/if}"
                        {if $IS_SELECT_BUTTON eq true} data-moduleName={$RELATED_LINK->get('_module')->get('name')} {/if}
                        {if ($RELATED_LINK->isPageLoadLink())}
                        {if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
                        data-url="{$RELATED_LINK->getUrl()}"
                    {/if}
            {if $IS_SELECT_BUTTON neq true}name="addButton"{/if}>{if $IS_SELECT_BUTTON eq false}<span class="glyphicon glyphicon-plus icon-white"></span>{/if}&nbsp;<strong>{$RELATED_LINK->getLabel()}</strong></button>
			</div>
			{/if}
		{/foreach}
		&nbsp;
		<div class="btn-group" style="vertical-align: top;">
			<input class="switchBtn" type="checkbox" {if $TIME=='current'}checked{/if} title="{vtranslate('LBL_CHANGE_ACTIVITY_TYPE')}" data-size="small" data-label-width="5" data-handle-width="90" data-on-text="{vtranslate('LBL_TO_REALIZE')}" data-off-text="{vtranslate('LBL_HISTORY')}">
		</div>
</div>
<div class="col-md-6">
    <div class="pull-right">
        <span class="pageNumbers">
            <span class="pageNumbersText">{if !empty($RELATED_RECORDS)} {$PAGING->getRecordStartRange()} {vtranslate('LBL_to', $RELATED_MODULE->get('name'))} {$PAGING->getRecordEndRange()}{else}<span>&nbsp;</span>{/if}</span>
            <span class="glyphicon glyphicon-refresh totalNumberOfRecords cursorPointer{if empty($RELATED_RECORDS)} hide{/if}" title="{vtranslate('LBL_REFRESH')}"></span>
        </span>
        <span class="btn-group">
            <button class="btn btn-default" id="relatedListPreviousPageButton" {if !$PAGING->isPrevPageExists()} disabled {/if} type="button"><span class="glyphicon glyphicon-chevron-left"></span></button>
            <button class="btn btn-default dropdown-toggle" type="button" id="relatedListPageJump" data-toggle="dropdown" {if $PAGE_COUNT eq 1} disabled {/if}>
                <span class="vtGlyph vticon-pageJump" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP',$moduleName)}"></span>
            </button>
            <ul class="listViewBasicAction dropdown-menu" id="relatedListPageJumpDropDown">
                <li>
                    <div class="">
                        <div class="col-md-4 recentComments textAlignCenter pushUpandDown2per"><span>{vtranslate('LBL_PAGE',$moduleName)}</span></div>
                        <div class="col-md-3 recentComments">
                            <input type="text" id="pageToJump" class="listViewPagingInput textAlignCenter" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP')}" value="{$PAGING->getCurrentPage()}"/>
                        </div>
                        <div class="col-md-2 recentComments textAlignCenter pushUpandDown2per">
                            {vtranslate('LBL_OF',$moduleName)}
                        </div>
                        <div class="col-md-2 recentComments textAlignCenter pushUpandDown2per" id="totalPageCount">{$PAGE_COUNT}</div>
                    </div>
                </li>
            </ul>
            <button class="btn btn-default" id="relatedListNextPageButton" {if (!$PAGING->isNextPageExists()) or ($PAGE_COUNT eq 1)} disabled {/if} type="button"><span class="glyphicon glyphicon-chevron-right"></span></button>
        </span>
    </div>
</div>
</div>
</div>
<div class="contents-topscroll">
    <div class="topscroll-div">
        &nbsp;
    </div>
</div>
<div class="relatedContents contents-bottomscroll">
    <div class="bottomscroll-div">
		{assign var=FILENAME value="RelatedListContents.tpl"}
		{include file=$FILENAME|vtemplate_path:$RELATED_MODULE->get('name')}
    </div>
</div>
</div>
{/strip}
