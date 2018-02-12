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
		<input type="hidden" value="{$ORDER_BY}" id="orderBy" />
		<input type="hidden" value="{$SORT_ORDER}" id="sortOrder" />
		<input type="hidden" value="{$SOURCE_FIELD}" id="sourceField" />
		<input type="hidden" value="{$SOURCE_RECORD}" id="sourceRecord" />
		<input type="hidden" value="{$SOURCE_MODULE}" id="parentModule" />
		<input type="hidden" value="Product_PriceBooks_Popup_Js" id="popUpClassName" />
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<div class="bottomscroll-div">
			<table class="table table-bordered listViewEntriesTable">
				<thead>
					<tr class="listViewHeaders">
						<th class="{$WIDTHTYPE}">
							<input type="checkbox" title="{\App\Language::translate('LBL_SELECT_ALL_CURRENTPAGE')}"  class="selectAllInCurrentPage" />
						</th>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							<th class="{$WIDTHTYPE}">
								<a class="listViewHeaderValues cursorPointer" data-nextsortorderval="{if $ORDER_BY eq $LISTVIEW_HEADER->getColumnName()}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->getColumnName()}">{\App\Language::translate($LISTVIEW_HEADER->getFieldLabel(), $MODULE_NAME)}
									{if $ORDER_BY eq $LISTVIEW_HEADER->getColumnName()}<img class="sortImage" src="{\App\Layout::getImagePath( $SORT_IMAGE, $MODULE_NAME)}">{else}<img class="hide sortingImage" src="{\App\Layout::getImagePath( 'downArrowSmall.png', $MODULE_NAME)}">{/if}</a>
							</th>
						{/foreach}
						<th class="listViewHeaderValues noSorting {$WIDTHTYPE}">{\App\Language::translate('LBL_UNIT_PRICE',$MODULE_NAME)}</th>
						<th class="listViewHeaderValues noSorting {$WIDTHTYPE}">{\App\Language::translate('LBL_LIST_PRICE',$MODULE_NAME)}</th>
					</tr>
				</thead>
				{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=popupListView}
					<tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}" data-name='{$LISTVIEW_ENTRY->getName()}' data-currency='{$LISTVIEW_ENTRY->get('currency_id')}'
						{if $GETURL neq '' } data-url='{$LISTVIEW_ENTRY->$GETURL()}' {/if} id="{$MODULE}_popUpListView_row_{$smarty.foreach.popupListView.index+1}">
						<td class="{$WIDTHTYPE}">
							<input class="entryCheckBox" title="{\App\Language::translate('LBL_SELECT_RECORD')}" type="checkbox" />
						</td>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->getFieldName()}
							<td class="listViewEntryValue {$WIDTHTYPE}">
								{if ($LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->getUIType() eq '4') && $LISTVIEW_ENTRY->isViewable()}
									<a {if $LISTVIEW_HEADER->isNameField() eq true}class="modCT_{$MODULE}"{/if} href="{$LISTVIEW_ENTRY->getDetailViewUrl()}">
										{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADERNAME)}
									</a>
								{else}
									{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADERNAME)}
								{/if}
							</td>
						{/foreach}
						<td class="listViewEntryValue {$WIDTHTYPE}">
							<a>{$LISTVIEW_ENTRY->get('unit_price')}</a>
						</td>
						<td class="listViewEntryValue {$WIDTHTYPE}">
							<input type="text" value="{\App\Purifier::encodeHtml($LISTVIEW_ENTRY->get('unit_price'))}" name="listPrice" class="invisible col-md-10 zeroPaddingAndMargin form-control" data-validation-engine="validate[funcCall[Vtiger_Currency_Validator_Js.invokeValidation]]"
								   data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}'/>
						</td>
					</tr>
				{/foreach}
			</table>
		</div>
		<!--added this div for Temporarily -->
		{if $LISTVIEW_ENTRIES_COUNT eq '0'}
			<div class="row">
				<div class="emptyRecordsDiv">{\App\Language::translate('LBL_RECORDS_NO_FOUND')}.{if $IS_MODULE_EDITABLE} <a href="{$MODULE_MODEL->getCreateRecordUrl()}">{\App\Language::translate('LBL_CREATE_SINGLE_RECORD')}</a>.{/if}</div>
			</div>
		{/if}
	</div>
	<div class="clearfix form-actions pushDown">
		<button class="cancelLink float-right btn btn-warning" type="button">{\App\Language::translate('LBL_CANCEL', $MODULE_NAME)}</button>
		<button class="btn btn-success addButton select float-right"><i class="fas fa-plus"></i>&nbsp;<strong>{\App\Language::translate('LBL_ADD_TO',$MODULE_NAME)}&nbsp;{\App\Language::translate($SOURCE_MODULE, $SOURCE_MODULE)}</strong></button>
	</div>
{/strip}
