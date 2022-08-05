{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Base-ListViewContents -->
	{include file=\App\Layout::getTemplatePath('ListViewContentsTop.tpl', $MODULE_NAME)}
	<table class="table tableBorderHeadBody listViewEntriesTable {$WIDTHTYPE} {if $VIEW_MODEL && !$VIEW_MODEL->isEmpty('entityState')}listView{$VIEW_MODEL->get('entityState')}{/if} js-fixed-thead" data-js="floatThead">
		<thead>
			<tr class="{if isset($CUSTOM_VIEWS) && $CUSTOM_VIEWS|@count gt 0}c-tab--border-active{/if} listViewHeaders">
				<th class="p-2">
					<div class="d-flex align-items-center">
						<label class="sr-only" for="listViewEntriesMainCheckBox">{\App\Language::translate('LBL_SELECT_ALL')}</label>
						<input type="checkbox" id="listViewEntriesMainCheckBox" title="{\App\Language::translate('LBL_SELECT_ALL')}" />
						{if $MODULE_MODEL->isAdvSortEnabled()}
							<button type="button" title="{\App\Language::translate('LBL_SORTING_SETTINGS')}" class="ml-2 btn {if !empty($ORDER_BY)}btn-info{else}btn-outline-info{/if} btn-xs js-show-modal"
								data-url="index.php?view=SortOrderModal&module={$MODULE_NAME}"
								data-modalid="sortOrderModal-{\App\Layout::getUniqueId()}" data-js="click">
								<span class="fas fa-sort"></span>
							</button>
						{/if}
						{if $MODULE_MODEL->isCustomViewAdvCondEnabled()}
							<button type="button" class="ml-2 btn {if !empty($ADVANCED_CONDITIONS['relationId']) || isset($ADVANCED_CONDITIONS['relationColumns'])}btn-primary{else}btn-outline-primary{/if} btn-xs js-custom-view-adv-cond-modal" title="{\App\Language::translate('LBL_CUSTOM_VIEW_ADV_COND')}" data-js="click">
								<span class="yfi-advenced-custom-view-conditions"></span>
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
							{assign var=LISTVIEW_HEADER_NAME value=$LISTVIEW_HEADER->getFullName()}
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
			{assign var="LISTVIEW_HEADER_COUNT" value=count($LISTVIEW_HEADERS)}
			{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
				{assign var=LINKS value=$LISTVIEW_ENTRY->getRecordListViewLinksRightSide()}
				{assign var="RECORD_ID" value=$LISTVIEW_ENTRY->getId()}
				{assign var="RECORD_COLORS" value=$LISTVIEW_ENTRY->getListViewColor()}
				<tr class="listViewEntries" data-id='{$LISTVIEW_ENTRY->getId()}' data-recordUrl='{$LISTVIEW_ENTRY->getDetailViewUrl()}' id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
					<td class="noWrap leftRecordActions listButtons {$WIDTHTYPE}" {if $RECORD_COLORS['leftBorder']}style="border-left-color: {$RECORD_COLORS['leftBorder']};" {/if}>
						{include file=\App\Layout::getTemplatePath('ListViewLeftSide.tpl', $MODULE_NAME)}
					</td>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS name=listHeaderForeach}
						<td class="listViewEntryValue noWrap {$WIDTHTYPE}" data-field-type="{$LISTVIEW_HEADER->getFieldDataType()}">
							{if empty($LISTVIEW_HEADER->get('source_field_name')) && ($LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->getUIType() eq '4') && $MODULE_MODEL->isListViewNameFieldNavigationEnabled() eq true && $LISTVIEW_ENTRY->isViewable()}
								<a {if $LISTVIEW_HEADER->isNameField() eq true}class="modCT_{$MODULE} js-list-field js-popover-tooltip--record" data-js="width" {/if} href="{$LISTVIEW_ENTRY->getDetailViewUrl()}">
									{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADER)}
								</a>
							{else}
								{$LISTVIEW_ENTRY->getListViewDisplayValue($LISTVIEW_HEADER)}
							{/if}
						</td>
					{/foreach}
					<td class="noWrap rightRecordActions listButtons {$WIDTHTYPE} reducePadding">
						{include file=\App\Layout::getTemplatePath('ListViewRightSide.tpl', $MODULE_NAME)}
					</td>
				</tr>
			{/foreach}
		</tbody>
		{if empty($SOURCE_MODULE) || $MODULE_NAME === $SOURCE_MODULE}
			<tfoot class="listViewSummation">
				<tr>
					<td></td>
					{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
						<td {if $LISTVIEW_HEADER@last}colspan="2" {/if} class="noWrap {if !empty($LISTVIEW_HEADER->isCalculateField())}border{/if}">
							{if !empty($LISTVIEW_HEADER->isCalculateField())}
								<button class="btn btn-sm btn-light js-popover-tooltip" data-js="popover" type="button" data-operator="sum" data-field="{$LISTVIEW_HEADER->getName()}" data-content="{\App\Language::translate('LBL_CALCULATE_SUM_FOR_THIS_FIELD')}">
									<span class="fas fa-signal" title="{\App\Language::translate('LBL_CALCULATE_SUM_FOR_THIS_FIELD')}"></span>
								</button>
								<span class="calculateValue"></span>
							{/if}
						</td>
					{/foreach}
				</tr>
			</tfoot>
		{/if}
	</table>
	{include file=\App\Layout::getTemplatePath('ListViewContentsBottom.tpl', $MODULE_NAME)}
	</div>
	<!-- /tpl-Base-ListViewContents -->
{/strip}
