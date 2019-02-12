{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Modals-RecordsList modal-body js-modal-body" data-js="container">
		<input type="hidden" class="js-parent-module" data-js="value" value="{$SOURCE_MODULE}"/>
		<input type="hidden" class="js-source-record" data-js="value" value="{$SOURCE_RECORD}"/>
		<input type="hidden" class="js-source-field" data-js="value" value="{$SOURCE_FIELD}"/>
		<input type="hidden" class="js-related-parent-module" data-js="value" value="{$RELATED_PARENT_MODULE}"/>
		<input type="hidden" class="js-related-parent-id" data-js="value" value="{$RELATED_PARENT_ID}"/>
		<input type="hidden" class="js-multi-select" data-js="value" value="{$MULTI_SELECT}"/>
		<input type="hidden" class="js-order-by" data-js="value" value="{$ORDER_BY}"/>
		<input type="hidden" class="js-sort-order" data-js="value" value="{$SORT_ORDER}"/>
		<input type='hidden' class='js-page-number' data-js="value" value="{$PAGE_NUMBER}">
		<input type="hidden" class="js-total-count" data-js="value" value="{$LISTVIEW_COUNT}"/>
		<input type='hidden' class="js-page-limit" data-js="value" value="{$PAGING_MODEL->getPageLimit()}"/>
		<input type="hidden" class="js-no-entries" data-js="value" value="{$LISTVIEW_ENTRIES_COUNT}">
		<input type="hidden" class="js-additional-informations" data-js="value" value="{$ADDITIONAL_INFORMATIONS}">
		<input type="hidden" id="autoRefreshListOnChange" data-js="value"
			   value="{AppConfig::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}"/>
		<input type="hidden" class="js-filter-fields" data-js="value" value="{App\Purifier::encodeHtml(\App\Json::encode($FILTER_FIELDS))}">
		<div class="table-responsive">
			{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
			<table class="table table-bordered listViewEntriesTable">
				<thead>
				<tr class="listViewHeaders">
					<th class="{$WIDTHTYPE} text-center">
						{if $MULTI_SELECT}
							<input type="checkbox" title="{App\Language::translate('LBL_SELECT_ALL_CURRENTPAGE')}"
								   class="js-select-checkbox u-cursor-pointer" data-type="all" data-js="click"/>
						{/if}
					</th>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						<th class="{$WIDTHTYPE}">
							<a href="#" class="{if $LISTVIEW_HEADER->isListviewSortable()}js-change-order{/if}"
							   data-js="click" data-name="{$LISTVIEW_HEADER->getFieldName()}"
							   data-next-order="{if $ORDER_BY eq $LISTVIEW_HEADER->getFieldName()}{$NEXT_SORT_ORDER}{else}ASC{/if}">
								{App\Language::translate($LISTVIEW_HEADER->getFieldLabel(), $MODULE_NAME)}
								{if $ORDER_BY eq $LISTVIEW_HEADER->getFieldName()}
									<span class="{$SORT_IMAGE} ml-2"
										  alt="{if $SORT_ORDER eq 'ASC'}{App\Language::translate('LBL_SORT_ASCENDING')}{else}{App\Language::translate('LBL_SORT_DESCENDING')}{/if}"></span>
								{/if}
							</a>
						</th>
					{/foreach}
				</tr>
				</thead>
				<tbody>
				<tr>
					<td class="listSearchTd">
						<button class="btn btn-light" data-trigger="listSearch">
							<span class="fas fa-search"></span>
						</button>
					</td>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						<td>
							{assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
							{assign var=LISTVIEW_HEADER_NAME value=$LISTVIEW_HEADER->getName()}
							{if isset($SEARCH_DETAILS[$LISTVIEW_HEADER_NAME])}
								{assign var=SEARCH_INFO value=$SEARCH_DETAILS[$LISTVIEW_HEADER_NAME]}
							{else}
								{assign var=SEARCH_INFO value=[]}
							{/if}
							{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $MODULE_NAME) FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL}
						</td>
					{/foreach}
				</tr>
				{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES}
					<tr class="u-cursor-pointer js-select-row" data-id="{$LISTVIEW_ENTRY->getId()}" data-js="click"
						data-name='{$LISTVIEW_ENTRY->getName()}'
						data-info='{App\Json::encode($LISTVIEW_ENTRY->getRawData())}'>
						<td class="{$WIDTHTYPE} u-cursor-auto text-center">
							{if $MULTI_SELECT}
								<input class="js-select-checkbox" title="{App\Language::translate('LBL_SELECT_RECORD')}"
									   type="checkbox" data-type="row" data-js="click"/>
							{/if}
						</td>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->getFieldName()}
							<td class="{$WIDTHTYPE}" data-field="{$LISTVIEW_HEADERNAME}"
								data-type="{$LISTVIEW_HEADER->getFieldDataType()}">
								{if $LISTVIEW_HEADER->get('fromOutsideList') eq true}
									{$LISTVIEW_HEADER->getDisplayValue($LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME))}
								{else}
									{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADER,true)}
								{/if}
							</td>
						{/foreach}
					</tr>
				{/foreach}
				</tbody>
			</table>
			{if $LISTVIEW_ENTRIES_COUNT eq '0'}
				<div>
					<div class="emptyRecordsDiv">{App\Language::translate('LBL_NO_RELATED_RECORDS_FOUND', $MODULE_NAME)}</div>
				</div>
			{/if}
		</div>
	</div>
{/strip}