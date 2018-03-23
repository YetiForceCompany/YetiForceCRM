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
	{if $LISTVIEW_ENTRIES_COUNT neq '0'}
		<div class="clearfix form-actions">
			<button class="cancelLink float-right btn btn-warning" type="reset">{\App\Language::translate('LBL_CANCEL', $MODULE)}</button>
			<button class="btn btn-success addButton select float-right"><i class="fas fa-plus"></i>&nbsp;<strong>{\App\Language::translate('LBL_ADD_TO_PRICEBOOKS',$MODULE)}</strong></button>
		</div>
	{/if}
	<div class="popupEntriesDiv relatedContents contents-bottomscroll">
		<input type="hidden" value="{$ORDER_BY}" id="orderBy" />
		<input type="hidden" value="{$SORT_ORDER}" id="sortOrder" />
		<input type="hidden" value="{$SOURCE_FIELD}" id="sourceField" />
		<input type="hidden" value="{$SOURCE_RECORD}" id="sourceRecord" />
		<input type="hidden" value="{$SOURCE_MODULE}" id="parentModule" />
		<input type="hidden" value="PriceBook_Products_Popup_Js" id="popUpClassName" />
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<div class="bottomscroll-div">
			<table class="table table-bordered listViewEntriesTable">
				<thead>
					<tr class="listViewHeaders">
						<th class="{$WIDTHTYPE}">
							<input type="checkbox" title="{\App\Language::translate('LBL_SELECT_ALL_CURRENTPAGE')}" class="selectAllInCurrentPage" />
						</th>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							<th class="{$WIDTHTYPE}">
								<a class="listViewHeaderValues u-cursor-pointer" data-nextsortorderval="{if $ORDER_BY eq $LISTVIEW_HEADER->getColumnName()}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->getColumnName()}">{\App\Language::translate($LISTVIEW_HEADER->getFieldLabel(), $MODULE_NAME)}
									{if $ORDER_BY eq $LISTVIEW_HEADER->getColumnName()}<img class="sortImage" src="{\App\Layout::getImagePath( $SORT_IMAGE, $MODULE_NAME)}">{else}<img class="d-none sortingImage" src="{\App\Layout::getImagePath( 'downArrowSmall.png', $MODULE_NAME)}">{/if}</a>
							</th>
						{/foreach}
						<th class="listViewHeaderValues noSorting {$WIDTHTYPE}">{\App\Language::translate('LBL_LIST_PRICE',$MODULE)}</th>
					</tr>
				</thead>
				{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=popupListView}
					<tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}" data-name='{$LISTVIEW_ENTRY->getName()}'
						{if $GETURL neq '' } data-url='{$LISTVIEW_ENTRY->$GETURL()}' {/if} id="{$MODULE}_popUpListView_row_{$smarty.foreach.popupListView.index+1}">
						<td class="{$WIDTHTYPE}">
							<input class="entryCheckBox" title="{\App\Language::translate('LBL_SELECT_RECORD')}" type="checkbox" />
						</td>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->getFieldName()}
							<td class="listViewEntryValue {$WIDTHTYPE}">
								{if ($LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->getUIType() eq '4') && $LISTVIEW_ENTRY->isViewable()}
									<a>{$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}</a>
								{else}
									<a>{$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}</a>
								{/if}
							</td>
						{/foreach}
						<td class="listViewEntryValue {$WIDTHTYPE}">
							<div>
								<input type="text" value="{$LISTVIEW_ENTRY->get('unit_price')}" name="listPrice" class="invisible col-md-10 zeroPaddingAndMargin form-control" data-validation-engine="validate[funcCall[Vtiger_Currency_Validator_Js.invokeValidation]]"
									   data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}'/>
							</div>
						</td>
					</tr>
				{/foreach}
			</table>
		</div>
		<!--added this div for Temporarily -->
		{if $LISTVIEW_ENTRIES_COUNT eq '0'}
			<div>
				<div class="emptyRecordsDiv">{\App\Language::translate('LBL_RECORDS_NO_FOUND')}</div>
			</div>
		{/if}
	</div>
{/strip}
