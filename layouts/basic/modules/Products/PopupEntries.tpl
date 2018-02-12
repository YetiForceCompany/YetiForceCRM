<input type='hidden' id='pageNumber' value="{$PAGE_NUMBER}">
<input type='hidden' id='pageLimit' value="{$PAGING_MODEL->getPageLimit()}">
<input type="hidden" id="noOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}">
<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
<div class="contents-topscroll">
    <div class="topscroll-div">
        &nbsp;
    </div>
</div>
<div class="popupEntriesDiv relatedContents contents-bottomscroll">
	<input type="hidden" value="{$ORDER_BY}" id="orderBy" />
	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder" />
	<input type="hidden" value="Inventory_Popup_Js" id="popUpClassName" />
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<div class="bottomscroll-div">
		<table class="table table-bordered listViewEntriesTable">
			<thead>
				<tr class="listViewHeaders">
					{if $MULTI_SELECT}
						<th class="{$WIDTHTYPE}">
							<input type="checkbox" title="{\App\Language::translate('LBL_SELECT_ALL_CURRENTPAGE')}" class="selectAllInCurrentPage" />
						</th>
					{/if}
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						<th class="{$WIDTHTYPE}">
							<a href="javascript:void(0);" class="listViewHeaderValues" data-nextsortorderval="{if $ORDER_BY eq $LISTVIEW_HEADER->getColumnName()}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->getColumnName()}">{\App\Language::translate($LISTVIEW_HEADER->getFieldLabel(), $MODULE)}
								{if $ORDER_BY eq $LISTVIEW_HEADER->getColumnName()}<img class="sortImage" src="{\App\Layout::getImagePath( $SORT_IMAGE, $MODULE)}">{else}<img class="hide sortingImage" src="{\App\Layout::getImagePath( 'downArrowSmall.png', $MODULE)}">{/if}</a>
						</th>
					{/foreach}
					<th class="{$WIDTHTYPE}">{\App\Language::translate('Action', $MODULE_NAME)}</th>
				</tr>
			</thead>
			{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=popupListView}
				<tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}" data-name='{$LISTVIEW_ENTRY->getName()}' data-info='{\App\Json::encode($LISTVIEW_ENTRY->getRawData())}'
					{if $GETURL neq '' } data-url='{$LISTVIEW_ENTRY->$GETURL()}' {/if}  id="{$MODULE}_popUpListView_row_{$smarty.foreach.popupListView.index+1}">
					{if $MULTI_SELECT}
						<td class="{$WIDTHTYPE}">
							<input class="entryCheckBox" title="{\App\Language::translate('LBL_SELECT_RECORD')}" type="checkbox" />
						</td>
					{/if}
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->getFieldName()}
						<td class="listViewEntryValue {$WIDTHTYPE}">
							{if ($LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->getUIType() eq '4') && $LISTVIEW_ENTRY->isViewable()}
								<a>{$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}</a>
							{else}
								{$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}
							{/if}
						</td>
					{/foreach}
					<td class="listViewEntryValue {$WIDTHTYPE}">
						{if $LISTVIEW_ENTRY->get('subProducts') eq true}
							<a class="subproducts"><b>{\App\Language::translate('Sub Products',$MODULE_NAME)}</b></a>
							<!--<img class="lineItemPopup cursorPointer alignMiddle" data-popup="ProductsPopup" title="{\App\Language::translate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid" src="{\App\Layout::getImagePath('Products.png')}" />-->
						{else} 
							Not a Bunble
						{/if}
					</td>
				</tr>
			{/foreach}
			<td class="listViewEntryValue {$WIDTHTYPE}">
				{if $LISTVIEW_ENTRY->get('subProducts') eq true}
					<a class="subproducts"><b>{\App\Language::translate('Sub Products',$MODULE_NAME)}</b></a>
					<!--<img class="lineItemPopup cursorPointer alignMiddle" data-popup="ProductsPopup" title="{\App\Language::translate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid" src="{\App\Layout::getImagePath('Products.png')}" />-->
				{else} 
					{\App\Language::translate('NOT_A_BUNDLE', $MODULE_NAME)}
				{/if}
			</td>
			</tr>
			{/foreach}
			</table>

			<!--added this div for Temporarily -->
			{if $LISTVIEW_ENTRIES_COUNT eq '0'}
				<div class="row">
					<div class="emptyRecordsDiv">{\App\Language::translate('LBL_NO', $MODULE)} {\App\Language::translate($MODULE, $MODULE)} {\App\Language::translate('LBL_FOUND', $MODULE)}.</div>
				</div>
			{/if}
		</div>
