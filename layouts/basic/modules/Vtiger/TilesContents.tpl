{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-TilesContents -->
	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type="hidden" id="listMaxEntriesMassEdit" value="{\App\Config::main('listMaxEntriesMassEdit')}" />
	<input type="hidden" id="autoRefreshListOnChange" value="{App\Config::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}" />
	<input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'>
	<input type="hidden" id="selectedIds" name="selectedIds" />
	<input type="hidden" id="recordsCount" value="" />
	<input type="hidden" id="excludedIds" name="excludedIds" />
	<input type="hidden" id="pageLimit" value="{$PAGING_MODEL->getPageLimit()}">
	<input type="hidden" id="noOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}">
	<input type="hidden" class="js-empty-fields" value="{\App\Purifier::encodeHtml(\App\Json::encode($LOCKED_EMPTY_FIELDS))}" data-js="value" />
	{include file=\App\Layout::getTemplatePath('ListViewAlphabet.tpl', $MODULE_NAME)}
	<div class="clearfix"></div>
	<div class="listViewEntriesDiv u-overflow-scroll-non-desktop">
		<input type="hidden" id="orderBy" value="{\App\Purifier::encodeHtml(\App\Json::encode($ORDER_BY))}" />
		<input type="hidden" id="search_params" value="{\App\Purifier::encodeHtml(\App\Json::encode($SEARCH_PARAMS))}" />
		<input type="hidden" class="js-custom-view-adv-cond" value="{\App\Purifier::encodeHtml(\App\Json::encode($ADVANCED_CONDITIONS))}" data-js="value" />
		<div class="listViewLoadingImageBlock d-none modal noprint" id="loadingListViewModal">
			<img class="listViewLoadingImage" src="{\App\Layout::getImagePath('loading.gif')}" alt="no-image" title="{\App\Language::translate('LBL_LOADING')}" />
			<p class="listViewLoadingMsg">{\App\Language::translate('LBL_LOADING_LISTVIEW_CONTENTS')}........</p>
		</div>
		<table class="table tableBorderHeadBody listViewEntriesTable {$WIDTHTYPE} {if $VIEW_MODEL && !$VIEW_MODEL->isEmpty('entityState')}listView{$VIEW_MODEL->get('entityState')}{/if} js-fixed-thead" data-js="floatThead">
			<thead>
				<tr class="{if isset($CUSTOM_VIEWS) && $CUSTOM_VIEWS|@count gt 0}c-tab--border-active{/if} listViewHeaders">
					<th class="p-2">
						<div class="d-flex align-items-center">
							<label class="sr-only" for="listViewEntriesMainCheckBox">{\App\Language::translate('LBL_SELECT_ALL')}</label>
							<input type="checkbox" id="listViewEntriesMainCheckBox" title="{\App\Language::translate('LBL_SELECT_ALL')}" />
							{if $MODULE_MODEL->isAdvSortEnabled()}
								<button type="button" class="ml-2 btn {if !empty($ORDER_BY)}btn-info{else}btn-outline-info{/if} btn-xs js-show-modal"
									data-url="index.php?view=SortOrderModal&module={$MODULE_NAME}"
									data-modalid="sortOrderModal-{\App\Layout::getUniqueId()}" data-js="click">
									<span class="fas fa-sort"></span>
								</button>
							{/if}
							{if $MODULE_MODEL->isCustomViewAdvCondEnabled()}
								<button type="button" class="ml-2 btn {if !empty($ADVANCED_CONDITIONS['relationId']) || isset($ADVANCED_CONDITIONS['relationColumns'])}btn-primary{else}btn-outline-primary{/if} btn-xs js-custom-view-adv-cond-modal" title="{\App\Language::translate('LBL_CUSTOM_VIEW_ADV_COND')}" data-js="click">
									<span class="fas fa-cog"></span>
								</button>
							{/if}
							<div class="js-list-reload" data-js="click"></div>
						</div>
					</th>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						{assign var=LISTVIEW_HEADER_NAME value=$LISTVIEW_HEADER->getFullName()}
						<th class="noWrap p-2 u-table-column__before-block
						{if !empty($LISTVIEW_HEADER->get('maxwidthcolumn'))} u-table-column__vw-{$LISTVIEW_HEADER->get('maxwidthcolumn')}{/if}
						{if isset($ORDER_BY[$LISTVIEW_HEADER_NAME])} columnSorted{/if}">
							<span class="listViewHeaderValues float-left {if $LISTVIEW_HEADER->isListviewSortable()} js-change-order u-cursor-pointer{/if}"
								data-nextsortorderval="{if isset($ORDER_BY[$LISTVIEW_HEADER_NAME]) && $ORDER_BY[$LISTVIEW_HEADER_NAME] eq \App\Db::ASC}{\App\Db::DESC}{else}{\App\Db::ASC}{/if}"
								data-columnname="{$LISTVIEW_HEADER_NAME}"
								data-js="click">
								{$LISTVIEW_HEADER->getFullLabelTranslation($MODULE_MODEL)}
								{if isset($ORDER_BY[$LISTVIEW_HEADER_NAME])}
									&nbsp;&nbsp;<span class="fas {if $ORDER_BY[$LISTVIEW_HEADER_NAME] eq \App\Db::DESC}fa-chevron-down{else}fa-chevron-up{/if}"></span>
								{/if}
							</span>
							{if $LISTVIEW_HEADER->getFieldDataType() eq 'tree' || $LISTVIEW_HEADER->getFieldDataType() eq 'categoryMultipicklist'}
								{assign var=LISTVIEW_HEADER_NAME value=$LISTVIEW_HEADER->getName()}
								<div class="d-flex align-items-center">
									<input name="searchInSubcategories" value="1" type="checkbox" class="searchInSubcategories mr-1 ml-1" id="searchInSubcategories{$LISTVIEW_HEADER_NAME}" title="{\App\Language::translate('LBL_SEARCH_IN_SUBCATEGORIES',$MODULE_NAME)}" data-columnname="{$LISTVIEW_HEADER->getColumnName()}" {if !empty($SEARCH_DETAILS[$LISTVIEW_HEADER_NAME]['specialOption'])} checked {/if}>
									<span class="js-popover-tooltip delay0" data-js="popover" data-placement="top" data-original-title="{\App\Language::translate($LISTVIEW_HEADER->getFieldLabel(), $MODULE)}"
										data-content="{\App\Language::translate('LBL_SEARCH_IN_SUBCATEGORIES',$MODULE_NAME)}">
										<span class="fas fa-info-circle"></span>
									</span>
								</div>
							{/if}
						</th>
					{/foreach}
					<th class="reducePadding"></th>
				</tr>
				{if $MODULE_MODEL->isQuickSearchEnabled()}
					<tr class="bg-white">
						<td class="listViewSearchTd">
							<div class="flexWrapper">
								<a class="btn btn-light" role="button" data-trigger="listSearch" href="javascript:void(0);">
									<span class="fas fa-search" title="{\App\Language::translate('LBL_SEARCH')}"></span>
								</a>
								<a class="btn btn-light float-right listRemoveBtn" role="button" href="index.php?view={$VIEW}&module={$MODULE}">
									<span class="fas fa-times" title="{\App\Language::translate('LBL_CLEAR_SEARCH')}"></span>
								</a>
							</div>
						</td>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							<td class="pl-1">
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
								{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $MODULE_NAME) FIELD_MODEL=$LISTVIEW_HEADER SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL}
							</td>
						{/foreach}
						<td class="reducePadding"></td>
					</tr>
				{/if}
			</thead>
			<tbody>
			</tbody>
		</table>
		<div class="row m-0 mt-1 js-tiles-container h-100">
			{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
				{assign var="RECORD_ID" value=$LISTVIEW_ENTRY->getId()}
				<div class="col-md-{$TILE_COLUMN_SIZE} col-sm-12 p-1 border-0 c-tile-container js-tile-container" data-recordUrl='{$LISTVIEW_ENTRY->getDetailViewUrl()}'>
					<div class="card js-tile-card justify-content-center">
						<div class="w-100 h-100 c-tile-body border border-light bg-light">
							<div class="card-footer p-0 border-0 justify-content-center">
								{include file=\App\Layout::getTemplatePath('TilesLeftSide.tpl', $MODULE_NAME)}
							</div>
							<div class="card-body js-card-body justify-content-center h-100">
								{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS name=listHeaderForeach}
									{assign var="FIELD_DATA_TYPE" value=$LISTVIEW_HEADER->getFieldDataType() }
									{if $smarty.foreach.listHeaderForeach.first}
										<h5 class="card-title text-center c-tile-value {if in_array($FIELD_DATA_TYPE,['multiImage', 'image'])} c-tile-image {/if}"><span class=" listViewEntryValue noWrap text-muted" data-field-type="{$FIELD_DATA_TYPE}">
												{if empty($LISTVIEW_HEADER->get('source_field_name')) && ($LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->getUIType() eq '4') && $MODULE_MODEL->isListViewNameFieldNavigationEnabled() eq true && $LISTVIEW_ENTRY->isViewable()}
													<a {if $LISTVIEW_HEADER->isNameField() eq true}class="modCT_{$MODULE} js-list-field js-popover-tooltip--record" data-js="width" {/if} href="{$LISTVIEW_ENTRY->getDetailViewUrl()}">
														<small> {$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADER)} </small>
													</a>
												{else}
													<small>
														{if $FIELD_DATA_TYPE eq 'multiImage'}
															{$LISTVIEW_ENTRY->getTilesDisplayValue($LISTVIEW_HEADER)}
														{else}
															{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADER)}

														{/if}
													</small>
												{/if}
											</span>
										</h5>
									{else}
										{if !empty($LISTVIEW_HEADER->get('source_field_name'))}
											{assign var=LISTVIEW_HEADER_NAME value="`$LISTVIEW_HEADER->getName()`:`$LISTVIEW_HEADER->getModuleName()`:`$LISTVIEW_HEADER->get('source_field_name')`"}
										{else}
											{assign var=LISTVIEW_HEADER_NAME value=$LISTVIEW_HEADER->getName()}
										{/if}
										<div class="text-center">
											<span class=" text-muted"> <small> {$LISTVIEW_HEADER->getFullLabelTranslation($MODULE_MODEL)}: </small> </span>
											<span class=" listViewEntryValue noWrap text-muted c-tile-value" data-field-type="{$LISTVIEW_HEADER->getFieldDataType()}">
												{if empty($LISTVIEW_HEADER->get('source_field_name')) && ($LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->getUIType() eq '4') && $MODULE_MODEL->isListViewNameFieldNavigationEnabled() eq true && $LISTVIEW_ENTRY->isViewable()}
													<a {if $LISTVIEW_HEADER->isNameField() eq true}class="modCT_{$MODULE} js-list-field js-popover-tooltip--record" data-js="width" {/if} href="{$LISTVIEW_ENTRY->getDetailViewUrl()}">
														<small> {$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADER)} </small>
													</a>
												{else}
													<small> {$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADER)} </small>
												{/if}
											</span>
										</div>
									{/if}
								{/foreach}
							</div>
						</div>
					</div>
				</div>
			{/foreach}
		</div>
		{if $LISTVIEW_ENTRIES_COUNT eq '0'}
			<table class="emptyRecordsDiv">
				<tbody>
					<tr>
						<td>
							{\App\Language::translate('LBL_RECORDS_NO_FOUND')}. {if $IS_MODULE_EDITABLE}
								<a href="{$MODULE_MODEL->getCreateRecordUrl()}">{\App\Language::translate('LBL_CREATE_SINGLE_RECORD')}</a>
							{/if}
						</td>
					</tr>
				</tbody>
			</table>
		{/if}
		</>
		<!-- /tpl-Base-TilesContents -->
{/strip}
