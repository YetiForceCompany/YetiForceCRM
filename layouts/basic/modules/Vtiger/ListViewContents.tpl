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
	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type="hidden" id="listMaxEntriesMassEdit" value="{vglobal('listMaxEntriesMassEdit')}" />
	<input type="hidden" id="autoRefreshListOnChange" value="{AppConfig::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}" />
	<input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'>
	<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
	<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">

	{include file=vtemplate_path('ListViewAlphabet.tpl',$MODULE_NAME)}
	<div class="clearfix"></div>
	<div id="selectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="selectAllMsg">{vtranslate('LBL_SELECT_ALL',$MODULE)}&nbsp;{vtranslate($MODULE ,$MODULE)}&nbsp;(<span id="totalRecordsCount"></span>)</a></strong>
	</div>
	<div id="deSelectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="deSelectAllMsg">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
	</div>
	<div class="contents-topscroll noprint stick" data-position="top">
		<div class="topscroll-div"></div>
	</div>
	<div class="listViewEntriesDiv contents-bottomscroll">
		<div class="bottomscroll-div">
			<input type="hidden" value="{$ORDER_BY}" id="orderBy">
			<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
			<div class="listViewLoadingImageBlock hide modal noprint" id="loadingListViewModal">
				<img class="listViewLoadingImage" src="{vimage_path('loading.gif')}" alt="no-image" title="{\App\Language::translate('LBL_LOADING')}"/>
				<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
			</div>
			{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
			<table class="table table-bordered listViewEntriesTable {$WIDTHTYPE}">
				<thead>
					<tr class="listViewHeaders">
						<th>
							<input type="checkbox" id="listViewEntriesMainCheckBox" title="{vtranslate('LBL_SELECT_ALL')}" />
						</th>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							<th {if !empty($LISTVIEW_HEADER->get('maxwidthcolumn'))}style="width:{$LISTVIEW_HEADER->get('maxwidthcolumn')}%"{/if} {if $LISTVIEW_HEADER@last}colspan="2"{/if} class="noWrap {if $COLUMN_NAME eq $LISTVIEW_HEADER->get('column')}columnSorted{/if}">
								<a href="javascript:void(0);" class="listViewHeaderValues pull-left" {if $LISTVIEW_HEADER->isListviewSortable()}data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}"{/if} data-columnname="{$LISTVIEW_HEADER->get('column')}">{vtranslate($LISTVIEW_HEADER->get('label'), $MODULE)}
									&nbsp;&nbsp;{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('column')}<span class="{$SORT_IMAGE}"></span>{/if}</a>
									{if $LISTVIEW_HEADER->getFieldDataType() eq 'tree'}
									<div class='pull-left'>
										<span class="pull-right popoverTooltip delay0"  data-placement="top" data-original-title="{vtranslate($LISTVIEW_HEADER->get('label'), $MODULE)}" 
											  data-content="{vtranslate('LBL_SEARCH_IN_SUBCATEGORIES',$MODULE_NAME)}">
											<span class="glyphicon glyphicon-info-sign"></span>
										</span>
										<input type="checkbox" id="searchInSubcategories" title="{vtranslate('LBL_SEARCH_IN_SUBCATEGORIES',$MODULE_NAME)}" name="searchInSubcategories" class="pull-right" value="1" data-columnname="{$LISTVIEW_HEADER->get('column')}" {if $SEARCH_DETAILS[$LISTVIEW_HEADER->getName()]['specialOption']} checked {/if}>
									</div>
								{/if}
							</th>
						{/foreach}
					</tr>
				</thead>
				{if $MODULE_MODEL->isQuickSearchEnabled()}
					<tr>
						<td class="listViewSearchTd">
							<a class="btn btn-default" data-trigger="listSearch" href="javascript:void(0);"><span class="glyphicon glyphicon-search"></span></a>
						</td>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							<td>
								{assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
								{if isset($SEARCH_DETAILS[$LISTVIEW_HEADER->getName()])}
									{assign var=SEARCH_INFO value=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()]}
								{else}
									{assign var=SEARCH_INFO value=[]}
								{/if}
								{include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$MODULE_NAME)
                    FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL}
							</td>
						{/foreach}
						<td>
							<a class="btn btn-default" href="index.php?view=List&module={$MODULE}" >
								<span class="glyphicon glyphicon-remove"></span>
							</a>
						</td>
					</tr>
				{/if}
				{assign var="LISTVIEW_HEADER_COUNT" value=count($LISTVIEW_HEADERS)}
				{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
					{assign var="RECORD_ID" value=$LISTVIEW_ENTRY->getId()}
					<tr class="listViewEntries" data-id='{$LISTVIEW_ENTRY->getId()}' data-recordUrl='{$LISTVIEW_ENTRY->getDetailViewUrl()}' id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}" {if $LISTVIEW_ENTRY->colorList}style="background-color: {$LISTVIEW_ENTRY->colorList['background']};color: {$LISTVIEW_ENTRY->colorList['text']};"{/if}>
						<td class="{$WIDTHTYPE} noWrap leftRecordActions">
							{include file=vtemplate_path('ListViewLeftSide.tpl',$MODULE_NAME)}
						</td>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS name=listHeaderForeach}
							{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
							<td class="listViewEntryValue noWrap {$WIDTHTYPE}" data-field-type="{$LISTVIEW_HEADER->getFieldDataType()}" data-raw-value="{Vtiger_Util_Helper::toSafeHTML($LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME))}">
								{if ($LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->get('uitype') eq '4') and $MODULE_MODEL->isListViewNameFieldNavigationEnabled() eq true }
									<a {if $LISTVIEW_HEADER->isNameField() eq true}class="moduleColor_{$MODULE}"{/if} href="{$LISTVIEW_ENTRY->getDetailViewUrl()}">
										{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADERNAME)}
									</a>
								{else}
									{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADERNAME)}
								{/if}
							</td>
						{/foreach}
						<td class="{$WIDTHTYPE} noWrap rightRecordActions">
							{include file=vtemplate_path('ListViewRightSide.tpl',$MODULE_NAME)}
						</td>
					</tr>
				{/foreach}
			</table>

			<!--added this div for Temporarily -->
			{if $LISTVIEW_ENTRIES_COUNT eq '0'}
				<table class="emptyRecordsDiv">
					<tbody>
						<tr>
							<td>
								{vtranslate('LBL_RECORDS_NO_FOUND')}.{if $IS_MODULE_EDITABLE} <a href="{$MODULE_MODEL->getCreateRecordUrl()}">{vtranslate('LBL_CREATE_SINGLE_RECORD')}</a>{/if}
							</td>
						</tr>
					</tbody>
				</table>
			{/if}
		</div>
	</div>
{/strip}

