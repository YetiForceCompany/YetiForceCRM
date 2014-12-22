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
        <input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
        {assign var="RELATED_MODULE_NAME" value=$RELATED_MODULE->get('name')} 
        <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE_NAME}" /> 
        <input type="hidden" value="{$ORDER_BY}" id="orderBy">
        <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
        <input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
        <input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
        <input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
        <div class="relatedHeader ">
            <div class="btn-toolbar row-fluid">
                <div class="span6">

                    {foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
                        <div class="btn-group">
                            {assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
                            <button type="button" class="btn addButton
                            {if $IS_SELECT_BUTTON eq true} selectRelation {/if} "
                        {if $IS_SELECT_BUTTON eq true} data-moduleName={$RELATED_LINK->get('_module')->get('name')} {/if}
                        {if ($RELATED_LINK->isPageLoadLink())}
                        {if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
                    {/if}
            {if $IS_SELECT_BUTTON neq true}name="composeEmail"{/if}>{if $IS_SELECT_BUTTON eq false}<i class="icon-plus icon-white"></i>{/if}&nbsp;<strong>{$RELATED_LINK->getLabel()}</strong></button>
    </div>
{/foreach}
&nbsp;
</div>
<div class="span6">
    <div class="pull-right">
        <span class="pageNumbers">
            <span class="pageNumbersText">{if !empty($RELATED_RECORDS)} {$PAGING->getRecordStartRange()} {vtranslate('LBL_to', $RELATED_MODULE_NAME)} {$PAGING->getRecordEndRange()}{else}<span>&nbsp;</span>{/if}</span>
            <span class="icon-refresh pull-right totalNumberOfRecords cursorPointer{if empty($RELATED_RECORDS)} hide{/if}"></span>
        </span>
        <span class="btn-group">
            <button class="btn" id="relatedListPreviousPageButton" {if !$PAGING->isPrevPageExists()} disabled {/if} type="button"><span class="icon-chevron-left"></span></button>
            <button class="btn dropdown-toggle" type="button" id="relatedListPageJump" data-toggle="dropdown" {if $PAGE_COUNT eq 1} disabled {/if}>
                <i class="vtGlyph vticon-pageJump" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP',$moduleName)}"></i>
            </button>
            <ul class="listViewBasicAction dropdown-menu" id="relatedListPageJumpDropDown">
                <li>
                    <span class="row-fluid">
                        <span class="span3"><span class="pull-right">{vtranslate('LBL_PAGE',$moduleName)}</span></span>
                        <span class="span4">
                            <input type="text" id="pageToJump" class="listViewPagingInput" value="{$PAGING->getCurrentPage()}"/>
                        </span>
                        <span class="span2 textAlignCenter">
                            {vtranslate('LBL_OF',$moduleName)}
                        </span>
                        <span class="span2" id="totalPageCount">{$PAGE_COUNT}</span>
                    </span>
                </li>
            </ul>
            <button class="btn" id="relatedListNextPageButton" {if (!$PAGING->isNextPageExists()) or ($PAGE_COUNT eq 1)} disabled {/if} type="button"><span class="icon-chevron-right"></span></button>
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
			{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
        <table class="table table-bordered listViewEntriesTable">
            <thead>
                <tr class="listViewHeaders">
                    {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
							<th class="{$WIDTHTYPE}">
                            {if $HEADER_FIELD->get('column') eq 'access_count' or $HEADER_FIELD->get('column') eq 'idlists'}
                                <a href="javascript:void(0);" class="noSorting">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE_NAME)}</a>
                            {else}
                                <a href="javascript:void(0);" class="relatedListHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('column')}">{vtranslate($HEADER_FIELD->get('label')|html_entity_decode, $RELATED_MODULE_NAME)}
                                    &nbsp;&nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}<img class="{$SORT_IMAGE}">{/if}
                                </a>
                            {/if}
                        </th>
                    {/foreach}
                </tr>
            </thead>
            {foreach item=RELATED_RECORD from=$RELATED_RECORDS}
                <tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' name="emailsRelatedRecord">
                    {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                        {assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
							<td class="{$WIDTHTYPE}">
                            {if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
                                <a>{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
                            {elseif $RELATED_HEADERNAME eq 'access_count'}
                                {$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
                            {elseif $RELATED_HEADERNAME eq 'date_start'}
                                {if $RELATED_RECORD->isSentMail()} 
                                    {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)} 
                                {elseif $RELATED_RECORD->isFromMailManager()} 
                                    <span class="label label-warning">{vtranslate('LBL_ATTACHED',$RELATED_MODULE_NAME)}</span>  
                                {else} 
                                    <span class="label label-info">{vtranslate('LBL_DRAFT',$RELATED_MODULE_NAME)}</span>  
                                {/if}   
                            {else if $RELATED_HEADERNAME eq 'time_start'}
                                {if $RELATED_RECORD->isSentMail()} 
                                    {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}   
                                 {else}--- 
                                 {/if}
                            {elseif $RELATED_HEADERNAME eq 'parent_id'}
                                {assign var=REFERENCE_RECORD value=$RELATED_RECORD->get($RELATED_HEADERNAME)}
                                {assign var=RECORD_MODULE_MODEL value=$RELATED_RECORD->getModule()}
                                {assign var=RECORD_FIELD_MODEL value=$RECORD_MODULE_MODEL->getField($RELATED_HEADERNAME)}
                                {assign var=UI_TYPE_MODEL value=Vtiger_Base_UIType::getInstanceFromField($RECORD_FIELD_MODEL)}

                                {assign var=REFERENCE_MODULE_MODEL value=$UI_TYPE_MODEL->getReferenceModule($REFERENCE_RECORD)}
                                {assign var=REFERENCE_MODULE_NAME value=$REFERENCE_MODULE_MODEL->get('name')}
                                {assign var=REFERENCE_RECORD_ENTIYNAME_LIST value=getEntityName($REFERENCE_MODULE_NAME,$REFERENCE_RECORD)}
                                <a href="index.php?module={$REFERENCE_MODULE_NAME}&view={$REFERENCE_MODULE_MODEL->getDetailViewName()}&record={$REFERENCE_RECORD}"
                                   title="{vtranslate($REFERENCE_MODULE_NAME, $REFERENCE_MODULE_NAME)}"
                                   onclick="if(event.stopPropagation){ldelim}event.stopPropagation();{rdelim}else{ldelim}event.cancelBubble=true;{rdelim}">
                                    {$REFERENCE_RECORD_ENTIYNAME_LIST.$REFERENCE_RECORD}
                                </a>
                            {else if $HEADER_FIELD->getFieldDataType() eq 'owner'}
                                {getOwnerName($RELATED_RECORD->get($RELATED_HEADERNAME))}
                            {else}
                                {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
                            {/if}
                            {if $HEADER_FIELD@last}
                                <div class="pull-right actions">
                                    <span class="actionImages">
                                        <a name="emailsDetailView"><i title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="icon-th-list alignMiddle"></i></a>&nbsp;
                                        {if !($RELATED_RECORD->isSentMail())}
                                            <a name="emailsEditView"><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="icon-pencil alignMiddle"></i></a>	
                                        {/if}
                                        {if $IS_DELETABLE}
                                            <a class="relationDelete"><i title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i></a>
                                        {/if}
                                    </span>
                                </div>
                            {/if}
                        </td>
                    {/foreach}
                </tr>
            {/foreach}
        </table>
    </div>
</div>
</div>
{/strip}
