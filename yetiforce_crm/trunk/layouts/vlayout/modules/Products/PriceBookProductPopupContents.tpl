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
<div class="contents-topscroll">
    <div class="topscroll-div">
        &nbsp;
    </div>
</div>
<div class="popupEntriesDiv relatedContents contents-bottomscroll">
	<input type="hidden" value="{$ORDER_BY}" id="orderBy">
	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
	<input type="hidden" value="{$SOURCE_FIELD}" id="sourceField">
	<input type="hidden" value="{$SOURCE_RECORD}" id="sourceRecord">
	<input type="hidden" value="{$SOURCE_MODULE}" id="parentModule">
	<input type="hidden" value="PriceBook_Products_Popup_Js" id="popUpClassName"/>
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<div class="bottomscroll-div">
		<table class="table table-bordered listViewEntriesTable">
			<thead>
				<tr class="listViewHeaders">
					<th class="{$WIDTHTYPE}">
						<input type="checkbox"  class="selectAllInCurrentPage" />
					</th>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
					<th class="{$WIDTHTYPE}">
						<a class="listViewHeaderValues cursorPointer" data-nextsortorderval="{if $ORDER_BY eq $LISTVIEW_HEADER->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('column')}">{vtranslate($LISTVIEW_HEADER->get('label'), $MODULE_NAME)}
							{if $ORDER_BY eq $LISTVIEW_HEADER->get('column')}<img class="sortImage" src="{vimage_path( $SORT_IMAGE, $MODULE_NAME)}">{else}<img class="hide sortingImage" src="{vimage_path( 'downArrowSmall.png', $MODULE_NAME)}">{/if}</a>
					</th>
					{/foreach}
					<th class="listViewHeaderValues noSorting {$WIDTHTYPE}">{vtranslate('LBL_LIST_PRICE',$MODULE)}</th>
				</tr>
			</thead>
			{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=popupListView}
			<tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}" data-name='{$LISTVIEW_ENTRY->getName()}'
				{if $GETURL neq '' } data-url='{$LISTVIEW_ENTRY->$GETURL()}' {/if} id="{$MODULE}_popUpListView_row_{$smarty.foreach.popupListView.index+1}">
				<td class="{$WIDTHTYPE}">
					<input class="entryCheckBox" type="checkbox" />
				</td>
				{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
				{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
				<td class="listViewEntryValue {$WIDTHTYPE}">
					{if $LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->get('uitype') eq '4'}
						<a>{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}</a>
					{else}
						<a>{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}</a>
					{/if}
				</td>
				{/foreach}
				<td class="listViewEntryValue {$WIDTHTYPE}">
					<div class="row-fluid">
						<input type="text" value="{$LISTVIEW_ENTRY->get('unit_price')}" name="listPrice" class="invisible span10 zeroPaddingAndMargin" data-validation-engine="validate[required,funcCall[Vtiger_Currency_Validator_Js.invokeValidation]]"
							   data-decimal-seperator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-seperator='{$USER_MODEL->get('currency_grouping_separator')}'/>
					</div>
				</td>
			</tr>
			{/foreach}
		</table>
	</div>
	<!--added this div for Temporarily -->
	{if $LISTVIEW_ENTRIES_COUNT eq '0'}
		<div class="row-fluid">
			<div class="emptyRecordsDiv">{vtranslate('LBL_NO', $MODULE_NAME)} {vtranslate($MODULE_NAME, $MODULE_NAME)} {vtranslate('LBL_FOUND', $MODULE_NAME)}.</div>
		</div>
	{/if}
</div>
{if $LISTVIEW_ENTRIES_COUNT neq '0'}
    <div class="clearfix form-actions" style="border: 1px solid #DDDDDD;">
	<a class="cancelLink pull-right">{vtranslate('LBL_CANCEL', $MODULE)}</a>
	<button class="btn addButton select pull-right"><i class="icon-plus"></i>&nbsp;<strong>{vtranslate('LBL_ADD_TO_PRICEBOOKS',$MODULE)}</strong></button>
</div>
{/if}
{/strip}