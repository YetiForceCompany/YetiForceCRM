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
    {if !empty($CUSTOM_VIEWS)}
        <div class="relatedContainer listViewPageDiv m-0">
            <input type="hidden" name="emailEnabledModules" value=true />
            <input type="hidden" id="view" value="{$VIEW}" />
            <input type="hidden" name="currentPageNum" value="{$PAGING_MODEL->getCurrentPage()}" />
            <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE->get('name')}" />
            <input type="hidden" value="{$ORDER_BY}" id="orderBy" />
            <input type="hidden" value="{$SORT_ORDER}" id="sortOrder" />
            <input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries" />
            <input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit' />
            <input type="hidden" id="recordsCount" value="" />
            <input type="hidden" id="selectedIds" name="selectedIds" data-selected-ids={\App\Json::encode($SELECTED_IDS)} />
            <input type="hidden" id="excludedIds" name="excludedIds" data-excluded-ids={\App\Json::encode($EXCLUDED_IDS)} />
            <input type="hidden" id="recordsCount" name="recordsCount" />
            <input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount' />
			<input type="hidden" id="autoRefreshListOnChange" value="{AppConfig::performance('AUTO_REFRESH_RECORD_LIST_ON_SELECT_CHANGE')}" />
            <div class="relatedHeader">
                <div class="btn-toolbar row">
					<div class="col-md-9">
						<div class="btn-group listViewMassActions btn-group pr-2">
							{if $RELATED_LIST_LINKS['RELATEDLIST_MASSACTIONS']|@count gt 0}
								<button class="btn btn-light dropdown-toggle" data-toggle="dropdown"><strong>{\App\Language::translate('LBL_ACTIONS', $MODULE)}</strong>&nbsp;&nbsp;<span class="caret"></span></button>
								<ul class="dropdown-menu">
									{foreach item="LISTVIEW_MASSACTION" from=$RELATED_LIST_LINKS['RELATEDLIST_MASSACTIONS'] name=actionCount}
										<li id="{$MODULE}_listView_massAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_MASSACTION->getLabel())}">
											<a class="dropdown-item" href="javascript:void(0);" {if stripos($LISTVIEW_MASSACTION->getUrl(), 'javascript:')===0}onclick='{$LISTVIEW_MASSACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick="Vtiger_List_Js.triggerMassAction('{$LISTVIEW_MASSACTION->getUrl()}')"{/if} >{\App\Language::translate($LISTVIEW_MASSACTION->getLabel(), $MODULE)}</a>
										</li>
											{if $smarty.foreach.actionCount.last eq true}
											<li class="dropdown-divider"></li>
											{/if}
										{/foreach}
										{if $RELATED_LIST_LINKS['RELATEDLIST_MASSACTIONS_ADV']|@count gt 0}
											{foreach item=LISTVIEW_ADVANCEDACTIONS from=$RELATED_LIST_LINKS['RELATEDLIST_MASSACTIONS_ADV']}
											<li id="{$MODULE}_listView_advancedAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_ADVANCEDACTIONS->getLabel())}">
												<a {if stripos($LISTVIEW_ADVANCEDACTIONS->getUrl(), 'javascript:')===0}
													href="javascript:void(0);" onclick='{$LISTVIEW_ADVANCEDACTIONS->getUrl()|substr:strlen("javascript:")};'
													{else}
														href='{$LISTVIEW_ADVANCEDACTIONS->getUrl()}'
														{/if}
															class="dropdown-item{if $LISTVIEW_ADVANCEDACTIONS->get('linkclass') neq ''} {$LISTVIEW_ADVANCEDACTIONS->get('linkclass')}{/if}"
															{if count($LISTVIEW_ADVANCEDACTIONS->get('linkdata')) gt 0}
																{foreach from=$LISTVIEW_ADVANCEDACTIONS->get('linkdata') key=NAME item=DATA}
																	data-{$NAME}="{$DATA}"
																{/foreach}
															{/if}
															>{\App\Language::translate($LISTVIEW_ADVANCEDACTIONS->getLabel(), $MODULE)}</a>
													</li>
													{/foreach}
														{/if}
														</ul>
														{/if}
														</div>
														<div class="btn-group col-md-3">
															<span class="customFilterMainSpan">
																{if $CUSTOM_VIEWS|@count gt 0}
																	<select id="recordsFilter" class="col-md-12" data-placeholder="{\App\Language::translate('LBL_SELECT_TO_LOAD_LIST', $RELATED_MODULE_NAME)}">
																		{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
																			<optgroup label="{\App\Language::translate($GROUP_LABEL)}">
																				{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
																					<option id="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" value="{$CUSTOM_VIEW->get('cvid')}" class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" data-id="{$CUSTOM_VIEW->get('cvid')}">{if $CUSTOM_VIEW->get('viewname') eq 'All'}{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $RELATED_MODULE_NAME)} {\App\Language::translate($RELATED_MODULE_NAME, $RELATED_MODULE_NAME)}{else}{\App\Language::translate($CUSTOM_VIEW->get('viewname'), $RELATED_MODULE_NAME)}{/if}{if $GROUP_LABEL neq 'Mine'} [ {$CUSTOM_VIEW->getOwnerName()} ] {/if}</option>
																				{/foreach}
																			</optgroup>
																		{/foreach}
																	</select>
																	<span class="filterImage">
																		<span class="fas fa-filter"></span>
																	</span>
																{else}
																	<input type="hidden" value="0" id="customFilter" />
																{/if}
															</span>
														</div>
														<div class="btn-group pr-2">
															<button type="button" class="btn btn-light loadFormFilterButton js-popover-tooltip"	data-js="popover" data-content="{\App\Language::translate('LBL_LOAD_RECORDS_INFO',$MODULE)}">
																<span class="fas fa-filter"></span>&nbsp;
																<strong>{\App\Language::translate('LBL_LOAD_RECORDS',$MODULE)}</strong>
															</button>
														</div>
														{foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
															<div class="btn-group pr-2">
																{assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
																{assign var=IS_SEND_EMAIL_BUTTON value={$RELATED_LINK->get('_sendEmail')}}
																<button type="button" class="btn btn-light addButton
																		{if $IS_SELECT_BUTTON eq true} selectRelation {/if} modCT_{$RELATED_MODULE->get('name')} {if $RELATED_LINK->linkqcs eq true}quickCreateSupported{/if}"
																		{if $IS_SELECT_BUTTON eq true} data-moduleName='{$RELATED_LINK->get('_module')->get('name')}'{/if}
																		{if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
																		{if $IS_SEND_EMAIL_BUTTON eq true}	onclick="{$RELATED_LINK->getUrl()}" {else} data-url="{$RELATED_LINK->getUrl()}"{/if}
																		{if ($IS_SELECT_BUTTON eq false) and ($IS_SEND_EMAIL_BUTTON eq false)}
																			name="addButton">
																		{else}
																			> {* closing the button tag *}
																		{/if}
																		{if $RELATED_LINK->get('linkicon') neq ''}
																			<span class="{$RELATED_LINK->get('linkicon')}"></span>
																		{/if}&nbsp;<strong>{$RELATED_LINK->getLabel()}</strong>
																</button>
															</div>
														{/foreach}&nbsp;
													</div>
													<div class="col-md-3">
														<div class="float-right">
															{if $VIEW_MODEL}
																<div class="float-right pl-1">
																	{assign var=COLOR value=AppConfig::search('LIST_ENTITY_STATE_COLOR')}
																	<input type="hidden" class="entityState" value="{if $VIEW_MODEL->has('entityState')}{$VIEW_MODEL->get('entityState')}{else}Active{/if}" />
																	<div class="dropdown dropdownEntityState u-remove-dropdown-icon">
																		<button class="btn btn-light dropdown-toggle" type="button" id="dropdownEntityState" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
																			{if $VIEW_MODEL->get('entityState') === 'Archived'}
																				<span class="fas fa-archive"></span>
																			{elseif $VIEW_MODEL->get('entityState') === 'Trash'}
																				<span class="fas fa-trash-alt"></span>
																			{elseif $VIEW_MODEL->get('entityState') === 'All'}
																				<span class="fas fa-bars"></span>
																			{else}
																				<span class="fas fa-undo-alt"></span>
																			{/if}
																		</button>
																		<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownEntityState">
																			<li {if $COLOR['Active']}style="border-color: {$COLOR['Active']};"{/if}>
																				<a class="dropdown-item{if !$VIEW_MODEL->get('entityState') || $VIEW_MODEL->get('entityState') === 'Active'} active{/if}" href="#" data-value="Active">
																					<span class="fas fa-undo-alt mr-1"></span>&nbsp;
																					{\App\Language::translate('LBL_ENTITY_STATE_ACTIVE')}
																				</a>
																			</li>
																			<li {if $COLOR['Archived']}style="border-color: {$COLOR['Archived']};"{/if}>
																				<a class="dropdown-item{if $VIEW_MODEL->get('entityState') === 'Archived'} active{/if}" href="#" data-value="Archived">
																					<span class="fas fa-archive mr-1"></span>
																					{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
																				</a>
																			</li>
																			<li {if $COLOR['Trash']}style="border-color: {$COLOR['Trash']};"{/if}>
																				<a class="dropdown-item{if $VIEW_MODEL->get('entityState') === 'Trash'} active{/if}" href="#" data-value="Trash">
																					<span class="fas fa-trash-alt mr-1"></span>
																					{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
																				</a>
																			</li>
																			<li>
																				<a class="dropdown-item{if $VIEW_MODEL->get('entityState') === 'All'} active{/if}" href="#" data-value="All">
																					<span class="fas fa-bars mr-1"></span>
																					{\App\Language::translate('LBL_ALL')}
																				</a>
																			</li>
																		</ul>
																	</div>
																</div>
															{/if}
														</div>
														<div class="paginationDiv float-right">
															{include file=\App\Layout::getTemplatePath('Pagination.tpl', $MODULE) VIEWNAME='related'}
														</div>
													</div>
												</div>
											</div>
											<div id="selectAllMsgDiv" class="alert-block msgDiv">
												<strong><a id="selectAllMsg">{\App\Language::translate('LBL_SELECT_ALL',$MODULE)}&nbsp;{\App\Language::translate($RELATED_MODULE->get('name'))}&nbsp;(<span id="totalRecordsCount"></span>)</a></strong>
											</div>
											<div id="deSelectAllMsgDiv" class="alert-block msgDiv">
												<strong><a id="deSelectAllMsg">{\App\Language::translate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></strong>
											</div>
											<div class="contents-topscroll">
												<div class="topscroll-div">
													&nbsp;
												</div>
											</div>
											{include file=\App\Layout::getTemplatePath('ListViewAlphabet.tpl', $RELATED_MODULE_NAME) MODULE_MODEL=$RELATED_MODULE}
											<div class="relatedContents contents-bottomscroll">
												<div class="bottomscroll-div">
													{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
													<table class="table table-bordered listViewEntriesTable {if $VIEW_MODEL && !$VIEW_MODEL->isEmpty('entityState')}listView{$VIEW_MODEL->get('entityState')}{/if}">
														<thead>
															<tr class="listViewHeaders">
																<th width="4%">
																	<input type="checkbox" title="{\App\Language::translate('LBL_SELECT_ALL')}" id="listViewEntriesMainCheckBox" />
																</th>
																{if $IS_FAVORITES}
																	<th></th>
																	{/if}
																	{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
																	<th nowrap>
																		{if $HEADER_FIELD->getColumnName() eq 'access_count' or $HEADER_FIELD->getColumnName() eq 'idlists' }
																			<a href="javascript:void(0);" class="noSorting">{\App\Language::translate($HEADER_FIELD->getFieldLabel(), $RELATED_MODULE->get('name'))}</a>
																		{elseif $HEADER_FIELD->getColumnName() eq 'time_start'}
																		{else}
																			<a href="javascript:void(0);" class="relatedListHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->getColumnName()}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->getColumnName()}">{\App\Language::translate($HEADER_FIELD->getFieldLabel(), $RELATED_MODULE->get('name'))}
																				&nbsp;&nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->getColumnName()}<span class="{$SORT_IMAGE}"></span>{/if}
																			</a>
																		{/if}
																	</th>
																{/foreach}
																<th nowrap colspan="2">
																	<a href="javascript:void(0);" class="noSorting">{\App\Language::translate('Status', $RELATED_MODULE->get('name'))}</a>
																</th>
															</tr>
														</thead>
														{if $RELATED_MODULE->isQuickSearchEnabled()}
															<tr>
																<td>
																	<a class="btn btn-light" role="button" data-trigger="listSearch" href="javascript:void(0);">
																		<span class="fas fa-search" title="{\App\Language::translate('LBL_SEARCH')}"></span>
																	</a>
																</td>
																{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
																	<td>
																		{assign var=FIELD_UI_TYPE_MODEL value=$HEADER_FIELD->getUITypeModel()}
																		{if isset($SEARCH_DETAILS[$HEADER_FIELD->getName()])}
																			{assign var=SEARCH_INFO value=$SEARCH_DETAILS[$HEADER_FIELD->getName()]}
																		{else}
																			{assign var=SEARCH_INFO value=[]}
																		{/if}
																		{include file=\App\Layout::getTemplatePath($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(), $RELATED_MODULE_NAME)
							FIELD_MODEL=$HEADER_FIELD SEARCH_INFO=$SEARCH_INFO USER_MODEL=$USER_MODEL MODULE_MODEL=$RELATED_MODULE}
																	</td>
																{/foreach}
																<td>
																	<button type="button" class="btn btn-light removeSearchConditions">
																		<span class="fas fa-times">{\App\Language::translate('LBL_CLEAR_SEARCH')}</span>
																	</button>
																</td>
															</tr>
														{/if}
														{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
															{assign var="RECORD_COLORS" value=$RELATED_RECORD->getListViewColor()}
															<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
																<td width="4%" class="{$WIDTHTYPE}" {if $RECORD_COLORS['leftBorder']}style="border-left-color: {$RECORD_COLORS['leftBorder']};"{/if}>
																	<input type="checkbox" value="{$RELATED_RECORD->getId()}" title="{\App\Language::translate('LBL_SELECT_SINGLE_ROW')}" class="listViewEntriesCheckBox" />
																</td>
																{if $IS_FAVORITES}
																	<td class="{$WIDTHTYPE} text-center text-center font-larger">
																		{assign var=RECORD_IS_FAVORITE value=(int)in_array($RELATED_RECORD->getId(),$FAVORITES)}
																		<a class="favorites" data-state="{$RECORD_IS_FAVORITE}">
																			<span class="fas fa-star align-middle {if !$RECORD_IS_FAVORITE}d-none{/if}" title="{\App\Language::translate('LBL_REMOVE_FROM_FAVORITES', $MODULE)}"></span>
																			<span class="far fa-star align-middle {if $RECORD_IS_FAVORITE}d-none{/if}" title="{\App\Language::translate('LBL_ADD_TO_FAVORITES', $MODULE)}"></span>
																		</a>
																	</td>
																{/if}
																{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
																	{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->getFieldName()}
																	<td nowrap class="{$WIDTHTYPE}">
																		{if ($HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->getUIType() eq '4') && $RELATED_RECORD->isViewable()}
																			<a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
																		{elseif $RELATED_HEADERNAME eq 'access_count'}
																			{$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
																		{elseif $RELATED_HEADERNAME eq 'time_start'}
																		{else}
																			{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
																		{/if}
																	</td>
																{/foreach}
																<td nowrap class="{$WIDTHTYPE}">
																	<!--
																	<span class="currentStatus btn-group">
																		<span class="statusValue dropdown-toggle" data-toggle="dropdown">{\App\Language::translate($RELATED_RECORD->get('status'),$MODULE)}</span>
																		<span title="{\App\Language::translate('LBL_EDIT', $MODULE)}" class="icon-arrow-down align-middle editRelatedStatus"></span>
																		<ul class="dropdown-menu float-right" style="left: -2px; position: relative;">
																	{foreach key=STATUS_ID item=STATUS from=$STATUS_VALUES}
																		<li id="{$STATUS_ID}" data-status="{\App\Language::translate($STATUS, $MODULE)}">
																			<a>{\App\Language::translate($STATUS, $MODULE)}</a>
																		</li>
																	{/foreach}
																</ul>
															</span>
																	-->
																</td>
																<td nowrap class="{$WIDTHTYPE}">
																	<div class="float-right actions">
																		<span class="actionImages">
																			<a href="{$RELATED_RECORD->getFullDetailViewUrl()}">
																				<span class="fas fa-th-list align-middle" title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}"></span>
																			</a>&nbsp;
																				{if $IS_EDITABLE}
																				<a href='{$RELATED_RECORD->getEditViewUrl()}'>
																					<span class="fas fa-edit align-middle" title="{\App\Language::translate('LBL_EDIT', $MODULE)}"></span>
																				</a>
																				{/if}
																				{if $IS_DELETABLE}
																				<a class="relationDelete">
																					<span class="fas fa-trash-alt align-middle" title="{\App\Language::translate('LBL_DELETE', $MODULE)}"></span>
																				</a>
																				{/if}
																		</span>
																	</div>
																</td>
															</tr>
														{/foreach}
													</table>
												</div>
											</div>
										</div>
										{else}
											{include file=\App\Layout::getTemplatePath('RelatedList.tpl')}
											{/if}
												{/strip}
