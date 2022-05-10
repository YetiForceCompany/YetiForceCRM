{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Modals-RecordsList modal-body js-modal-body" data-js="container">
		<input type="hidden" class="js-parent-module" data-js="value" value="{$SOURCE_MODULE}" />
		<input type="hidden" class="js-source-record" data-js="value" value="{$SOURCE_RECORD}" />
		<input type="hidden" class="js-source-field" data-js="value" value="{$SOURCE_FIELD}" />
		<input type="hidden" class="js-related-parent-module" data-js="value" value="{$RELATED_PARENT_MODULE}" />
		<input type="hidden" class="js-related-parent-id" data-js="value" value="{$RELATED_PARENT_ID}" />
		<input type="hidden" class="js-multi-select" data-js="value" value="{$MULTI_SELECT}" />
		<input type="hidden" id="orderBy" value="{\App\Purifier::encodeHtml(\App\Json::encode($ORDER_BY))}">
		<input type='hidden' class='js-page-number' data-js="value" value="{$PAGE_NUMBER}">
		<input type="hidden" class="js-total-count" data-js="value" value="{$LISTVIEW_COUNT}" />
		<input type='hidden' class="js-page-limit" data-js="value" value="{$PAGING_MODEL->getPageLimit()}" />
		<input type="hidden" class="js-no-entries" data-js="value" value="{$LISTVIEW_ENTRIES_COUNT}">
		<input type="hidden" class="js-additional-informations" data-js="value" value="{$ADDITIONAL_INFORMATIONS}">
		<input type="hidden" id="autoRefreshListOnChange" data-js="value" value="{App\Config::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}" />
		<input type="hidden" class="js-filter-fields" data-js="value" value="{App\Purifier::encodeHtml(\App\Json::encode($FILTER_FIELDS))}">
		<input type="hidden" id="search_params" value="{\App\Purifier::encodeHtml(\App\Json::encode($SEARCH_PARAMS))}">
		{include file=\App\Layout::getTemplatePath('ListViewAlphabet.tpl', $MODULE)}
		<input type="hidden" class="js-locked-fields" data-js="value" value="{\App\Purifier::encodeHtml(\App\Json::encode($LOCKED_FIELDS))}" />
		<input type="hidden" class="js-empty-fields" data-js="value" value="{\App\Purifier::encodeHtml(\App\Json::encode($LOCKED_EMPTY_FIELDS))}" />
		<div class="table-responsive">
			<table class="table table-bordered listViewEntriesTable">
				<thead>
					<tr class="listViewHeaders">
						<th class="{$WIDTHTYPE} text-center">
							{if $MULTI_SELECT}
								<input type="checkbox" title="{App\Language::translate('LBL_SELECT_ALL_CURRENTPAGE')}" {if $RECORD_SELECTED} checked="checked" {/if}
									class="js-select-checkbox u-cursor-pointer" data-type="all" data-js="click" />
							{/if}
						</th>
						{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
							{assign var=LISTVIEW_HEADER_NAME value=$LISTVIEW_HEADER->getFullName()}
							<th class="{$WIDTHTYPE}{if isset($ORDER_BY[$LISTVIEW_HEADER_NAME])} columnSorted{/if}">
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
						<tr class="u-cursor-pointer js-select-row listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}" data-js="click"
							data-name='{$LISTVIEW_ENTRY->getName()}'>
							<td class="{$WIDTHTYPE} u-cursor-auto text-center">
								{if $MULTI_SELECT}
									<input class="js-select-checkbox" title="{App\Language::translate('LBL_SELECT_RECORD')}" {if $RECORD_SELECTED} checked="checked" {/if}
										type="checkbox" data-type="row" data-js="click" />
								{/if}
							</td>
							{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
								{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->getFieldName()}
								<td class="{$WIDTHTYPE}" data-field="{$LISTVIEW_HEADERNAME}"
									data-type="{$LISTVIEW_HEADER->getFieldDataType()}">
									{if $LISTVIEW_HEADER->get('fromOutsideList') eq true}
										{$LISTVIEW_HEADER->getDisplayValue($LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME))}
									{else}
										{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADER)}
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
