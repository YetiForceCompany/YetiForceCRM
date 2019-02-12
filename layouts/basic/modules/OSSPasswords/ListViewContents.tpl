{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}"/>
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}"/>
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}"/>
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}"/>
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}"/>
	<input type="hidden" id="listMaxEntriesMassEdit" value="{\AppConfig::main('listMaxEntriesMassEdit')}"/>
	<input type="hidden" id="autoRefreshListOnChange"
		   value="{AppConfig::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}"/>
	<input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'/>
	<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id="pageLimit"/>
	<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries"/>
	{include file=\App\Layout::getTemplatePath('ListViewAlphabet.tpl', $MODULE_NAME)}
	<div class="clearfix"></div>
	<div id="selectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="selectAllMsg">{\App\Language::translate('LBL_SELECT_ALL',$MODULE)}
				&nbsp;{\App\Language::translate($MODULE ,$MODULE)}&nbsp;(<span
						id="totalRecordsCount"></span>)</a></strong>
	</div>
	<div id="deSelectAllMsgDiv" class="alert-block msgDiv noprint">
		<strong><a id="deSelectAllMsg">{\App\Language::translate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
	</div>
	<div class="listViewEntriesDiv u-overflow-scroll-xsm-down">
		<input type="hidden" value="{$ORDER_BY}" id="orderBy"/>
		<input type="hidden" value="{$SORT_ORDER}" id="sortOrder"/>
		<div class="listViewLoadingImageBlock d-none modal noprint" id="loadingListViewModal">
			<img class="listViewLoadingImage" src="{\App\Layout::getImagePath('loading.gif')}" alt="no-image"
				 title="{\App\Language::translate('LBL_LOADING')}"/>
			<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
		</div>
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		<table class="table table-bordered listViewEntriesTable {$WIDTHTYPE} {if $VIEW_MODEL && !$VIEW_MODEL->isEmpty('entityState')}listView{$VIEW_MODEL->get('entityState')}{/if} js-fixed-thead" data-js="floatThead">
			<thead>
			<tr class="listViewHeaders">
				<th>
					<input type="checkbox" id="listViewEntriesMainCheckBox"
						   title="{\App\Language::translate('LBL_SELECT_ALL')}"/>
				</th>
				{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
					<th {if $LISTVIEW_HEADER@last}colspan="2"{/if}
						class="noWrap {if $COLUMN_NAME eq $LISTVIEW_HEADER->getColumnName()}columnSorted{/if}">
						<a href="javascript:void(0);" class="listViewHeaderValues js-listview_header float-left"
						   data-js="click"
						   {if $LISTVIEW_HEADER->isListviewSortable()}data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->getColumnName()}{$NEXT_SORT_ORDER}{else}ASC{/if}"{/if}
						   data-columnname="{$LISTVIEW_HEADER->getColumnName()}">
							{if $LISTVIEW_HEADER->getModuleName() !== $MODULE_NAME && !empty($LISTVIEW_HEADER->get('source_field_name'))}
								{\App\Language::translate(Vtiger_Field_Model::getInstance($LISTVIEW_HEADER->get('source_field_name'),$MODULE_MODEL)->getFieldLabel(), $MODULE_NAME)}&nbsp;-&nbsp;
							{/if}
							{\App\Language::translate($LISTVIEW_HEADER->getFieldLabel(), $LISTVIEW_HEADER->getModuleName())}
							&nbsp;&nbsp;{if $COLUMN_NAME eq $LISTVIEW_HEADER->getColumnName()}<span
							class="{$SORT_IMAGE}"></span>{/if}</a>
						{if $LISTVIEW_HEADER->getFieldDataType() eq 'tree' || $LISTVIEW_HEADER->getFieldDataType() eq 'categoryMultipicklist'}
							{assign var=LISTVIEW_HEADER_NAME value=$LISTVIEW_HEADER->getName()}
							<div class="float-left">
									<span class="float-right js-popover-tooltip delay0" data-js="popover"
										  data-placement="top"
										  data-original-title="{\App\Language::translate($LISTVIEW_HEADER->getFieldLabel(), $MODULE)}"
										  data-content="{\App\Language::translate('LBL_SEARCH_IN_SUBCATEGORIES',$MODULE_NAME)}">
										<span class="fas fa-info-circle"></span>
									</span>
								<input name="searchInSubcategories" class="float-right searchInSubcategories" value="1" type="checkbox" id="searchInSubcategories{$LISTVIEW_HEADER_NAME}"
									   title="{\App\Language::translate('LBL_SEARCH_IN_SUBCATEGORIES',$MODULE_NAME)}"
									   data-columnname="{$LISTVIEW_HEADER->getColumnName()}" {if !empty($SEARCH_DETAILS[$LISTVIEW_HEADER_NAME]['specialOption'])} checked {/if} />
							</div>
						{/if}
					</th>
				{/foreach}
			</tr>
			{if $MODULE_MODEL->isQuickSearchEnabled()}
				<tr class="bg-white">
					<td class="listViewSearchTd">
						<div class="flexWrapper">
							<a class="btn btn-light" role="button" data-trigger="listSearch" href="javascript:void(0);">
								<span class="fas fa-search" title="{\App\Language::translate('LBL_SEARCH')}"></span>
							</a>
							<a class="btn btn-light float-right listRemoveBtn" role="button"
							   href="index.php?view={$VIEW}&module={$MODULE}">
								<span class="fas fa-times"
									  title="{\App\Language::translate('LBL_CLEAR_SEARCH')}"></span>
							</a>
						</div>
					</td>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						<td>
							{assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
							{if !empty($LISTVIEW_HEADER->get('source_field_name'))}
								{assign var=LISTVIEW_HEADER_NAME value="`$LISTVIEW_HEADER->getName()`:`$LISTVIEW_HEADER->getModuleName()`:`$LISTVIEW_HEADER->get('source_field_name')`"}
							{else}
								{assign var=LISTVIEW_HEADER_NAME value=$LISTVIEW_HEADER->getName()}
							{/if}
							{if isset($SEARCH_DETAILS[$LISTVIEW_HEADER_NAME])}
								{assign var=SEARCH_INFO value=$SEARCH_DETAILS[$LISTVIEW_HEADER_NAME]}
							{else}
								{assign var=SEARCH_INFO value=[]}
							{/if}
							{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $MODULE_NAME)
							FIELD_MODEL=$LISTVIEW_HEADER SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL}
						</td>
					{/foreach}
					<td class="reducePadding"></td>
				</tr>
			{/if}
			</thead>
			{assign var="LISTVIEW_HEADER_COUNT" value=count($LISTVIEW_HEADERS)}
			{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
				{if array_key_exists('password',$LISTVIEW_HEADERS)}
					{$PASS_ID="{$LISTVIEW_ENTRY->get('id')}"}
				{/if}
				{assign var="RECORD_ID" value=$LISTVIEW_ENTRY->getId()}
				{assign var="RECORD_COLORS" value=$LISTVIEW_ENTRY->getListViewColor()}
				<tr class="listViewEntries" data-id='{$LISTVIEW_ENTRY->getId()}'
					data-recordUrl='{$LISTVIEW_ENTRY->getDetailViewUrl()}'
					id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
					<td class="{$WIDTHTYPE} noWrap leftRecordActions"
						{if !empty($RECORD_COLORS['leftBorder'])}style="border-left-color: {$RECORD_COLORS['leftBorder']};"{/if}>
						{include file=\App\Layout::getTemplatePath('ListViewLeftSide.tpl', $MODULE_NAME)}
					</td>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS name=listHeaderForeach}
						{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->getFieldName()}
						<td class="listViewEntryValue noWrap {$WIDTHTYPE}"
							data-field-type="{$LISTVIEW_HEADER->getFieldDataType()}" {if $LISTVIEW_HEADERNAME eq 'password'} id="{$PASS_ID}" {/if} {if $smarty.foreach.listHeaderForeach.iteration eq $LISTVIEW_HEADER_COUNT}colspan="2"{/if}>
							{if empty($LISTVIEW_HEADER->get('source_field_name')) && ($LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->getUIType() eq '4') && $MODULE_MODEL->isListViewNameFieldNavigationEnabled() eq true && $LISTVIEW_ENTRY->isViewable()}
								<a {if $LISTVIEW_HEADER->isNameField() eq true}class="modCT_{$MODULE}"{/if}
								   href="{$LISTVIEW_ENTRY->getDetailViewUrl()}">
									{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADER)}
								</a>
							{else}
								{if $LISTVIEW_HEADERNAME eq 'password'}
									{str_repeat('*', 10)}
								{else}
									{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADER)}
								{/if}
							{/if}
						</td>
					{/foreach}
				</tr>
			{/foreach}
		</table>

		<!--added this div for Temporarily -->
		{if $LISTVIEW_ENTRIES_COUNT eq '0'}
			<table class="emptyRecordsDiv">
				<tbody>
				<tr>
					<td>
						{\App\Language::translate('LBL_RECORDS_NO_FOUND')}.{if $IS_MODULE_EDITABLE} <a
						href="{$MODULE_MODEL->getCreateRecordUrl()}">{\App\Language::translate('LBL_CREATE_SINGLE_RECORD')}</a>{/if}
					</td>
				</tr>
				</tbody>
			</table>
		{/if}
	</div>
{/strip}
