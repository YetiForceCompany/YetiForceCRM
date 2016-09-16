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
		<input type="hidden" value="{$ORDER_BY}" id="orderBy">
		<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
		{if $SOURCE_MODULE eq "Emails"}
			<input type="hidden" value="Vtiger_EmailsRelatedModule_Popup_Js" id="popUpClassName"/>
		{/if}
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<div class="bottomscroll-div">
			<table class="table table-bordered listViewEntriesTable">
				<thead>
					<tr class="listViewHeaders">
						{if $MULTI_SELECT}
							<th class="{$WIDTHTYPE}">
								<input type="checkbox" title="{vtranslate('LBL_SELECT_ALL_CURRENTPAGE')}" class="selectAllInCurrentPage" />
							</th>
						{/if}
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							<th class="{$WIDTHTYPE}">
								<a href="javascript:void(0);" class="listViewHeaderValues {if $LISTVIEW_HEADER->get('name') eq 'listprice'} noSorting {/if}" data-nextsortorderval="{if $ORDER_BY eq $LISTVIEW_HEADER->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('column')}">{vtranslate($LISTVIEW_HEADER->get('label'), $MODULE_NAME)}
									{if $ORDER_BY eq $LISTVIEW_HEADER->get('column')}<img class="sortImage" alt="{vtranslate('LBL_SORT_ASCENDING')}" src="{vimage_path( $SORT_IMAGE, $MODULE_NAME)}">{else}<img class="hide sortingImage" alt="{vtranslate('LBL_SORT_DESCENDING')}" src="{vimage_path( 'downArrowSmall.png', $MODULE_NAME)}">{/if}</a>
							</th>
						{/foreach}
						{if $POPUPTYPE == 2}
							<th class="{$WIDTHTYPE}"></th>
							{/if}
					</tr>
				</thead>
				<tbody>
					{if $POPUPTYPE == 2}
						<tr>
							{if $MULTI_SELECT}
								<td class="{$WIDTHTYPE}"></td>
							{/if}
							{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
								<td class="{$WIDTHTYPE}">
									{assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
									{include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$MODULE_NAME) FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()] USER_MODEL=$USER_MODEL}
								</td>
							{/foreach}
							<td class="{$WIDTHTYPE}"><button class="btn btn-default" data-trigger="listSearch">{vtranslate('LBL_SEARCH', $MODULE_NAME )}</button></td>
							{/if}
					</tr>

					{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=popupListView}
						<tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}" data-name='{$LISTVIEW_ENTRY->getName()}' data-info='{\includes\utils\Json::encode($LISTVIEW_ENTRY->getRawData())}'
							{if $GETURL neq '' } data-url='{$LISTVIEW_ENTRY->$GETURL()}' {/if}  id="{$MODULE_NAME}_popUpListView_row_{$smarty.foreach.popupListView.index+1}">
							{if $MULTI_SELECT}
								<td class="{$WIDTHTYPE}">
									<input class="entryCheckBox" title="{vtranslate('LBL_SELECT_RECORD')}" type="checkbox" />
								</td>
							{/if}
							{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
								{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
								<td class="listViewEntryValue {$WIDTHTYPE}">
									{if $LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->get('uitype') eq '4'}
										<a>{if $LISTVIEW_HEADER->getFieldDataType() eq 'sharedOwner' || $LISTVIEW_HEADER->getFieldDataType() eq 'boolean' || $LISTVIEW_HEADER->getFieldDataType() eq 'tree'}
											{$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}
										{else}
											{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
										{/if}</a>
											{else if $LISTVIEW_HEADERNAME eq 'listprice'}
												{CurrencyField::convertToUserFormat($LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME), null, true, true)}
												{else}
													{if $LISTVIEW_HEADER->getFieldDataType() eq 'double'}
														{\vtlib\Functions::formatDecimal($LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME))}
													{else if $LISTVIEW_HEADER->getFieldDataType() eq 'sharedOwner' || $LISTVIEW_HEADER->getFieldDataType() eq 'boolean' || $LISTVIEW_HEADER->getFieldDataType() eq 'tree'}
														{$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}
													{else}
														{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
													{/if}
													{/if}
												</td>
												{/foreach}
													{if $POPUPTYPE == 2}
														<td class="{$WIDTHTYPE}"></td>
													{/if}
												</tr>
												{/foreach}
												</tbody>
											</table>
										</div>

										<!--added this div for Temporarily -->
										{if $LISTVIEW_ENTRIES_COUNT eq '0'}
											<div class="">
												<div class="emptyRecordsDiv">{vtranslate('LBL_NO_RELATED_RECORDS_FOUND', $MODULE)}.</div>
											</div>
										{/if}
	</div>
{/strip}
